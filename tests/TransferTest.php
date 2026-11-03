<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests;

use Mitoop\Web3\Contracts\CoinInterface;
use Mitoop\Web3\Exceptions\BalanceShortageException;
use Mitoop\Web3\Exceptions\InvalidArgumentException;
use Mitoop\Web3\Support\Http\BizResponseInterface;
use Mitoop\Web3\Support\Http\HttpMethod;
use Mitoop\Web3\Tokens\Eth\Coin as EthCoin;
use Mitoop\Web3\Tokens\Eth\Token as EthToken;
use Mitoop\Web3\Tokens\Tron\Coin as TronCoin;
use Mitoop\Web3\Tokens\Tron\Token as TronToken;
use Mitoop\Web3\Transactions\TransactionInfo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SensitiveParameter;

class TransferTest extends TestCase
{
    #[Test]
    public function evm_token_rejects_non_positive_amount(): void
    {
        $token = $this->ethToken();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid amount');

        $token->transfer('0xfrom', 'private', '0xto', '0');
    }

    #[Test]
    public function evm_token_rejects_insufficient_balance(): void
    {
        $token = $this->ethToken(tokenBalance: '1');

        $this->expectException(BalanceShortageException::class);

        $token->transfer('0xfrom', 'private', '0xto', '2');
    }

    #[Test]
    public function evm_token_best_effort_uses_available_balance(): void
    {
        $token = $this->ethToken(tokenBalance: '1.25', nativeBalance: '1000000000000000000');

        $hash = $token->transfer(
            '0xfrom',
            'private',
            '0x1111111111111111111111111111111111111111',
            '2',
            true
        );

        $this->assertSame('0xtoken-tx', $hash);
        $this->assertStringEndsWith(
            str_pad(gmp_strval(gmp_init('1250000000000000000', 10), 16), 64, '0', STR_PAD_LEFT),
            $token->lastData
        );
    }

    #[Test]
    public function evm_coin_rejects_non_positive_amount(): void
    {
        $coin = $this->ethCoin();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid amount');

        $coin->transfer('0xfrom', 'private', '0xto', '0');
    }

    #[Test]
    public function evm_coin_rejects_insufficient_balance(): void
    {
        $coin = $this->ethCoin(balance: '1000000000000000000');

        $this->expectException(BalanceShortageException::class);

        $coin->transfer('0xfrom', 'private', '0xto', '2');
    }

    #[Test]
    public function evm_coin_transfer_parses_units_before_creating_transaction(): void
    {
        $coin = $this->ethCoin(balance: '5000000000000000000');

        $hash = $coin->transfer('0xfrom', 'private', '0xto', '1.5');

        $this->assertSame('0xcoin-tx', $hash);
        $this->assertSame('1500000000000000000', $coin->lastValue);
    }

    #[Test]
    public function tron_coin_rejects_insufficient_balance(): void
    {
        $coin = $this->tronCoin(balance: '1000000');

        $this->expectException(BalanceShortageException::class);

        $coin->transfer('TFrom', 'private', 'TTo', '2');
    }

    #[Test]
    public function tron_coin_transfer_sends_sun_amount(): void
    {
        $coin = $this->tronCoin(balance: '20000000');

        $hash = $coin->transfer('TFrom', 'private', 'TTo', '10');

        $this->assertSame('tron-txid', $hash);
        $this->assertSame('wallet/createtransaction', $coin->lastMethod);
        $this->assertSame(10_000_000, $coin->lastParams['amount']);
    }

    #[Test]
    public function tron_token_rejects_insufficient_balance(): void
    {
        $token = $this->tronToken(tokenBalance: '1');

        $this->expectException(BalanceShortageException::class);

        $token->transfer('TFrom', 'private', 'TTo', '2');
    }

    #[Test]
    public function tron_token_best_effort_stops_before_rpc_when_balance_is_used(): void
    {
        $token = $this->tronToken(tokenBalance: '3');
        $token->failOnRpc = true;

        try {
            $token->transfer('TFrom', 'private', 'TTo', '10', true);
            $this->fail('Expected rpc stub to be reached after balance adjustment.');
        } catch (\RuntimeException $e) {
            $this->assertSame('rpc-reached', $e->getMessage());
        }
    }

