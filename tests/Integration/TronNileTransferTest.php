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
        $rpcUrl = $this->env('WEB3_TRON_NILE_RPC_URL');

        $coin = CoinBuilder::fromArray([
            'chain' => 'tron',
            'chain_id' => 0,
            'rpc_url' => $rpcUrl,
            'rpc_api_key' => getenv('WEB3_TRON_NILE_API_KEY') ?: '',
            'explorer_url' => 'https://nile.tronscan.org',
        ])->build();

        $balance = $this->rpc(fn () => $coin->getBalance($address, true));

        if (bccomp($balance, '2', 6) < 0) {
            $this->markTestSkipped("Fund Nile wallet {$address} with at least 2 TRX. Current balance: {$balance}");
        }

        $hash = $this->rpc(fn () => $coin->transfer($address, $privateKey, $address, '1'));

        $this->assertNotSame('', $hash);
        $this->assertMatchesRegularExpression('/^[a-fA-F0-9]{64}$/', $hash);
    }
}
