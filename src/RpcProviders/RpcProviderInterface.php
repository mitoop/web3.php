<?php

namespace Mitoop\Web3\RpcProviders;

interface RpcProviderInterface
{
    public function getGuzzleOptions(): array;
}
