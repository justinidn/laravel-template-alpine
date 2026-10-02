<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsersRequest;
use App\Models\Departments;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::with('department');

            if ($request->filled('search')) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('nrk', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->status);
            }

            return DataTables::of($query)->make(true);
        }

        $departments = Departments::query()->orderBy('department_name')->get()->pluck('display_name', 'id');

        return view('users.index', compact('departments'));
    }

    public function store(UsersRequest $request)
    {
        $user = User::findOrNew($request->input('id'));
        $isCreating = ! $user->exists;
        $validated = $request->validated();

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->fill($validated);

        if ($isCreating) {
            $user->email_verified_at = now();
            $user->is_active = true;
        } elseif ($request->has('is_active')) {
            $user->is_active = $request->boolean('is_active');
        }

        $user->save();

        if ($request->exists('system_login')) {
            $systemLoginPermission = Permission::firstOrCreate([
                'name' => 'system.login',
                'guard_name' => 'web',
            ]);

            if ($request->boolean('system_login')) {
                $user->givePermissionTo($systemLoginPermission);
            } else {
                $user->revokePermissionTo($systemLoginPermission);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => $user->wasRecentlyCreated
                ? 'Data berhasil ditambahkan.'
                : 'Data berhasil diperbarui.',
            'data' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $data = $user->toArray();
        $data['system_login'] = $user->getDirectPermissions()->contains('name', 'system.login');

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.',
        ]);
    }
}
