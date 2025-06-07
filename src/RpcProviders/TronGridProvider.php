<?php

namespace Mitoop\Crypto\RpcProviders;

class TronGridProvider extends AbstractRpcProvider
{
    public function getGuzzleOptions(): array
    {
        return [
            'base_uri' => $this->config('rpc_url'),
            'timeout' => $this->config('rpc_timeout', 120),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'TRON-PRO-API-KEY' => $this->config('rpc_api_key'),
            ],
        ];
    }
}
