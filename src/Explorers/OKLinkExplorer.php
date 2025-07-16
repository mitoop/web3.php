<?php

namespace Mitoop\Crypto\Explorers;

class OKLinkExplorer extends BaseExplorer
{
    protected string $lang = 'zh-hans';

    protected array $chainMap = [
        'eth' => 'ethereum',
        'bsc' => 'bsc',
        'tron' => 'tron',
        'polygon' => 'polygon',
    ];

    public function address(string $chain, string $address): string
    {
        return sprintf('%s/%s/%s/address/%s', $this->baseUrl, $this->lang, $this->mapChain(strtolower($chain)), $address);
    }

    public function transaction(string $chain, string $txId): string
    {
        return sprintf('%s/%s/%s/tx/%s', $this->baseUrl, $this->lang, $this->mapChain(strtolower($chain)), $txId);
    }

    protected function mapChain(string $chain): string
    {
        return $this->chainMap[$chain] ?? $chain;
    }
}
