<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests;

use Mitoop\Web3\Concerns\Evm\Transactions\TransactionBuilder as EvmTransactionBuilder;
use Mitoop\Web3\Concerns\Tron\TransactionBuilder as TronTransactionBuilder;
use Mitoop\Web3\Enums\MethodSelector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TransactionBuilderTest extends TestCase
{
    #[Test]
    public function evm_encode_builds_transfer_data(): void
    {
        $data = (new EvmTransactionBuilder)->encode(
            '0x1111111111111111111111111111111111111111',
            '1.5',
            18
        );

        $this->assertStringStartsWith(MethodSelector::EvmTransfer->value, $data);
        $this->assertSame(138, strlen($data));
        $this->assertStringContainsString(
            '0000000000000000000000001111111111111111111111111111111111111111',
            $data
        );
        $this->assertStringEndsWith(
            str_pad(gmp_strval(gmp_init('1500000000000000000', 10), 16), 64, '0', STR_PAD_LEFT),
            $data
        );
    }

    #[Test]
    public function tron_encode_builds_transfer_parameter(): void
    {
        $data = (new TronTransactionBuilder)->encode(
            '0x2222222222222222222222222222222222222222',
            '10',
            6
        );

        $this->assertSame(128, strlen($data));
        $this->assertStringStartsWith(
            '0000000000000000000000002222222222222222222222222222222222222222',
            $data
        );
        $this->assertStringEndsWith(
            str_pad(gmp_strval(gmp_init('10000000', 10), 16), 64, '0', STR_PAD_LEFT),
            $data
        );
    }
}
