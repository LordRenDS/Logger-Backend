<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'pc_id',
        'timestamp',
        'pc_status_id',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function pc(): BelongsTo
    {
        return $this->belongsTo(Pc::class);
    }

    public function pcStatus(): BelongsTo
    {
        return $this->belongsTo(PcStatus::class);
    }
}
