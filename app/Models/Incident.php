<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'reclamation_id',
        'equipment_id',
        'type_probleme',
        'description',
        'damage_percentage',
        'proposed_refund_percentage',
        'proposed_refund_amount',
    ];

    protected $casts = [
        'damage_percentage' => 'float',
        'proposed_refund_percentage' => 'float',
        'proposed_refund_amount' => 'decimal:2',
    ];

    /**
     * Types de problèmes possibles
     */
    public const TYPES = [
        'endommage',
        'manquant',
        'malfonctionnant',
        'autre',
    ];

    /**
     * Get the reclamation that owns this incident.
     */
    public function reclamation(): BelongsTo
    {
        return $this->belongsTo(Reclamation::class);
    }

    /**
     * Get the equipment associated with this incident.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Calculate proposed refund based on type and equipment price.
     */
    public function calculateProposedRefund(): float
    {
        $basePrice = $this->equipment?->price_per_day ?? 0;

        return match ($this->type_probleme) {
            'endommage' => $basePrice * 0.30,
            'manquant' => $basePrice * 0.20,
            default => 0,
        };
    }
}
