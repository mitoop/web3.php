<?php

namespace Mitoop\Web3\Wallets;

use Elliptic\EC;
use kornrunner\Keccak;
use StephenHill\Base58;
use Throwable;

class TronWallet implements WalletInterface
{
    public function generate(): Wallet
    {
        $ec = new EC('secp256k1');
        $key = $ec->genKeyPair();

        $privateKey = $key->getPrivate('hex');
        $publicKey = $key->getPublic('hex');

        $hexAddress = $this->generateHexAddress($publicKey);
        $address = $this->convertHexToBase58($hexAddress);

        return new Wallet($address, $privateKey, $publicKey, $hexAddress);
    }

    protected function generateHexAddress(string $publicKey): string
    {
        $hash = Keccak::hash(substr(hex2bin($publicKey), -64), 256);

        return '41'.substr($hash, 24);
    }

    protected function convertHexToBase58(string $hexString): string
    {
        $address = hex2bin($hexString);
        $checksum = substr(hash('sha256', hex2bin(hash('sha256', $address))), 0, 8);
        $address .= hex2bin($checksum);

        return (new Base58)->encode($address);
    }

    public function validate(string $address): bool
    {
        try {
            $decoded = (new Base58)->decode($address);
            $decodedHex = bin2hex($decoded);

            if (strlen($decodedHex) !== 50) {
                return false;
            }

            $prefix = substr($decodedHex, 0, 2);
            if ($prefix !== '41') {
                return false;
            }

            $addressData = substr($decoded, 0, 21);
            $checksum = substr($decoded, 21, 4);

            $hash0 = hash('sha256', $addressData, true);
            $hash1 = hash('sha256', $hash0, true);
            $validChecksum = substr($hash1, 0, 4);

            return $checksum === $validChecksum;
        } catch (Throwable) {
            return false;
        }
    }
}
