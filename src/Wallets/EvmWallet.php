<?php

namespace Mitoop\Crypto\Wallets;

use Elliptic\EC;
use kornrunner\Keccak;

class EvmWallet implements WalletInterface
{
    public function generate(): Wallet
    {
        $ec = new EC('secp256k1');
        $key = $ec->genKeyPair();
        $privateKey = $key->getPrivate('hex');
        $publicKey = $key->getPublic(false, 'hex');
        $publicKey = substr($publicKey, 2);
        $hash = Keccak::hash(hex2bin($publicKey), 256);
        $address = '0x'.substr($hash, 24);

        return new Wallet($address, $privateKey, $publicKey);
    }

    public function validate(string $address): bool
    {
        if (! preg_match('/^0x[a-fA-F0-9]{40}$/', $address)) {
            return false;
        }

        if (strtolower($address) === $address || strtoupper($address) === $address) {
            return true;
        }

        $addressNoPrefix = substr($address, 2);
        $hash = Keccak::hash(strtolower($addressNoPrefix), 256);

        for ($i = 0; $i < 40; $i++) {
            $char = $addressNoPrefix[$i];
            $hashChar = hexdec($hash[$i]);

            if (($hashChar > 7 && strtoupper($char) !== $char) || ($hashChar <= 7 && strtolower($char) !== $char)) {
                return false;
            }
        }

        return true;
    }
}
