<?php

namespace Mitoop\Crypto\Tokens\Polygon;

use Mitoop\Crypto\Concerns\AbstractChainContext;
use Mitoop\Crypto\Concerns\Evm\Chain\EvmLike;

class ChainContext extends AbstractChainContext
{
    use EvmLike;
}
