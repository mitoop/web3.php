<?php

namespace Mitoop\Web3\Tokens\Bsc;

use Mitoop\Web3\Concerns\AbstractChainContext;
use Mitoop\Web3\Concerns\Evm\Traits\EvmLikeChain;

class ChainContext extends AbstractChainContext
{
    use EvmLikeChain;

    protected function supportsEIP1559Transaction(): bool
    {
        return false;
    }
}
