<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRolesRequest;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Yajra\DataTables\DataTables;

class UserRolesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::with('roles');

            if ($request->filled('search')) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nrk', 'like', "%{$search}%");
                });
            }

            return DataTables::of($query)->make(true);
        }

        return view('user-roles.index');
    }

    public function store(UserRolesRequest $request)
    {
        $validated = $request->validated();
        $user = User::findOrFail($validated['user_id']);
        $wasAssigned = $user->roles()->exists();

        DB::transaction(function () use ($user, $validated) {
            $roleIds = array_map('intval', $validated['roles'] ?? []);
            $user->syncRoles($roleIds);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'status' => 'success',
            'message' => $wasAssigned ? 'Data berhasil diperbarui.' : 'Data berhasil ditambahkan.',
            'data' => $user->load('roles'),
        ]);
    }

    public function edit(User $userRole)
    {
        $userRole->load('roles');

        return response()->json([
            'status' => 'success',
            'data' => $userRole,
        ]);
    }

    public function getAssignmentOptions()
    {
        $guardName = config('auth.defaults.guard', 'web');
        $roles = Roles::query()
            ->where('guard_name', $guardName)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'users' => User::query()->select('id', 'name', 'email')->orderBy('name')->get(),
                'roles' => $roles,
            ],
        ]);
    }
}
