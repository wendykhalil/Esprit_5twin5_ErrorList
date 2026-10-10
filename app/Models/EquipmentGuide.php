<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentGuide extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'title',
        'usage_context',
        'instructions',
        'safety_precautions',
        'difficulty_level',
        'video_url',
        'status',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
