<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'id' => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:master_departments,id'],
            'nrk' => ['required', 'numeric', 'digits_between:1,7', Rule::unique('users', 'nrk')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => [$id ? 'nullable' : 'required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'system_login' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            // Kustomisasi pesan validasi di sini jika diperlukan
        ];
    }
}
