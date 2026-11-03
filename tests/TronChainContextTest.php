<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests;

use Mitoop\Web3\Tokens\Tron\ChainContext;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TronChainContextTest extends TestCase
{
    #[Test]
    public function to_sun_uses_ten_to_the_power_of_decimals(): void
    {
        $context = new class([]) extends ChainContext {};

        $method = new ReflectionMethod(ChainContext::class, 'toSun');

        $this->assertSame(10_000_000, $method->invoke($context, '10'));
        $this->assertSame(1_000_000, $method->invoke($context, 1));
    }

    #[Test]
    public function fee_limit_defaults_to_thirty_trx_and_can_be_overridden(): void
    {
        $context = new class([]) extends ChainContext {};

        $this->assertSame(30_000_000, $context->getFeeLimit());

        $context->setFeeLimit(50_000_000);

        $this->assertSame(50_000_000, $context->getFeeLimit());
    }
}
