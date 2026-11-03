<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests\Integration;

use Mitoop\Web3\Exceptions\RpcException;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class IntegrationTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('WEB3_RUN_INTEGRATION') !== '1') {
            $this->markTestSkipped('Set WEB3_RUN_INTEGRATION=1 in .env to run live testnet transfers.');
        }
    }

    protected function env(string $key): string
    {
        $value = getenv($key);

        if ($value === false || $value === '') {
            $this->markTestSkipped("Missing {$key} in .env");
        }

        return $value;
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    protected function rpc(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (RpcException $e) {
            $this->markTestSkipped('RPC unavailable: '.$e->getMessage());
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'cURL error') || str_contains($e::class, 'ConnectException')) {
                $this->markTestSkipped('RPC unavailable: '.$e->getMessage());
            }

            throw $e;
        }
    }
}
