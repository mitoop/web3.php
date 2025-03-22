<?php

namespace Mitoop\Crypto\Tokens\Bsc;

use Mitoop\Crypto\Concerns\Chain\AbstractChain;
use Mitoop\Crypto\Concerns\Evm\Chain\EvmLike;

class Chain extends AbstractChain
{
    use EvmLike;

    protected function supportsEIP1559Transaction(): bool
    {
        return false;
    }
}
