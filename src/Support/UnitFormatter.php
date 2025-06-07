<?php

namespace Mitoop\Crypto\Support;

class UnitFormatter
{
    public static function removeTrailingZeros(string $number, $decimals): string
    {
        return bcadd($number, '0', $decimals);
    }

    public static function formatUnits($amount, $decimals, bool $removeTrailingZeros = true): string
    {
        $decimals = (string) $decimals;

        if (str_starts_with(strtolower($amount), '0x')) {
            $amount = gmp_strval(gmp_init($amount, 16));
        }

        $amount = bcdiv($amount, bcpow(10, $decimals, 0), $decimals);

        return $removeTrailingZeros ? self::removeTrailingZeros($amount, $decimals) : $amount;
    }
}
