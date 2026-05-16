<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PcStatus extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'status',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
