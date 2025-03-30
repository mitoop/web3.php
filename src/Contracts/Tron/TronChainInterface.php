<?php

namespace Mitoop\Crypto\Contracts\Tron;

use Mitoop\Crypto\Concerns\Tron\Resource;

interface TronChainInterface
{
    public function getAccountResource(string $address): array;

    public function stake(string $address, string $addressPrivateKey, $amount, Resource $resource): string;

    public function unStake(string $address, string $addressPrivateKey, $amount, Resource $resource): string;

    public function delegate(string $from, string $fromPrivateKey, string $to, $amount, Resource $resource): string;

    public function unDelegate(string $from, string $fromPrivateKey, string $to, $amount, Resource $resource): string;
}
