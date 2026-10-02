<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => [
                'integer',
                Rule::exists(config('permission.table_names.roles'), 'id')
                    ->where('guard_name', config('auth.defaults.guard', 'web')),
            ],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
