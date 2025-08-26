<?php

namespace Mitoop\Web3\Support\Http;

interface BizResponseInterface
{
    public function bizOk(): bool;

    public function getBizErrorMsg(): string;
}
