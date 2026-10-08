<?php

namespace App\Enums;

enum UserRole: string
{
    case Client = 'client';
    case Provider = 'provider';
    case Admin = 'admin';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Client => 'Client',
            self::Provider => 'Prestataire',
            self::Admin => 'Administrateur',
        };
    }
}
