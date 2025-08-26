<?php

namespace Mitoop\Web3\Tokens\Eth;

use Mitoop\Web3\Concerns\AbstractChainContext;
use Mitoop\Web3\Concerns\Evm\Traits\EvmLikeChain;

class ChainContext extends AbstractChainContext
{
    use EvmLikeChain;
}
