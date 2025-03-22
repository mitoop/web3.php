<?php

namespace Mitoop\Crypto\Tokens\Eth;

use Mitoop\Crypto\Concerns\Chain\AbstractChain;
use Mitoop\Crypto\Concerns\Evm\Chain\EvmLike;

class Chain extends AbstractChain
{
    use EvmLike;
}
