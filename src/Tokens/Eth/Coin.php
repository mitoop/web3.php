<?php

namespace Mitoop\Web3\Tokens\Eth;

use Mitoop\Web3\Concerns\Evm\Traits\CoinTrait;
use Mitoop\Web3\Contracts\CoinInterface;

class Coin extends ChainContext implements CoinInterface
{
    use CoinTrait;

    public function symbol(): string
    {
        return 'ETH';
    }
}
