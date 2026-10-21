<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReleveEnergie extends Model
{
    use HasFactory;

    protected $table = 'releves_energie';

    protected $fillable = [
        'reservation_id', 'puissance_watts', 'heures_utilisation',
        'kwh_estimes', 'co2_evite_kg', 'date_releve', 'observations',
        'statut_verification', 'motif_suspicion',
    ];

    protected $casts = [
        'heures_utilisation' => 'decimal:2',
        'kwh_estimes' => 'decimal:4',
        'co2_evite_kg' => 'decimal:4',
        'date_releve' => 'date',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user(): ?User
    {
        return $this->reservation?->user;
    }
}
