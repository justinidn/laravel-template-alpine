<?php

use App\Http\Controllers\UsersController;
use App\Http\Controllers\UserPermissionsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\MasterMenusController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('users', UsersController::class);
    Route::get('user-permissions/assignment-options', [UserPermissionsController::class, 'getAssignmentOptions'])->name('user-permissions.assignment-options');
    Route::resource('user-permissions', UserPermissionsController::class);
    Route::get('roles/master-menus-list', [RolesController::class, 'getMasterMenusWithPermissions'])->name('roles.master-menus-list');
    Route::resource('roles', RolesController::class);
    Route::get('roles/master-menus-list', [RolesController::class, 'getMasterMenusWithPermissions'])->name('roles.master-menus-list');
    Route::resource('master-menus', MasterMenusController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('departments', DepartmentsController::class);
});

require __DIR__ . '/auth.php';
