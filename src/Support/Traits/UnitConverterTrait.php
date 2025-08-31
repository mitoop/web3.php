<?php

namespace Mitoop\Web3\Support\Traits;

trait UnitConverterTrait
{
    protected function removeTrailingZeros(string $number): string
    {
        if (! str_contains($number, '.')) {
            return $number;
        }

        return rtrim(rtrim($number, '0'), '.');
    }

    public function formatUnits(string|int $amount, string|int $decimals, bool $removeTrailingZeros = true): string
    {
        $amount = (string) $amount;
        $decimals = (string) $decimals;

        if (str_starts_with(strtolower($amount), '0x')) {
            $amount = gmp_strval(gmp_init($amount, 16));
        }

        $amount = bcdiv($amount, bcpow('10', $decimals, 0), $decimals);

        return $removeTrailingZeros ? $this->removeTrailingZeros($amount) : $amount;
    }

    protected function parseUnits(string $value, string $decimals): string
    {
        return bcmul($value, bcpow('10', $decimals, 0), 0);
    }
}
