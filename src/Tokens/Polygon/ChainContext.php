<?php

namespace Mitoop\Crypto\Tokens\Polygon;

use Mitoop\Crypto\Concerns\AbstractChainContext;
use Mitoop\Crypto\Concerns\Evm\Traits\EvmLikeChain;

class ChainContext extends AbstractChainContext
{
    use EvmLikeChain;
}
