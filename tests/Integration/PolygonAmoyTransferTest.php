<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests\Integration;

use Mitoop\Web3\CoinBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration')]
class PolygonAmoyTransferTest extends IntegrationTestCase
{
    #[Test]
    public function transfers_native_pol_on_amoy(): void
    {
        $address = $this->env('WEB3_EVM_ADDRESS');
        $privateKey = $this->env('WEB3_EVM_PRIVATE_KEY');

        $coin = CoinBuilder::fromArray([
            'chain' => 'polygon',
            'chain_id' => 80002,
            'rpc_url' => $this->env('WEB3_POLYGON_AMOY_RPC_URL'),
            'rpc_api_key' => '',
            'explorer_url' => 'https://amoy.polygonscan.com',
        ])->build();

        $balance = $this->rpc(fn () => $coin->getBalance($address, true));

        if (bccomp($balance, '0.01', 18) < 0) {
            $this->markTestSkipped("Fund Amoy wallet {$address} with at least 0.01 POL. Current balance: {$balance}");
        }

        $hash = $this->rpc(fn () => $coin->transfer($address, $privateKey, $address, '0.000001'));

        $this->assertTxHash($hash);
    }

    #[Test]
    public function transfers_link_on_amoy(): void
    {
        $address = $this->env('WEB3_EVM_ADDRESS');
        $privateKey = $this->env('WEB3_EVM_PRIVATE_KEY');
        $decimals = $this->envInt('WEB3_AMOY_LINK_DECIMALS');
        $amount = '0.000001';

        $token = $this->token([
            'chain' => 'polygon',
            'chain_id' => 80002,
            'contract_address' => $this->env('WEB3_AMOY_LINK_CONTRACT'),
            'decimals' => $decimals,
            'rpc_url' => $this->env('WEB3_POLYGON_AMOY_RPC_URL'),
            'explorer_url' => 'https://amoy.polygonscan.com',
        ]);

        $native = $this->rpc(fn () => $token->getNativeCoin()->getBalance($address, true));
        if (bccomp($native, '0.01', 18) < 0) {
            $this->markTestSkipped("Fund Amoy wallet {$address} with POL for gas. Current: {$native}");
        }

        $balance = $this->rpc(fn () => $token->getBalance($address));
        if (bccomp($balance, $amount, $decimals) < 0) {
            $this->markTestSkipped("Fund Amoy wallet {$address} with LINK. Current: {$balance}");
        }

        $hash = $this->rpc(fn () => $token->transfer($address, $privateKey, $address, $amount));

        $this->assertTxHash($hash);
    }
}
