<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncSchedulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => 'required|array',
            'data.*.timestamp' => 'required|date',
            'data.*.status' => 'required|string|in:on,off',
        ];
    }
}
