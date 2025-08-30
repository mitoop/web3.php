<?php

namespace Mitoop\Web3\Support\Traits;

trait NumberConverterTrait
{
    protected function decimalToHex(string $value, bool $withPrefix = true): string
    {
        $hex = gmp_strval(gmp_init($value, 10), 16);

        return $withPrefix ? '0x'.$hex : $hex;
    }

    protected function hexToDecimal(string $hexValue): string
    {
        $hexValue = str_starts_with($hexValue, '0x') ? substr($hexValue, 2) : $hexValue;

        return gmp_strval(gmp_init($hexValue, 16));
    }
}
