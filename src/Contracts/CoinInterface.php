<?php

namespace Mitoop\Crypto\Contracts;

use Mitoop\Crypto\Transactions\TransactionInfo;
use SensitiveParameter;

interface CoinInterface
{
    public function symbol(): string;

    public function getDecimals(): int;

    public function getBalance(string $address, bool $asUnit = false): string;

    public function getTransaction(string $txId): ?TransactionInfo;

    public function getTransactionStatus(string $txId): bool;

    public function transfer(
        string $fromAddress,
        #[SensitiveParameter] string $fromPrivateKey,
        string $toAddress,
        string $amount
    ): string;
}
