<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty',
        'description',
        'experience_years',
        'phone',
        'location',
        'hourly_rate',
        'availability',
        'status',
    ];

    protected $casts = [
        'availability' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'experience_years' => 'integer',
    ];

    /**
     * Get the user that owns the service provider profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the service requests for the service provider.
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
