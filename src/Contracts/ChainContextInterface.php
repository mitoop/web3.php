<?php

namespace Mitoop\Crypto\Contracts;

use Mitoop\Crypto\Explorers\ExplorerType;
use Mitoop\Crypto\Support\Http\BizResponseInterface;
use Mitoop\Crypto\Support\Http\HttpMethod;
use Mitoop\Crypto\Wallets\Wallet;

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
