<?php

namespace Mitoop\Crypto\Contracts\Tron;

use Mitoop\Crypto\Concerns\Tron\Resource;

interface TronChainContextInterface
{
    public function getAccountResource(string $address): array;

    public function stake(string $address, string $addressPrivateKey, $amount, Resource $resource): string;

    public function unStake(string $address, string $addressPrivateKey, $amount, Resource $resource): string;

    public function delegate(string $from, string $fromPrivateKey, string $to, $amount, Resource $resource): string;

    public function unDelegate(string $from, string $fromPrivateKey, string $to, $amount, Resource $resource): string;

    public function getBandwidthPrices(): array;

    public function getBandwidthPrice(): string;

    public function setBandwidthPrice(string $price): static;

    public function getEnergyPrices(): array;

    public function getEnergyPrice(): string;

    public function setEnergyPrice(string $price): static;
}
