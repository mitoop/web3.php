<?php

namespace Mitoop\Crypto\Support\Http;

class TronResponse extends Response implements BizResponseInterface
{
    public function bizOk(): bool
    {
        return $this->ok()
            && is_null($this->json('Error'))
            && is_null($this->json('result.code'))
            && $this->json('result') !== false;
    }

    public function getBizErrorMsg(): string
    {
        foreach (['Error', 'error', 'result.message', 'message'] as $key) {
            if (! is_null($msg = $this->json($key))) {
                return in_array($key, ['result.message', 'message']) ? hex2bin($msg) : $msg;
            }
        }

        return sprintf('%s: %s', $this->status(), $this->body());
    }
}
