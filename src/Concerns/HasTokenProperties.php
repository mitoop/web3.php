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
            'chain' => $this->config('chain'),
            'chain_id' => $this->config('chain_id'),
            'rpc_url' => $this->config('rpc_url'),
            'rpc_api_key' => $this->config('rpc_api_key'),
            'rpc_timeout' => $this->config('rpc_timeout'),
            'explorer_url' => $this->config('explorer_url'),
            'explorer_map' => $this->config('explorer_map'),
        ])->build();
    }
}
