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
        'status',
    ];

    protected $casts = [
        'availability' => 'boolean',
        'power' => 'decimal:2',
        'capacity' => 'decimal:2',
        'price_per_day' => 'decimal:2',
    ];

    /**
     * Owner of the equipment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Category of the equipment.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Service requests for this equipment.
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    
/**
 * Usage guides associated with this equipment.
 */
public function equipmentGuides(): HasMany
{
    return $this->hasMany(EquipmentGuide::class);
}

}