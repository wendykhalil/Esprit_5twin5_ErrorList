<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
        'base_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    protected function dateDebut(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->startOfDay() : null,
            set: fn ($value) => $value ? Carbon::parse($value)->toDateString() : null,
        );
    }

    protected function dateFin(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->startOfDay() : null,
            set: fn ($value) => $value ? Carbon::parse($value)->toDateString() : null,
        );
    }

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

    /**
     * Check if this reservation should block dates in availability calculations.
     *
     * A reservation blocks dates if:
     * 1. It has a blocking status (en_attente, confirmee, en_cours, litige) OR
     * 2. Its associated delivery (if exists) is NOT completed (status !== 'terminee')
     *
     * A reservation does NOT block dates if:
     * - Reservation status is terminee, annulee, or refusee OR
     * - Reservation has a completed delivery (status = 'terminee')
     *
     * This handles the case where a reservation is marked confirmed but the delivery
     * is already marked as complete, meaning the equipment has been returned and
     * the dates should become available again.
     *
     * @return bool True if this reservation should block overlapping dates
     */
    public function shouldBlockDates(): bool
    {
        // Non-blocking statuses never block (always available)
        if (in_array($this->statut, ['annulea', 'refusee'])) {
            return false;
        }

        // If delivery exists and is marked 'terminee', don't block
        if ($this->delivery && $this->delivery->status === 'terminee') {
            return false;
        }

        // Otherwise, check if reservation status is blocking
        $blockingStatuses = ['en_attente', 'confirmee', 'en_cours', 'litige'];
        return in_array($this->statut, $blockingStatuses);
    }

}
