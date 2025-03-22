<?php

namespace Mitoop\Crypto\Tokens\Polygon;

use Mitoop\Crypto\Concerns\Evm\Token\TokenTrait;
use Mitoop\Crypto\Contracts\TokenInterface;

class Token extends Chain implements TokenInterface
{
    use TokenTrait;
}
