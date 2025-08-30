<?php

namespace Mitoop\Web3\Support\Traits;

trait UnitConverterTrait
{
    protected function formatUnits(string $value, string $decimals): string
    {
        return bcdiv($value, bcpow('10', $decimals, 0), $decimals);
    }

    protected function parseUnits(string $value, string $decimals): string
    {
        return bcmul($value, bcpow('10', $decimals, 0), 0);
    }
}
