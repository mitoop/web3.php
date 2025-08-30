<?php

namespace Mitoop\Web3\Concerns\Evm\Traits;

trait EvmAddressTrait
{
    public function normalizeAddress(string $address): string
    {
        return '0x'.substr($address, -40);
    }

    public function toAbiPaddedAddress(string $address, bool $withPrefix = false): string
    {
        $abiAddress = str_pad(substr($address, 2), 64, '0', STR_PAD_LEFT);

        return $withPrefix ? '0x'.$abiAddress : $abiAddress;
    }
}
