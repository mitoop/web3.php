<?php

namespace Mitoop\Crypto\RpcProviders;

abstract class AbstractRpcProvider implements RpcProviderInterface
{
    public function __construct(protected array $config) {}

    protected function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function getGuzzleOptions(): array
    {
        return [
            'base_uri' => $this->config('rpc_url'),
            'timeout' => $this->config('rpc_timeout', 120),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];
    }
}
