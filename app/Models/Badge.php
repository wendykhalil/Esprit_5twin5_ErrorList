<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'slug', 'description', 'image', 'critere', 'seuil', 'niveau', 'actif',
    ];

    protected $casts = ['actif' => 'boolean'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['reservation_id', 'date_obtention'])
            ->withTimestamps();
    }
}
