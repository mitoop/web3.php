<?php

namespace Mitoop\Crypto\Tokens\Polygon;

use Mitoop\Crypto\Concerns\Evm\Traits\CoinTrait;
use Mitoop\Crypto\Contracts\CoinInterface;

class Coin extends ChainContext implements CoinInterface
{
    use CoinTrait;

    public function symbol(): string
    {
        return 'POL';
    }
}
