<?php

namespace Mitoop\Crypto\Support\Http;

interface BizResponseInterface
{
    public function bizOk(): bool;

    public function getBizErrorMsg(): string;
}
