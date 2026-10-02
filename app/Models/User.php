<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'country',
        'bio',
        'profile_photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get all equipment owned by the user.
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    /**
     * Get all payments made by the user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the profile photo URL.
     */
    public function getProfilePhotoUrl(): ?string
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }

        return null;
    }

    /**
     * Get user initials for avatar fallback.
     */
    public function getInitials(): string
    {
        $names = explode(' ', trim($this->name));
        $initials = '';

        foreach ($names as $name) {
            if (!empty($name)) {
                $initials .= strtoupper(substr($name, 0, 1));
            }
        }

        return strlen($initials) >= 2 ? substr($initials, 0, 2) : $this->name[0];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}