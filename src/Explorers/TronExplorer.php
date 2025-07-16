<?php

namespace Mitoop\Crypto\Explorers;

class TronExplorer extends BaseExplorer
{
    public function address(string $chain, string $address): string
    {
        return sprintf('%s/#/address/%s', $this->baseUrl, $address);
    }

    public function transaction(string $chain, string $txId): string
    {
        return sprintf('%s/#/transaction/%s', $this->baseUrl, $txId);
    }
}
