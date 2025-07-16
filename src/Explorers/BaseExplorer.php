<?php

namespace Mitoop\Crypto\Explorers;

abstract class BaseExplorer implements ExplorerInterface
{
    protected string $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }
}
