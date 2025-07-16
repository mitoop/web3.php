<?php

namespace Mitoop\Crypto\Concerns;

use Mitoop\Crypto\Contracts\ChainContextInterface;
use Mitoop\Crypto\Exceptions\InvalidArgumentException;
use Mitoop\Crypto\Explorers\ExplorerInterface;
use Mitoop\Crypto\Explorers\ExplorerType;
use Mitoop\Crypto\RpcProviders\RpcProviderFactory;
use Mitoop\Crypto\Support\Http\BizResponseInterface;
use Mitoop\Crypto\Support\Http\HttpRequestClient;
use Mitoop\Crypto\Support\Http\Response;
use Mitoop\Crypto\Wallets\Factory;
use Mitoop\Crypto\Wallets\Wallet;

/**
 * @method BizResponseInterface|Response postJson($endpoint, $jsonData = [], $headers = [])
 */
abstract class AbstractChainContext implements ChainContextInterface
{
    use HttpRequestClient;

    protected array $explorers = [];

    public function __construct(protected array $config) {}

    public function config(string $key, $default = null)
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
