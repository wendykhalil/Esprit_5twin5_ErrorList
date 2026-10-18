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
        'titre',
        'description',
        'damage_percentage',
        'proposed_refund_percentage',
        'proposed_refund_amount',
        'attachment',
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

    /**
     * Check if this incident has an attached image.
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment);
    }

    /**
     * Get the full URL to the attached image.
     */
    public function getAttachmentUrl(): ?string
    {
        if (!$this->hasAttachment()) {
            return null;
        }

        return asset('storage/' . $this->attachment);
    }

    /**
     * Delete the attached image file if it exists.
     */
    public function deleteAttachment(): bool
    {
        if (!$this->hasAttachment()) {
            return true;
        }

        try {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($this->attachment);
            $this->update(['attachment' => null]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete incident attachment: ' . $e->getMessage());
            return false;
        }
    }
}
