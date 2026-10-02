<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DepartmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'id'               => 'nullable|exists:master_departments,id',
            'department_alias' => 'required|string|max:5|unique:master_departments,department_alias,' . $id,
            'department_name'  => 'required|string|max:50',
            'is_active'        => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'department_alias.required' => 'Alias departemen wajib diisi.',
            'department_alias.max'      => 'Alias departemen maksimal 5 karakter.',
            'department_alias.unique'   => 'Alias departemen sudah digunakan.',
            'department_name.required'  => 'Nama departemen wajib diisi.',
            'department_name.max'       => 'Nama departemen maksimal 50 karakter.',
        ];
    }
}
