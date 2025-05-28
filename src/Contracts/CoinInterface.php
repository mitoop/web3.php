<?php

namespace Mitoop\Crypto\Contracts;

use Mitoop\Crypto\Transactions\Coin\TransactionInfo;

interface CoinInterface
{
    public function symbol(): string;

    public function getDecimals(): int;

    public function getBalance(string $address, bool $asDisplayAmount = false): string;

    public function getTransaction(string $txId): ?TransactionInfo;

    public function getTransactionStatus(string $txId): bool;

    public function transfer(string $fromAddress, string $fromPrivateKey, string $toAddress, string $amount): string;
}
