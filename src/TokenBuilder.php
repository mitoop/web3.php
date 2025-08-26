<?php

namespace Mitoop\Web3;

use Mitoop\Web3\Contracts\TokenInterface;
use Mitoop\Web3\Contracts\Tron\TronTokenInterface;
use Mitoop\Web3\Exceptions\InvalidArgumentException;

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

    /**
     * @param array{
     *       chain: string,
     *       chain_id: int,
     *       contract_address: string,
     *       decimals: int,
     *       rpc_url: string,
     *       rpc_timeout: ?int,
     *       rpc_api_key: ?string,
     *       explorer_url: string|array,
     *       explorer_map: ?array
     *   } $config Configuration for initializing the token instance.
     */
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
        return ['chain', 'chain_id', 'contract_address', 'decimals', 'rpc_url', 'explorer_url'];
    }
}
