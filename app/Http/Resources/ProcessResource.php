<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pc_id' => $this->pc_id,
            'process_start' => $this->process_start?->toIso8601String(),
            'process_name' => $this->process_name,
            'window_name' => $this->window_name,
            'duration' => $this->duration,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
