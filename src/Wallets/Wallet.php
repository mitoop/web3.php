<?php

namespace Mitoop\Crypto\Wallets;

class Wallet
{
    public function __construct(
        public string $address,
        public string $privateKey,
        public string $publicKey,
        public ?string $hexAddress = null
    ) {}
}
