<?php

namespace Mitoop\Web3\Tokens\Bsc;

use Mitoop\Web3\Concerns\AbstractChainContext;

abstract class ChainContext extends AbstractChainContext
{
    protected function supportsEIP1559Transaction(): bool
    {
        return false;
    }
}
