<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'timestamp' => 'sometimes|required|date',
            'status' => 'sometimes|required|string|in:on,off',
        ];
    }
}
