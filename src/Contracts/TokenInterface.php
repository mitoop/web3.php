<?php

namespace Mitoop\Web3\Contracts;

use Mitoop\Web3\Transactions\TransactionInfo;
use SensitiveParameter;

interface TokenInterface extends ChainContextInterface
{
    public function getNativeCoin(): CoinInterface;

    public function getContractAddress(): string;

    public function getDecimals(): int;

    public function getBalance(string $address): string;

    /**
     * 只查询转入该地址的交易，不包含转出。
     */
    public function getTransactions(string $address, array $params = []): array;

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
