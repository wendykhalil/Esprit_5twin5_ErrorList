<?php

namespace App\Support;

final class ImpactFormatter
{
    public static function energy(float|int|null $kwh): string
    {
        $value = (float) $kwh;
        if ($value < 1) {
            return self::number($value * 1000).' Wh';
        }

        return self::number($value).' kWh';
    }

    public static function co2(float|int|null $kg): string
    {
        $value = (float) $kg;
        if ($value < 1) {
            return self::number($value * 1000).' g de CO2';
        }

        return self::number($value).' kg de CO2';
    }

    public static function number(float|int|null $value): string
    {
        return number_format((float) $value, 2, ',', ' ');
    }
}
