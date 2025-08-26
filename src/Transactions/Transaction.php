<?php

namespace Mitoop\Web3\Transactions;

class Transaction
{
    public function __construct(
        public string $hash,
        public string $contractAddress,
        public string $fromAddress,
        public string $toAddress,
        public string $value,
        public string $amount,
        public int $decimals,
    ) {}
}
