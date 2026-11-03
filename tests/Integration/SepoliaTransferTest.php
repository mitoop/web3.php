<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests\Integration;

use Mitoop\Web3\CoinBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('integration')]
class SepoliaTransferTest extends IntegrationTestCase
{
    #[Test]
    public function transfers_native_eth_on_sepolia(): void
    {
        $address = $this->env('WEB3_EVM_ADDRESS');
        $privateKey = $this->env('WEB3_EVM_PRIVATE_KEY');
        $rpcUrl = $this->env('WEB3_SEPOLIA_RPC_URL');

        $coin = CoinBuilder::fromArray([
            'chain' => 'eth',
            'chain_id' => 11155111,
            'rpc_url' => $rpcUrl,
            'rpc_api_key' => '',
            'explorer_url' => 'https://sepolia.etherscan.io',
        ])->build();

        $balance = $this->rpc(fn () => $coin->getBalance($address, true));

        if (bccomp($balance, '0.001', 18) < 0) {
            $this->markTestSkipped("Fund Sepolia wallet {$address} with at least 0.001 ETH. Current balance: {$balance}");
        }

        $hash = $this->rpc(fn () => $coin->transfer($address, $privateKey, $address, '0.000001'));

        $this->assertMatchesRegularExpression('/^0x[a-fA-F0-9]{64}$/', $hash);
    }
}
