<?php

namespace Mitoop\Web3\Concerns\Evm\Transactions;

use SensitiveParameter;

trait Buildable
{
    public function build(#[SensitiveParameter] string $privateKey, int $chainId = 0): string
    {
        return '0x'.$this->getRaw($privateKey, $chainId);
    }
}
