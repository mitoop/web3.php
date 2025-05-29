<?php

namespace Mitoop\Crypto\Wallets;

use SensitiveParameter;

class Wallet
{
    public function __construct(
        public string $address,
        #[SensitiveParameter] public string $privateKey,
        public string $publicKey,
        public ?string $hexAddress = null
    ) {}
}
