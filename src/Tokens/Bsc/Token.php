<?php

namespace Mitoop\Crypto\Tokens\Bsc;

use Mitoop\Crypto\Concerns\Evm\Token\TokenTrait;
use Mitoop\Crypto\Contracts\TokenInterface;

class Token extends Chain implements TokenInterface
{
    use TokenTrait;
}
