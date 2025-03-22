<?php

namespace Mitoop\Crypto\Support\Http;

class EvmResponse extends Response implements BizResponseInterface
{
    public function bizOk(): bool
    {
        return $this->ok() && is_null($this->json('error'));
    }

    public function getBizErrorMsg(): string
    {
        return sprintf('%s:%s', $this->json('error.code'), $this->json('error.message'));
    }
}
