<?php

namespace Mitoop\Web3\Support\Http;

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
            $msg = $this->json($key);

            if (! is_null($msg)) {
                if (in_array($key, ['result.message', 'message'], true)) {
                    return $this->tryDecodeHex($msg);
                }

                return $msg;
            }
        }

        return sprintf('%s: %s', $this->status(), $this->body());
    }

    protected function tryDecodeHex(string $msg): string
    {
        if (ctype_xdigit($msg) && strlen($msg) % 2 === 0) {
            $decoded = hex2bin($msg);

            if (! empty($decoded)) {
                return $decoded;
            }
        }

        return $msg;
    }
}
