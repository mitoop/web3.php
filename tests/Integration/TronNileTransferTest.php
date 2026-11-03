<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests\Integration;

use Mitoop\Web3\CoinBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration')]
class TronNileTransferTest extends IntegrationTestCase
{
    #[Test]
    public function transfers_native_trx_on_nile(): void
    {
        $address = $this->env('WEB3_TRON_ADDRESS');
        $privateKey = $this->env('WEB3_TRON_PRIVATE_KEY');

        $coin = CoinBuilder::fromArray([
            'chain' => 'tron',
            'chain_id' => 0,
            'rpc_url' => $this->env('WEB3_TRON_NILE_RPC_URL'),
            'rpc_api_key' => getenv('WEB3_TRON_NILE_API_KEY') ?: '',
            'explorer_url' => 'https://nile.tronscan.org',
        ])->build();

        $balance = $this->rpc(fn () => $coin->getBalance($address, true));

        if (bccomp($balance, '2', 6) < 0) {
            $this->markTestSkipped("Fund Nile wallet {$address} with at least 2 TRX. Current balance: {$balance}");
        }

        $hash = $this->rpc(fn () => $coin->transfer($address, $privateKey, $address, '1'));

        $this->assertTxHash($hash, false);
    }

    #[Test]
    public function transfers_usdt_on_nile(): void
    {
        $address = $this->env('WEB3_TRON_ADDRESS');
        $privateKey = $this->env('WEB3_TRON_PRIVATE_KEY');
        $decimals = $this->envInt('WEB3_TRON_NILE_USDT_DECIMALS');
        $amount = '0.01';

        $token = $this->token([
            'chain' => 'tron',
            'chain_id' => 0,
            'contract_address' => $this->env('WEB3_TRON_NILE_USDT_CONTRACT'),
            'decimals' => $decimals,
            'rpc_url' => $this->env('WEB3_TRON_NILE_RPC_URL'),
            'rpc_api_key' => getenv('WEB3_TRON_NILE_API_KEY') ?: '',
            'explorer_url' => 'https://nile.tronscan.org',
        ]);

        $native = $this->rpc(fn () => $token->getNativeCoin()->getBalance($address, true));
        if (bccomp($native, '2', 6) < 0) {
            $this->markTestSkipped("Fund Nile wallet {$address} with TRX for energy/bandwidth. Current: {$native}");
        }

        $balance = $this->rpc(fn () => $token->getBalance($address));
        if (bccomp($balance, $amount, $decimals) < 0) {
            $this->markTestSkipped("Fund Nile wallet {$address} with USDT ({$this->env('WEB3_TRON_NILE_USDT_CONTRACT')}). Current balance: {$balance}");
        }

        $hash = $this->rpc(fn () => $token->transfer($address, $privateKey, $address, $amount));

        $this->assertTxHash($hash, false);
    }
}
