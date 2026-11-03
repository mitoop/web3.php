<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests;

use Mitoop\Web3\Support\Http\HttpRequestClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class HttpRequestClientTest extends TestCase
{
    #[Test]
    public function keeps_provider_timeout_when_get_timeout_is_missing(): void
    {
        $client = new class
        {
            use HttpRequestClient;

            protected function getGuzzleOptions(): array
            {
                return [
                    'base_uri' => 'https://example.com',
                    'timeout' => 120,
                ];
            }
        };

        $method = new ReflectionMethod($client, 'getBaseOptions');
        $options = $method->invoke($client);

        $this->assertSame(120, $options['timeout']);
        $this->assertTrue($options['verify']);
        $this->assertFalse($options['http_errors']);
    }

    #[Test]
    public function overrides_timeout_when_get_timeout_exists(): void
    {
        $client = new class
        {
            use HttpRequestClient;

            protected function getGuzzleOptions(): array
            {
                return [
                    'timeout' => 120,
                ];
            }

            protected function getTimeout(): int
            {
                return 30;
            }
        };

        $method = new ReflectionMethod($client, 'getBaseOptions');
        $options = $method->invoke($client);

        $this->assertSame(30, $options['timeout']);
    }
}
