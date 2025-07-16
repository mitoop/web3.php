<?php

namespace Mitoop\Crypto\Explorers;

use Mitoop\Crypto\Support\Chain;

class OKLinkExplorer extends BaseExplorer
{
    protected string $lang = 'zh-hans';

    protected array $chainMap = [
        Chain::ETH->value => 'ethereum',
        Chain::BSC->value => 'bsc',
        Chain::TRON->value => 'tron',
        Chain::POLYGON->value => 'polygon',
    ];

    public function address(string $chain, string $address): string
    {
        return sprintf('%s/%s/%s/address/%s', $this->baseUrl, $this->lang, $this->mapChain(strtolower($chain)), $address);
    }

    public function transaction(string $chain, string $txId): string
    {
        $chain = strtolower($chain);

        if ($chain === Chain::TRON->value && str_starts_with($txId, '0x')) {
            $txId = substr($txId, 2);
        }

        return sprintf('%s/%s/%s/tx/%s', $this->baseUrl, $this->lang, $this->mapChain($chain), $txId);
    }

    protected function mapChain(string $chain): string
    {
        return $this->chainMap[$chain] ?? $chain;
    }
}