    private function ethToken(string $tokenBalance = '0', string $nativeBalance = '0'): EthToken
    {
        return new class(['chain' => 'eth', 'chain_id' => 1, 'contract_address' => '0x0000000000000000000000000000000000000001', 'decimals' => 18, 'rpc_url' => 'https://example.com', 'explorer_url' => 'https://etherscan.io'], $tokenBalance, $nativeBalance) extends EthToken
        {
            public string $lastData = '';

            public function __construct(array $config, public string $tokenBalance, public string $nativeBalance)
            {
                parent::__construct($config);
            }

            public function getBalance(string $address): string
            {
                return $this->tokenBalance;
            }

            public function getNativeCoin(): CoinInterface
            {
                $balance = $this->nativeBalance;

                return new class($balance) implements CoinInterface
                {
                    public function __construct(private string $balance) {}

                    public function symbol(): string
                    {
                        return 'ETH';
                    }

                    public function getDecimals(): int
                    {
                        return 18;
                    }

                    public function getBalance(string $address, bool $asUnit = false): string
                    {
                        return $this->balance;
                    }

                    public function getTransaction(string $txId): ?TransactionInfo
                    {
                        return null;
                    }

                    public function getTransactionStatus(string $txId): bool
                    {
                        return false;
                    }

                    public function transfer(
                        string $fromAddress,
                        #[SensitiveParameter] string $fromPrivateKey,
                        string $toAddress,
                        string $amount
                    ): string {
                        return '';
                    }
                };
            }

            protected function createTransaction(
                string $fromAddress,
                #[SensitiveParameter] string $fromPrivateKey,
                string $toAddress,
                string $nativeBalance,
                string $value = '',
                string $data = ''
            ): string {
                $this->lastData = $data;

                return '0xtoken-tx';
            }
        };
    }

    private function ethCoin(string $balance = '0'): EthCoin
    {
        return new class(['chain' => 'eth', 'chain_id' => 1, 'rpc_url' => 'https://example.com', 'explorer_url' => 'https://etherscan.io'], $balance) extends EthCoin
        {
            public string $lastValue = '';

            public function __construct(array $config, public string $balance)
            {
                parent::__construct($config);
            }

            public function getBalance(string $address, bool $asUnit = false): string
            {
                return $this->balance;
            }

            protected function createTransaction(
                string $fromAddress,
                #[SensitiveParameter] string $fromPrivateKey,
                string $toAddress,
                string $nativeBalance,
                string $value = '',
                string $data = ''
            ): string {
                $this->lastValue = $value;

                return '0xcoin-tx';
            }
        };
    }

    private function tronCoin(string $balance = '0'): TronCoin
    {
        return new class(['chain' => 'tron', 'chain_id' => 0, 'rpc_url' => 'https://example.com', 'explorer_url' => 'https://tronscan.org'], $balance) extends TronCoin
        {
            public string $lastMethod = '';

            public array $lastParams = [];

            public function __construct(array $config, public string $balance)
            {
                parent::__construct($config);
            }

            public function getBalance(string $address, bool $asUnit = false): string
            {
                return $this->balance;
            }

            public function rpcRequest(string $method, array $params = [], HttpMethod $httpMethod = HttpMethod::POST): BizResponseInterface
            {
                $this->lastMethod = $method;
                $this->lastParams = $params;

                return new class implements BizResponseInterface
                {
                    public function bizOk(): bool
                    {
                        return true;
                    }

                    public function getBizErrorMsg(): string
                    {
                        return '';
                    }

                    public function json($key = null, $default = null): mixed
                    {
                        return $key === null ? ['txID' => 'raw'] : $default;
                    }
                };
            }

            protected function broadcast(array $data, #[SensitiveParameter] string $privateKey): string
            {
                return 'tron-txid';
            }
        };
    }

    private function tronToken(string $tokenBalance = '0'): TronToken
    {
        return new class(['chain' => 'tron', 'chain_id' => 0, 'contract_address' => 'TContract', 'decimals' => 6, 'rpc_url' => 'https://example.com', 'explorer_url' => 'https://tronscan.org'], $tokenBalance) extends TronToken
        {
            public bool $failOnRpc = false;

            public function __construct(array $config, public string $tokenBalance)
            {
                parent::__construct($config);
            }

            public function getBalance(string $address): string
            {
                return $this->tokenBalance;
            }

            public function rpcRequest(string $method, array $params = [], HttpMethod $httpMethod = HttpMethod::POST): BizResponseInterface
            {
                if ($this->failOnRpc) {
                    throw new \RuntimeException('rpc-reached');
                }

                return parent::rpcRequest($method, $params, $httpMethod);
            }
        };
    }
}
