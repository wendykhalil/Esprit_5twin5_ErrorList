<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = ['reservation_id', 'type', 'etat', 'niveau_batterie', 'observations', 'date_inspection'];

    protected $casts = ['date_inspection' => 'datetime'];

    public const TYPES = ['remise', 'retour'];
    public const ETATS = ['neuf', 'bon', 'use', 'endommage'];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
