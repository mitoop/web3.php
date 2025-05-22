<?php

namespace Mitoop\Crypto\Support;

class NumberFormatter
{
    public static function removeTrailingZeros(string $number): string
    {
        if (! str_contains($number, '.')) {
            return $number;
        }

        return rtrim(rtrim($number, '0'), '.');
    }

    public static function toDecimalAmount($amount, $decimals, bool $removeTrailingZeros = true): string
    {
        $decimals = (string) $decimals;

        if (str_starts_with(strtolower($amount), '0x')) {
            $amount = gmp_strval(gmp_init($amount, 16));
        }

        $decimalAmount = bcdiv($amount, bcpow(10, $decimals), $decimals);

        return $removeTrailingZeros ? self::removeTrailingZeros($decimalAmount) : $decimalAmount;
    }
}
