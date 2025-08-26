<?php

namespace Mitoop\Web3\Tokens\Bsc;

use Mitoop\Web3\Concerns\Evm\Traits\TokenTrait;
use Mitoop\Web3\Contracts\TokenInterface;

class Token extends ChainContext implements TokenInterface
{
    use TokenTrait;
}
