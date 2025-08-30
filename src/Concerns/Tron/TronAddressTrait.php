<?php

namespace Mitoop\Web3\Concerns\Tron;

use StephenHill\Base58;

trait TronAddressTrait
{
    public function normalizeAddress(string $address): string
    {
        $hexAddress = '41'.substr($address, -40);
        $binaryAddress = hex2bin($hexAddress);
        $checksum = substr(hash('sha256', hex2bin(hash('sha256', $binaryAddress))), 0, 8);

        return (new Base58)->encode($binaryAddress.hex2bin($checksum));
    }

    public function toAbiPaddedAddress(string $address): string
    {
        return str_pad(self::toHexAddress($address), 64, '0', STR_PAD_LEFT);
    }

    public function toHexAddress(string $address, $stripTronPrefix = false): string
    {
        $binaryAddress = (new Base58)->decode($address);

        $hexAddress = bin2hex(substr($binaryAddress, 0, -4));

        return $stripTronPrefix ? substr($hexAddress, 2) : $hexAddress;
    }
}
