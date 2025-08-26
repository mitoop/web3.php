<?php

namespace Mitoop\Web3\Support\Http;

use Psr\Http\Message\ResponseInterface;

class Response
{
    protected mixed $decoded = null;

    public function __construct(protected ResponseInterface $response) {}

    public function body(): string
    {
        return (string) $this->response->getBody();
    }

    public function json($key = null, $default = null): mixed
    {
        if (! $this->decoded) {
            $this->decoded = json_decode($this->body(), true);
        }

        if (is_null($key)) {
            return $this->decoded;
        }

        $keys = explode('.', $key);
        $target = $this->decoded;

        foreach ($keys as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } else {
                return $default;
            }
        }

        return $target;
    }

    public function header(string $header): string
    {
        return $this->response->getHeaderLine($header);
    }

    public function headers(): array
    {
        return $this->response->getHeaders();
    }

    public function status(): int
    {
        return $this->response->getStatusCode();
    }

    public function ok(): bool
    {
        return $this->status() === 200;
    }

    public function __call($method, $parameters)
    {
        return $this->response->{$method}(...$parameters);
    }
}
