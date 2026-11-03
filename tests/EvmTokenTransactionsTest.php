<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests;

use Mitoop\Web3\Exceptions\InvalidArgumentException;
use Mitoop\Web3\Tokens\Eth\Token;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EvmTokenTransactionsTest extends TestCase
{
    #[Test]
    public function get_transactions_requires_from_block(): void
    {
        $token = new Token([
            'chain' => 'eth',
            'chain_id' => 1,
            'contract_address' => '0x0000000000000000000000000000000000000001',
            'decimals' => 18,
            'rpc_url' => 'https://example.com',
            'explorer_url' => 'https://etherscan.io',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('from_block is required');

        $token->getTransactions('0x0000000000000000000000000000000000000002', []);
    }

    #[Test]
    public function get_transactions_rejects_empty_from_block(): void
    {
        $token = new Token([
            'chain' => 'eth',
            'chain_id' => 1,
            'contract_address' => '0x0000000000000000000000000000000000000001',
            'decimals' => 18,
            'rpc_url' => 'https://example.com',
            'explorer_url' => 'https://etherscan.io',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('from_block is required');

        $token->getTransactions('0x0000000000000000000000000000000000000002', [
            'from_block' => '',
        ]);
    }
}
