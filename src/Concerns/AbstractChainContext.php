<?php

namespace Mitoop\Web3\Concerns;

use Mitoop\Web3\Contracts\ChainContextInterface;
use Mitoop\Web3\Exceptions\InvalidArgumentException;
use Mitoop\Web3\Explorers\ExplorerInterface;
use Mitoop\Web3\Explorers\ExplorerType;
use Mitoop\Web3\RpcProviders\RpcProviderFactory;
use Mitoop\Web3\Support\Http\BizResponseInterface;
use Mitoop\Web3\Support\Http\HttpRequestClient;
use Mitoop\Web3\Support\Http\Response;
use Mitoop\Web3\Wallets\Factory;
use Mitoop\Web3\Wallets\Wallet;

/**
 * @method BizResponseInterface|Response postJson($endpoint, $jsonData = [], $headers = [])
 */
abstract class AbstractChainContext implements ChainContextInterface
{
    use HttpRequestClient;

    protected array $explorers = [];

    public function __construct(protected array $config) {}

    public function config(string $key, $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function setExplorers($explorers): static
    {
        $this->explorers = $explorers;

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function explorer(?ExplorerType $type = null): ExplorerInterface
    {
        if ($type === null) {
            return reset($this->explorers);
        }

        return $this->explorers[$type->value] ?? throw new InvalidArgumentException("Explorer for type '{$type->value}' not found.");
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getExplorerAddressUrl(string $address, ?ExplorerType $type = null): string
    {
        return $this->explorer($type)->address($this->config('chain'), $address);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getExplorerTransactionUrl(string $txId, ?ExplorerType $type = null): string
    {
        return $this->explorer($type)->transaction($this->config('chain'), $txId);
    }

    public function generateWallet(): Wallet
    {
        return Factory::create($this->config('chain'))->generate();
    }

    public function validateAddress(string $address): bool
    {
        return Factory::create($this->config('chain'))->validate($address);
    }

    protected function getGuzzleOptions(): array
    {
        return RpcProviderFactory::create($this->config)->getGuzzleOptions();
    }
}
