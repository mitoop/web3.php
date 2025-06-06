<?php

namespace Mitoop\Crypto\Tokens\Eth;

use Mitoop\Crypto\Concerns\AbstractChainContext;
use Mitoop\Crypto\Concerns\Evm\Chain\EvmLike;

class ChainContext extends AbstractChainContext
{
    use EvmLike;
}
