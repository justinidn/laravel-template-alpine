<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserPermissionsRequest;
use App\Models\MasterMenus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Yajra\DataTables\DataTables;

class UserPermissionsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::with('permissions');

            if ($request->filled('search')) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nrk', 'like', "%{$search}%");
                });
            }

            // Un-comment jika menggunakan kolom is_active
            // $query->where('is_active', ! $request->boolean('inactive'));

            return DataTables::of($query)->make(true);
        }

        return view('user-permissions.index');
    }

    public function store(UserPermissionsRequest $request)
    {
        $validated = $request->validated();
        $user = User::findOrFail($validated['id'] ?? $validated['user_id']);
        $wasAssigned = $user->permissions()->exists();

        DB::transaction(function () use ($user, $validated) {
            $user->syncPermissions($validated['permissions'] ?? []);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'status' => 'success',
            'message' => $wasAssigned ? 'Data berhasil diperbarui.' : 'Data berhasil ditambahkan.',
            'data' => $user->load('permissions'),
        ]);
    }

    public function show(User $userPermission)
    {
        $userPermission->load('permissions');

        return response()->json([
            'status' => 'success',
            'data' => $userPermission,
        ]);
    }

    public function edit(User $userPermission)
    {
        $userPermission->load('permissions');

        return response()->json([
            'status' => 'success',
            'data' => $userPermission,
        ]);
    }

    public function destroy(User $userPermission)
    {
        $userPermission->syncPermissions([]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'status' => 'success',
            'message' => 'Permission user berhasil dihapus.',
        ]);
    }

    public function getAssignmentOptions()
    {
        $allPermissions = Permission::query()
            ->where('guard_name', config('auth.defaults.guard', 'web'))
            ->orderBy('name')
            ->get();

        $menus = MasterMenus::orderBy('name')->get()->map(function ($menu) use ($allPermissions) {
            $prefix = strtolower($menu->name).'.';
            $menuPermissions = $allPermissions->filter(
                fn ($permission) => str_starts_with(strtolower($permission->name), $prefix)
            );

            $menu->available_actions = $menuPermissions->map(function ($permission) {
                return [
                    'full_name' => $permission->name,
                    'action' => last(explode('.', $permission->name)),
                ];
            })->values();

            return $menu;
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'users' => User::query()->select('id', 'name', 'email')->orderBy('name')->get(),
                'menus' => $menus,
            ],
        ]);
    }
}
