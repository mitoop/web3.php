<?php

namespace Mitoop\Web3\Wallets;

interface WalletInterface
{
    public function generate(): Wallet;

    public function validate(string $address): bool;
}
