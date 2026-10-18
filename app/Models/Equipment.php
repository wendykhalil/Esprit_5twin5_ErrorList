<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'brand',
        'power',
        'capacity',
        'condition',
        'price_per_day',
        'location',
        'availability',
        'image',
        'image_public_id',
        'status',
    ];

    protected $casts = [
        'availability' => 'boolean',
        'power' => 'decimal:2',
        'capacity' => 'decimal:2',
        'price_per_day' => 'decimal:2',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (
            str_starts_with($this->image, 'https://') ||
            str_starts_with($this->image, 'http://')
        ) {
            return $this->image;
        }

        return asset('storage/' . ltrim($this->image, '/'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function equipmentGuides(): HasMany
    {
        return $this->hasMany(EquipmentGuide::class);
    }
}