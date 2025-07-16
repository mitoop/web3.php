<?php

namespace Mitoop\Crypto;

use Mitoop\Crypto\Contracts\CoinInterface;
use Mitoop\Crypto\Contracts\TokenInterface;
use Mitoop\Crypto\Contracts\Tron\TronCoinInterface;
use Mitoop\Crypto\Contracts\Tron\TronTokenInterface;
use Mitoop\Crypto\Exceptions\InvalidArgumentException;
use Mitoop\Crypto\Explorers\ExplorerBuilder;

class Factory
{
    /**
     * @param array{
     *       chain: string,
     *       chain_id: int,
     *       contract_address: string,
     *       decimals: int,
     *       rpc_url: string,
     *       rpc_timeout: ?int,
     *       rpc_api_key: string,
     *       explorer_url: string|array
     *   } $config Configuration for initializing the token instance.
     *
     * @throws InvalidArgumentException
     */
    public static function createToken(array $config): TokenInterface|TronTokenInterface
    {
        return self::create('Token', $config, ['chain', 'chain_id', 'contract_address', 'decimals', 'rpc_url', 'rpc_api_key', 'explorer_url']);
    }

    /**
     * @param array{
     *        chain: string,
     *        chain_id: int,
     *        rpc_url: string,
     *        rpc_timeout: ?int,
     *        rpc_api_key: string,
     *        explorer_url: string|array
     *    } $config Configuration for initializing the coin instance.
     *
     * @throws InvalidArgumentException
     */
    public static function createCoin(array $config): CoinInterface|TronCoinInterface
    {
        return self::create('Coin', $config, ['chain', 'chain_id', 'rpc_url', 'rpc_api_key', 'explorer_url']);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected static function create(string $type, array $config, array $requiredFields): CoinInterface|TokenInterface
    {
        foreach ($requiredFields as $field) {
            if (! isset($config[$field])) {
                throw new InvalidArgumentException("Missing required config field: $field");
            }
        }

        $class = sprintf('%s\\Tokens\\%s\\%s', __NAMESPACE__, ucfirst(strtolower($config['chain'])), ucfirst(strtolower($type)));

        if (! class_exists($class)) {
            throw new InvalidArgumentException("Class $class does not exist");
        }

        /** @var CoinInterface|TokenInterface $instance */
        $instance = new $class($config);

        $explorers = (new ExplorerBuilder($config['explorer_map'] ?? null))->build($config['explorer_url']);

        $instance->setExplorers($explorers);

        return $instance;
    }
}
