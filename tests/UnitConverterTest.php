<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests;

use Mitoop\Web3\Support\Traits\UnitConverterTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UnitConverterTest extends TestCase
{
    private object $converter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->converter = new class
        {
            use UnitConverterTrait {
                formatUnits as public;
                parseUnits as public;
            }
        };
    }

    #[Test]
    public function parse_units_scales_by_decimals(): void
    {
        $this->assertSame('10000000', $this->converter->parseUnits('10', '6'));
        $this->assertSame('1000000000000000000', $this->converter->parseUnits('1', '18'));
    }

    #[Test]
    public function format_units_converts_hex_and_decimal(): void
    {
        $this->assertSame('10', $this->converter->formatUnits('10000000', 6));
        $this->assertSame('1', $this->converter->formatUnits('0xde0b6b3a7640000', 18));
    }

    #[Test]
    public function format_units_can_keep_trailing_zeros(): void
    {
        $this->assertSame('10.000000', $this->converter->formatUnits('10000000', 6, false));
    }
}
