<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'process_name' => 'sometimes|required|string',
            'window_name' => 'sometimes|required|string',
            'duration' => 'sometimes|required|integer',
        ];
    }
}
