<?php

namespace Mitoop\Crypto\Concerns\Evm\Traits;

trait AddressFormatter
{
    public function toAddressFormat(string $address): string
    {
        return '0x'.substr($address, -40);
    }

    public function toPaddedAddress(string $address, bool $withPrefix = false): string
    {
        $paddedAddress = str_pad(substr($address, 2), 64, '0', STR_PAD_LEFT);

        return $withPrefix ? '0x'.$paddedAddress : $paddedAddress;
    }
}
