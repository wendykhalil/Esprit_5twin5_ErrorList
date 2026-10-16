<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'country',
        'bio',
        'profile_photo',
        'role',
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
     * Support tickets opened by the user.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Check whether the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin->value;
    }

    /**
     * Check whether the user is a client.
     */
    public function isClient(): bool
    {
        return $this->role === UserRole::Client->value;
    }

    /**
     * Check whether the user is a provider.
     */
    public function isProvider(): bool
    {
        return $this->role === UserRole::Provider->value;
    }

    /**
     * Get the user's role label.
     */
    public function roleLabel(): string
    {
        return UserRole::tryFrom($this->role)?->label() ?? 'Client';
    }

    /**
     * Get the service provider profile associated with the user.
     */
    public function serviceProvider(): HasOne
    {
        return $this->hasOne(ServiceProvider::class);
    }

    /**
     * Get all service requests made by the user.
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Get all reclamations created by the user.
     */
    public function reclamations(): HasMany
    {
        return $this->hasMany(Reclamation::class);
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

        return strlen($initials) >= 2
            ? substr($initials, 0, 2)
            : $this->name[0];
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
