<?php

namespace Mitoop\Crypto\Tokens\Bsc;

use Mitoop\Crypto\Concerns\AbstractChainContext;
use Mitoop\Crypto\Concerns\Evm\Chain\EvmLike;

class ChainContext extends AbstractChainContext
{
    use EvmLike;

    protected function supportsEIP1559Transaction(): bool
    {
        return false;
    }
}
