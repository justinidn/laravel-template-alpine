<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentsRequest;
use App\Models\Departments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class DepartmentsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Departments::query();

            if ($request->filled('search')) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('department_name', 'like', "%{$search}%")
                        ->orWhere('department_alias', 'like', "%{$search}%");
                });
            }

            $status = $request->filled('status') ? $request->status : true;

            $query->where('is_active', $status);

            return DataTables::of($query)->make(true);
        }

        return view('departments.index');
    }

    public function store(DepartmentsRequest $request)
    {
        $department = Departments::findOrNew($request->input('id'));
        $department->fill($request->validated());
        $department->is_active = $request->boolean('is_active');

        $department->exists
            ? $department->updated_by = Auth::id()
            : $department->created_by = Auth::id();

        $department->save();

        return response()->json([
            'status' => 'success',
            'message' => $department->wasRecentlyCreated
                ? 'Departemen berhasil ditambahkan.'
                : 'Departemen berhasil diperbarui.',
            'data' => $department,
        ]);
    }

    public function edit(Departments $department)
    {
        return response()->json([
            'status' => 'success',
            'data' => $department,
        ]);
    }

    public function destroy(Departments $department)
    {
        $department->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Departemen berhasil dihapus.',
        ]);
    }
}
