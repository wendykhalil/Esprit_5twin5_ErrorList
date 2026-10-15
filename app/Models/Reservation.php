<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'equipment_id',
        'date_debut',
        'date_fin',
        'statut',
        'prix_total',
        'compensation_code_id',
        'base_price',
        'discount_amount',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'base_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public const STATUTS = ['en_attente', 'confirmee', 'en_cours', 'terminee', 'litige', 'annulee', 'refusee'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function getEquipmentLabelAttribute(): string
    {
        return $this->equipment?->name
            ?? $this->equipment?->nom
            ?? $this->equipment?->title
            ?? 'Équipement #'.$this->equipment_id;
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function contract(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function reclamations(): HasMany
    {
        return $this->hasMany(Reclamation::class);
    }

    public function releveEnergie(): HasOne
    {
        return $this->hasOne(ReleveEnergie::class);
    }

    /**
     * Get the compensation code applied to this reservation (if any).
     */
    public function compensationCode(): BelongsTo
    {
        return $this->belongsTo(CompensationCode::class);
    }

    /**
     * Check if this reservation has a valid compensation code applied.
     *
     * @return bool
     */
    public function hasValidCompensationCode(): bool
    {
        return $this->compensation_code_id !== null
            && $this->compensationCode?->isValid();
    }

    /**
     * Get the discount amount applied to this reservation.
     * Returns 0 if no code or code is invalid.
     *
     * @return float
     */
    public function getDiscountAmount(): float
    {
        if (!$this->compensation_code_id) {
            return 0.0;
        }

        return (float) ($this->discount_amount ?? 0);
    }

    /**
     * Get the final price after discount.
     * Returns prix_total (which already includes discount).
     *
     * @return float
     */
    public function getFinalPrice(): float
    {
        return (float) $this->prix_total;
    }

    /**
     * Get the base price before any discount.
     * If no discount applied, base_price and prix_total are equal.
     *
     * @return float
     */
    public function getBasePrice(): float
    {
        if ($this->base_price !== null) {
            return (float) $this->base_price;
        }

        // Legacy: if base_price not set, assume prix_total was not discounted
        return (float) $this->prix_total;
    }

}
