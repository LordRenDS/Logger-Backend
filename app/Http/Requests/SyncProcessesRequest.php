<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncProcessesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => 'required|array',
            'data.*.process_start' => 'required|date',
            'data.*.process_name' => 'required|string',
            'data.*.window_name' => 'required|string',
            'data.*.duration' => 'required|integer',
        ];
    }
}
