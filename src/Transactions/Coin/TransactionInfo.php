<?php

namespace Mitoop\Crypto\Transactions\Coin;

class TransactionInfo
{
    public function __construct(
        public string $hash,
        public string $from,
        public string $to,
        public string $amount,
    ) {}
}
