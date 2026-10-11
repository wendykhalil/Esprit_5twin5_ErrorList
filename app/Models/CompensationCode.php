<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompensationCode extends Model
{
    use HasFactory;

    protected $table = 'compensation_codes';

    protected $fillable = [
        'code',
        'type',
        'value',
        'valid_from',
        'valid_until',
        'max_uses',
        'uses_count',
        'is_active',
        'user_id',
        'reclamation_id',
        'description',
        'created_by',
        'last_used_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    // ========== RELATIONS ==========

    /**
     * Get the user (client) who owns this compensation code.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reclamation that originated this code.
     */
    public function reclamation(): BelongsTo
    {
        return $this->belongsTo(Reclamation::class);
    }

    /**
     * Get the admin who created this code.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ========== SCOPES ==========

    /**
     * Only active codes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Only codes that have not yet expired.
     */
    public function scopeNotExpired($query)
    {
        return $query->where('valid_until', '>', now());
    }

    /**
     * Only codes whose validity period has started.
     */
    public function scopeStarted($query)
    {
        return $query->where('valid_from', '<=', now());
    }

    /**
     * Only codes with remaining uses.
     */
    public function scopeAvailable($query)
    {
        return $query->whereRaw('uses_count < max_uses');
    }

    /**
     * Only codes belonging to a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get all codes that can be used right now (active, not expired, started, available).
     */
    public function scopeUsable($query)
    {
        return $query
            ->active()
            ->notExpired()
            ->started()
            ->available();
    }

    // ========== BUSINESS METHODS ==========

    /**
     * Check if this code is valid for use.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->is_active
            && now()->isBetween($this->valid_from, $this->valid_until)
            && $this->uses_count < $this->max_uses;
    }

    /**
     * Calculate the discount amount for a given base price.
     *
     * @param float $basePrice The base price before discount
     * @return float The discount amount (always positive, never exceeding base price)
     */
    public function calculateDiscount(float $basePrice): float
    {
        if ($this->type === 'percentage') {
            $discount = round($basePrice * (float) $this->value / 100, 2);
        } else {
            // Type is 'fixed'
            $discount = (float) $this->value;
        }

        // Never discount more than the base price
        return min($discount, $basePrice);
    }

    /**
     * Check if there are remaining uses.
     *
     * @return bool
     */
    public function hasUsesRemaining(): bool
    {
        return $this->uses_count < $this->max_uses;
    }

    /**
     * Check if the code is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return now()->isAfter($this->valid_until);
    }

    /**
     * Check if the code validity has started.
     *
     * @return bool
     */
    public function hasStarted(): bool
    {
        return now()->isAfter($this->valid_from) || now()->eq($this->valid_from);
    }

    /**
     * Get human-readable status for display.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'Désactivé';
        }

        if ($this->isExpired()) {
            return 'Expiré';
        }

        if (!$this->hasStarted()) {
            return 'Pas encore valide';
        }

        if (!$this->hasUsesRemaining()) {
            return 'Utilisé';
        }

        return 'Disponible';
    }

    /**
     * Generate a unique compensation code.
     *
     * @return string
     */
    public static function generateCode(): string
    {
        do {
            // Generate 12-character alphanumeric code with prefix
            $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $code = 'CC-';
            for ($i = 0; $i < 12; $i++) {
                $code .= $chars[rand(0, strlen($chars) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Create a compensation code from a reclamation.
     *
     * @param Reclamation $reclamation
     * @param string $type 'percentage' or 'fixed'
     * @param float $value Discount value
     * @param int $daysValid Number of days the code is valid from now
     * @param int $maxUses Maximum number of uses (default 1)
     * @param int|null $createdBy Admin user ID who created this
     * @return self
     */
    public static function createFromReclamation(
        Reclamation $reclamation,
        string $type,
        float $value,
        int $daysValid = 30,
        int $maxUses = 1,
        ?int $createdBy = null
    ): self {
        return self::create([
            'code' => self::generateCode(),
            'type' => $type,
            'value' => $value,
            'valid_from' => now(),
            'valid_until' => now()->addDays($daysValid),
            'max_uses' => $maxUses,
            'uses_count' => 0,
            'is_active' => true,
            'user_id' => $reclamation->user_id,
            'reclamation_id' => $reclamation->id,
            'created_by' => $createdBy,
            'description' => "Compensation pour réclamation #{$reclamation->id}",
        ]);
    }
}
