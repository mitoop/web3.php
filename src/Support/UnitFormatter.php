<?php

namespace Mitoop\Web3\Support;

class UnitFormatter
{
    public static function removeTrailingZeros(string $number): string
    {
        if (! str_contains($number, '.')) {
            return $number;
        }

        return rtrim(rtrim($number, '0'), '.');
    }

    public static function formatUnits($amount, $decimals, bool $removeTrailingZeros = true): string
    {
        $amount = (string) $amount;
        $decimals = (string) $decimals;

        if (str_starts_with(strtolower($amount), '0x')) {
            $amount = gmp_strval(gmp_init($amount, 16));
        }

        $amount = bcdiv($amount, bcpow('10', $decimals, 0), $decimals);

        return $removeTrailingZeros ? self::removeTrailingZeros($amount) : $amount;
    }
}
