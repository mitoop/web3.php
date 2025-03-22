<?php

namespace Mitoop\Crypto\Tokens\Eth;

use Mitoop\Crypto\Concerns\Evm\Token\CoinTrait;
use Mitoop\Crypto\Contracts\CoinInterface;

class Coin extends Chain implements CoinInterface
{
    use CoinTrait;

    public function symbol(): string
    {
        return 'ETH';
    }
}
