<?php

namespace Mitoop\Crypto\Concerns;

use Mitoop\Crypto\Contracts\CoinInterface;
use Mitoop\Crypto\Factory;

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
        return Factory::createCoin([
            'chain' => $this->config('chain'),
            'chain_id' => $this->getChainId(),
            'rpc_url' => $this->config('rpc_url'),
            'rpc_api_key' => $this->config('rpc_api_key'),
            'explorer_url' => $this->config('explorer_url'),
        ]);
    }
}
