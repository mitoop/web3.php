<?php

namespace Mitoop\Crypto\Contracts;

use Mitoop\Crypto\Transactions\TransactionInfo;
use SensitiveParameter;

interface TokenInterface extends ChainContextInterface
{
    public function getNativeCoin(): CoinInterface;

    public function getContractAddress(): string;

    public function getDecimals(): int;

    public function getBalance(string $address): string;

    public function getTransactions($address, array $params = []): array;

    public function getTransaction(string $txId): ?TransactionInfo;

    public function getTransactionStatus(string $txId): bool;

    public function transfer(
        string $fromAddress,
        #[SensitiveParameter] string $fromPrivateKey,
        string $toAddress,
        string $amount,
        bool $bestEffort = false
    ): string;
}
