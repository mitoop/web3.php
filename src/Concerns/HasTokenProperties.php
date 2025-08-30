<?php

namespace Mitoop\Web3\Concerns;

use Mitoop\Web3\CoinBuilder;
use Mitoop\Web3\Contracts\CoinInterface;

trait HasTokenProperties
{
    public function getDecimals(): int
    {
        return (int) $this->config('decimals');
    }

    public function getContractAddress(): string
    {
        return $this->config('contract_address');
    }

    public function getNativeCoin(): CoinInterface
    {
        return CoinBuilder::fromArray([
            'chain' => (string) $this->config('chain'),
            'chain_id' => (int) $this->config('chain_id'),
            'rpc_url' => (string) $this->config('rpc_url'),
            'rpc_api_key' => (string) $this->config('rpc_api_key'),
            'rpc_timeout' => (int) $this->config('rpc_timeout'),
            'explorer_url' => (array) $this->config('explorer_url'),
            'explorer_map' => (array) $this->config('explorer_map'),
        ])->build();
    }
}
