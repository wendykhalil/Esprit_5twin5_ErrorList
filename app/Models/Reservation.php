<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'equipement_id', 'date_debut', 'date_fin', 'statut', 'prix_total'];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public const STATUTS = ['en_attente', 'confirmee', 'en_cours', 'terminee', 'litige', 'annulee'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }
}
