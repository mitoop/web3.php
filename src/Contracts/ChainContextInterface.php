<?php

namespace Mitoop\Web3\Contracts;

use Mitoop\Web3\Explorers\ExplorerType;
use Mitoop\Web3\Support\Http\BizResponseInterface;
use Mitoop\Web3\Support\Http\HttpMethod;
use Mitoop\Web3\Wallets\Wallet;

interface ChainContextInterface
{
    public function generateWallet(): Wallet;

    public function validateAddress(string $address): bool;

    public function getChainId(bool $preferLocal = true): int;

    public function getLatestBlockNum(): string;

    public function getNativeCoinDecimals(): int;

    public function rpcRequest(string $method, array $params = [], HttpMethod $httpMethod = HttpMethod::POST): BizResponseInterface;

    public function getExplorerAddressUrl(string $address, ?ExplorerType $type = null): string;

    public function getExplorerTransactionUrl(string $txId, ?ExplorerType $type = null): string;
}
