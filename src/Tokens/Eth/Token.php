<?php

namespace Mitoop\Crypto\Tokens\Eth;

use Mitoop\Crypto\Concerns\Evm\Traits\TokenTrait;
use Mitoop\Crypto\Contracts\TokenInterface;

class Token extends ChainContext implements TokenInterface
{
    use TokenTrait;
}
