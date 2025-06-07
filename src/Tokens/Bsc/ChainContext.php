<?php

namespace Mitoop\Crypto\Tokens\Bsc;

use Mitoop\Crypto\Concerns\AbstractChainContext;
use Mitoop\Crypto\Concerns\Evm\Traits\EvmLikeChain;

class ChainContext extends AbstractChainContext
{
    use EvmLikeChain;

    protected function supportsEIP1559Transaction(): bool
    {
        return false;
    }
}
