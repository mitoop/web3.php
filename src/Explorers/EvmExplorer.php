<?php

namespace Mitoop\Web3\Explorers;

class EvmExplorer extends BaseExplorer
{
    public function address(string $chain, string $address): string
    {
        return sprintf('%s/address/%s', $this->baseUrl, $address);
    }

    public function transaction(string $chain, string $txId): string
    {
        return sprintf('%s/tx/%s', $this->baseUrl, $txId);
    }
}
