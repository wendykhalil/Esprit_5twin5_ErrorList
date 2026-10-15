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
    ];

    protected $casts = [
        'heures_utilisation' => 'decimal:2',
        'kwh_estimes' => 'decimal:2',
        'co2_evite_kg' => 'decimal:2',
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
