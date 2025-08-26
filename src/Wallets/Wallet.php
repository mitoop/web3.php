<?php

namespace Mitoop\Web3\Wallets;

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
