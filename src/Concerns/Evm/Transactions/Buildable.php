<?php

namespace Mitoop\Crypto\Concerns\Evm\Transactions;

trait Buildable
{
    public function build(string $privateKey, int $chainId = 0): string
    {
        return '0x'.$this->getRaw($privateKey, $chainId);
    }
}
