<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests;

use Mitoop\Web3\Support\Traits\NumberConverterTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NumberConverterTest extends TestCase
{
    private object $converter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->converter = new class
        {
            use NumberConverterTrait {
                decimalToHex as public;
                hexToDecimal as public;
            }
        };
    }

    #[Test]
    public function decimal_to_hex_adds_prefix_by_default(): void
    {
        $this->assertSame('0x64', $this->converter->decimalToHex('100'));
        $this->assertSame('64', $this->converter->decimalToHex('100', false));
    }

    #[Test]
    public function hex_to_decimal_accepts_prefixed_values(): void
    {
        $this->assertSame('100', $this->converter->hexToDecimal('0x64'));
        $this->assertSame('100', $this->converter->hexToDecimal('64'));
    }
}
