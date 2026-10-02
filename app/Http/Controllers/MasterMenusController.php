<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterMenusRequest;
use App\Models\MasterMenus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;

class MasterMenusController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MasterMenus::query();

            if ($request->filled('search')) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%");
                });
            }

            // Un-comment jika menggunakan kolom is_active
            // $query->where('is_active', ! $request->boolean('inactive'));

            return DataTables::of($query)->make(true);
        }

        return view('master-menus.index');
    }

    public function store(MasterMenusRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $masterMenu = MasterMenus::findOrNew($request->input('id'));
            $masterMenu->fill($request->validated());

            $masterMenu->exists
                ? $masterMenu->updated_by = Auth::id()
                : $masterMenu->created_by = Auth::id();

            $masterMenu->save();

            $routeName = $masterMenu->name;
            $permissions = $request->input('permissions', []);

            foreach ($permissions as $action => $value) {
                $permissionName = "{$routeName}.{$action}";

                if ($value) {
                    Permission::findOrCreate($permissionName, 'web');
                } else {
                    Permission::where('name', $permissionName)->delete();
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => $masterMenu->wasRecentlyCreated
                    ? 'Data berhasil ditambahkan.'
                    : 'Data berhasil diperbarui.',
                'data' => $masterMenu,
            ]);
        });
    }

    public function edit(MasterMenus $masterMenu)
    {
        $routeName = $masterMenu->name;
        $actions = ['view', 'add', 'update', 'delete', 'export'];

        $permissions = [];
        foreach ($actions as $action) {
            $permissions[$action] = Permission::where('name', "{$routeName}.{$action}")->exists();
        }

        $data = $masterMenu->toArray();
        $data['permissions'] = $permissions;

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function destroy(MasterMenus $masterMenu)
    {
        $masterMenu->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.',
        ]);
    }
}
