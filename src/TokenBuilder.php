<?php

namespace Mitoop\Crypto;

use Mitoop\Crypto\Contracts\TokenInterface;
use Mitoop\Crypto\Contracts\Tron\TronTokenInterface;
use Mitoop\Crypto\Exceptions\InvalidArgumentException;

class TokenBuilder
{
    use InteractsWithCrypto;

    protected string $contractAddress;

    protected int $decimals;

    protected function getType(): string
    {
        return 'Token';
    }

    public function setContractAddress(string $contractAddress): static
    {
        $this->contractAddress = $contractAddress;

        return $this;
    }

    public function setDecimals(int $decimals): static
    {
        $this->decimals = $decimals;

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function build(): TokenInterface|TronTokenInterface
    {
        $config = [
            'chain' => $this->chain,
            'chain_id' => $this->chainId,
            'contract_address' => $this->contractAddress,
            'decimals' => $this->decimals,
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
            ->setContractAddress($config['contract_address'])
            ->setDecimals($config['decimals'])
            ->setRpcUrl($config['rpc_url'])
            ->setRpcTimeout($config['rpc_timeout'] ?? null)
            ->setRpcApiKey($config['rpc_api_key'])
            ->setExplorerUrl($config['explorer_url'])
            ->setExplorerMap($config['explorer_map'] ?? null);
    }

    protected function requiredFields(): array
    {
        return ['chain', 'chain_id', 'contract_address', 'decimals', 'rpc_url', 'rpc_api_key', 'explorer_url'];
    }
}
