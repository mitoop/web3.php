<?php

namespace Mitoop\Crypto\RpcProviders;

class RpcProviderFactory
{
    public static function create(array $config): RpcProviderInterface
    {
        $url = strtolower($config['rpc_url'] ?? '');

        if (str_contains($url, 'trongrid.io')) {
            return new TronGridProvider($config);
        }

        return new class($config) extends AbstractRpcProvider {};
    }
}
