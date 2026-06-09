<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePcRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unique_id' => 'required|string',
            'name' => 'nullable|string',
        ];
    }
}
