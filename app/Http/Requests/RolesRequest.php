<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'id' => 'nullable|exists:roles,id',
            'name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            // Kustomisasi pesan validasi di sini jika diperlukan
        ];
    }
}