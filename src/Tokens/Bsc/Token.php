<?php

namespace Mitoop\Crypto\Tokens\Bsc;

use Mitoop\Crypto\Concerns\Evm\Traits\TokenTrait;
use Mitoop\Crypto\Contracts\TokenInterface;

class Token extends ChainContext implements TokenInterface
{
    use TokenTrait;
}
