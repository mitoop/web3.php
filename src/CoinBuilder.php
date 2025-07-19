<?php

namespace Mitoop\Crypto;

use Mitoop\Crypto\Contracts\CoinInterface;
use Mitoop\Crypto\Contracts\Tron\TronCoinInterface;
use Mitoop\Crypto\Exceptions\InvalidArgumentException;

class CoinBuilder
{
    use InteractsWithCrypto;

    protected function getType(): string
    {
        return 'Coin';
    }

    /**
     * @throws InvalidArgumentException
     */
    public function build(): CoinInterface|TronCoinInterface
    {
        $config = [
            'chain' => $this->chain,
            'chain_id' => $this->chainId,
            'rpc_url' => $this->rpcUrl,
            'rpc_timeout' => $this->rpcTimeout,
            'rpc_api_key' => $this->rpcApiKey,
            'explorer_url' => $this->explorerUrl,
            'explorer_map' => $this->explorerMap,
        ];

        $this->validateRequiredFields($config);

        return $this->make($config);
    }

    public static function fromArray(array $config): static
    {
        return (new static)
            ->setChain($config['chain'])
            ->setChainId($config['chain_id'])
            ->setRpcUrl($config['rpc_url'])
            ->setRpcTimeout($config['rpc_timeout'] ?? null)
            ->setRpcApiKey($config['rpc_api_key'])
            ->setExplorerUrl($config['explorer_url'])
            ->setExplorerMap($config['explorer_map'] ?? null);
    }

    protected function requiredFields(): array
    {
        return ['chain', 'chain_id', 'rpc_url', 'rpc_api_key', 'explorer_url'];
    }
}
