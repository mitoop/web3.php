<?php

namespace Mitoop\Web3\Concerns\Evm\Traits;

trait EvmUnitConverterTrait
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

    protected function formatUnits(string $value, string $decimals): string
    {
        return bcdiv($value, bcpow('10', $decimals, 0), $decimals);
    }

    protected function parseUnits(string $value, string $decimals): string
    {
        return bcmul($value, bcpow('10', $decimals, 0), 0);
    }
}
