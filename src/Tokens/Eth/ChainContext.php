<?php

namespace Mitoop\Crypto\Tokens\Eth;

use Mitoop\Crypto\Concerns\AbstractChainContext;
use Mitoop\Crypto\Concerns\Evm\Traits\EvmLikeChain;

class ChainContext extends AbstractChainContext
{
    use EvmLikeChain;
}
