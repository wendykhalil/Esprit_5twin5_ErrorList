<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'equipment_id', 'date_debut', 'date_fin', 'statut', 'prix_total'];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
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
}
