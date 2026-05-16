<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Process extends Model
{
    use HasFactory;

    protected $fillable = [
        'pc_id',
        'process_start',
        'process_name',
        'window_name',
        'duration',
    ];

    protected $casts = [
        'process_start' => 'datetime',
    ];

    public function pc(): BelongsTo
    {
        return $this->belongsTo(Pc::class);
    }
}
