<?php

namespace Mitoop\Crypto\RpcProviders;

class RpcProviderFactory
{
    public static function create(array $config): RpcProviderInterface
    {
        $url = strtolower($config['rpc_url'] ?? '');

        if (str_contains($url, 'infura.io')) {
            return new InfuraProvider($config);
        }

        if (str_contains($url, 'ankr.com')) {
            return new AnkrProvider($config);
        }

        if (str_contains($url, 'trongrid.io')) {
            return new TronGridProvider($config);
        }

        return new class($config) extends AbstractRpcProvider {};
    }
}
