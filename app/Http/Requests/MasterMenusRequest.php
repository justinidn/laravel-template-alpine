<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MasterMenusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'id' => 'nullable|exists:master_menus,id',
            'name' => 'required|string|max:50',
            'display_name' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            // Kustomisasi pesan validasi di sini jika diperlukan
        ];
    }
}