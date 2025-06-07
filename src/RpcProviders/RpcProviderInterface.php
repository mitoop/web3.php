<?php

namespace Mitoop\Crypto\RpcProviders;

interface RpcProviderInterface
{
    public function getGuzzleOptions(): array;
}
