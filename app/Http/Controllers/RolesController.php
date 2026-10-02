<?php

namespace App\Http\Controllers;

use App\Http\Requests\RolesRequest;
use App\Models\MasterMenus;
use App\Models\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Yajra\DataTables\DataTables;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Roles::with('masterMenu');

            if ($request->filled('search')) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });
            }

            return DataTables::of($query)->make(true);
        }

        return view('roles.index');
    }

    public function store(RolesRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $roleModel = Roles::findOrNew($request->input('id'));
            $roleModel->fill($request->validated());

            if (!$roleModel->guard_name) {
                $roleModel->guard_name = config('auth.defaults.guard', 'web');
            }

            $roleModel->save();

            // Ambil permission aktif dari form modal
            $permissionsInput = $request->input('permissions', []);
            $activePermissions = array_keys(array_filter($permissionsInput, function ($val) {
                return (bool) $val;
            }));

            // Pastikan nama permission ada di tabel 'permissions'
            foreach ($activePermissions as $permName) {
                Permission::findOrCreate($permName, $roleModel->guard_name);
            }

            // Ini akan otomatis mengisi / memperbarui tabel 'role_has_permissions'
            $roleModel->syncPermissions($activePermissions);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return response()->json([
                'status' => 'success',
                'message' => $roleModel->wasRecentlyCreated
                    ? 'Data berhasil ditambahkan.'
                    : 'Data berhasil diperbarui.',
                'data' => $roleModel,
            ]);
        });
    }

    public function show(Roles $role)
    {
        $role->load('permissions');

        return response()->json([
            'status' => 'success',
            'data' => $role,
        ]);
    }

    public function edit(Roles $role)
    {
        $role->load('permissions');

        return response()->json([
            'status' => 'success',
            'data' => $role,
        ]);
    }

    public function destroy(Roles $role)
    {
        return DB::transaction(function () use ($role) {
            $role->syncPermissions([]);
            $role->delete();

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil dihapus.',
            ]);
        });
    }

    public function getMasterMenusWithPermissions()
    {
        // Ambil semua permissions Spatie
        $allPermissions = Permission::all();

        $menus = MasterMenus::all()->map(function ($menu) use ($allPermissions) {
            $prefixLower = strtolower($menu->name) . '.';
            $prefixRaw = $menu->name . '.';

            // Filter permission yang berawalan nama menu ini
            $menuPermissions = $allPermissions->filter(function ($perm) use ($prefixLower, $prefixRaw) {
                return str_starts_with(strtolower($perm->name), $prefixLower)
                    || str_starts_with($perm->name, $prefixRaw);
            });

            $menu->available_actions = $menuPermissions->map(function ($perm) {
                // Ambil nama aksi di belakang titik (misal: "Users.view" -> "view")
                $parts = explode('.', $perm->name);
                $action = end($parts);

                return [
                    'full_name' => $perm->name,
                    'action' => $action,
                ];
            })->values();

            return $menu;
        });

        return response()->json([
            'status' => 'success',
            'data' => $menus,
        ]);
    }
}
