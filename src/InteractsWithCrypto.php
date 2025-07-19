<?php

namespace Mitoop\Crypto;

use Mitoop\Crypto\Contracts\CoinInterface;
use Mitoop\Crypto\Contracts\TokenInterface;
use Mitoop\Crypto\Exceptions\InvalidArgumentException;
use Mitoop\Crypto\Explorers\ExplorerBuilder;

trait InteractsWithCrypto
{
    protected string $chain;

    protected int $chainId;

    protected string $rpcUrl;

    protected ?int $rpcTimeout = null;

    protected string $rpcApiKey;

    protected string|array $explorerUrl;

    protected ?array $explorerMap = null;

    abstract protected function getType(): string;

    /**
     * @throws InvalidArgumentException
     */
    protected function make(array $config): TokenInterface|CoinInterface
    {
        $class = sprintf('%s\\Tokens\\%s\\%s', __NAMESPACE__, ucfirst(strtolower($config['chain'])), $this->getType());

        if (! class_exists($class)) {
            throw new InvalidArgumentException("Class $class does not exist");
        }

        /** @var CoinInterface|TokenInterface $instance */
        $instance = new $class($config);

        $explorers = (new ExplorerBuilder($config['explorer_map'] ?? null))->build($config['explorer_url']);

        $instance->setExplorers($explorers);

        return $instance;
    }

    public function setChain(string $chain): static
    {
        $this->chain = $chain;

        return $this;
    }

    public function setChainId(int $chainId): static
    {
        $this->chainId = $chainId;

        return $this;
    }

    public function setRpcUrl(string $rpcUrl): static
    {
        $this->rpcUrl = $rpcUrl;

        return $this;
    }

    public function setRpcTimeout(?int $rpcTimeout): static
    {
        $this->rpcTimeout = $rpcTimeout;

        return $this;
    }

    public function setRpcApiKey(string $rpcApiKey): static
    {
        $this->rpcApiKey = $rpcApiKey;

        return $this;
    }

    public function setExplorerUrl(string|array $explorerUrl): static
    {
        $this->explorerUrl = $explorerUrl;

        return $this;
    }

    public function setExplorerMap(?array $explorerMap): static
    {
        $this->explorerMap = $explorerMap;

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function validateRequiredFields(array $config): void
    {
        foreach ($this->requiredFields() as $field) {
            if (! isset($config[$field])) {
                $type = $this->getType();
                throw new InvalidArgumentException("Missing required config field: $field for type: $type");
            }
        }
    }
}
