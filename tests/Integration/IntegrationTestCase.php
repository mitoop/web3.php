<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests\Integration;

use Mitoop\Web3\Contracts\TokenInterface;
use Mitoop\Web3\Exceptions\RpcException;
use Mitoop\Web3\TokenBuilder;
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

    protected function envInt(string $key): int
    {
        return (int) $this->env($key);
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

    /**
     * @param  array{
     *     chain: string,
     *     chain_id: int,
     *     contract_address: string,
     *     decimals: int,
     *     rpc_url: string,
     *     rpc_api_key?: string,
     *     explorer_url: string
     * }  $config
     */
    protected function token(array $config): TokenInterface
    {
        return TokenBuilder::fromArray([
            'chain' => $config['chain'],
            'chain_id' => $config['chain_id'],
            'contract_address' => $config['contract_address'],
            'decimals' => $config['decimals'],
            'rpc_url' => $config['rpc_url'],
            'rpc_api_key' => $config['rpc_api_key'] ?? '',
            'explorer_url' => $config['explorer_url'],
        ])->build();
    }

    protected function assertTxHash(string $hash, bool $evm = true): void
    {
        $this->assertNotSame('', $hash);

        if ($evm) {
            $this->assertMatchesRegularExpression('/^0x[a-fA-F0-9]{64}$/', $hash);
        } else {
            $this->assertMatchesRegularExpression('/^[a-fA-F0-9]{64}$/', $hash);
        }
    }
}
