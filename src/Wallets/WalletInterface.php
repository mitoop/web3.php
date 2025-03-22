<?php

namespace Mitoop\Crypto\Wallets;

interface WalletInterface
{
    public function generate(): Wallet;

    public function validate(string $address): bool;
}
