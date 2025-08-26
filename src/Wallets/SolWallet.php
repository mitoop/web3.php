<?php

namespace Mitoop\Web3\Wallets;

use SodiumException;
use StephenHill\Base58;
use Throwable;

class SolWallet implements WalletInterface
{
    /**
     * @throws SodiumException
     */
    public function generate(): Wallet
    {
        $keypair = sodium_crypto_sign_keypair();
        $privateKey = sodium_crypto_sign_secretkey($keypair);
        $publicKey = sodium_crypto_sign_publickey($keypair);

        $privateKey = (new Base58)->encode($privateKey);
        $publicKey = (new Base58)->encode($publicKey);

        return new Wallet($publicKey, $privateKey, $publicKey);
    }

    public function validate(string $address): bool
    {
        if (strlen($address) != 44) {
            return false;
        }

        try {
            return strlen((new Base58)->decode($address)) === 32;
        } catch (Throwable) {
            return false;
        }
    }
}
