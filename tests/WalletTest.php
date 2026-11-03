<?php

declare(strict_types=1);

namespace Mitoop\Web3\Tests;

use Mitoop\Web3\Exceptions\InvalidArgumentException;
use Mitoop\Web3\Wallets\EvmWallet;
use Mitoop\Web3\Wallets\Factory;
use Mitoop\Web3\Wallets\SolWallet;
use Mitoop\Web3\Wallets\TronWallet;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WalletTest extends TestCase
{
    #[Test]
    public function factory_creates_supported_wallets(): void
    {
        $this->assertInstanceOf(EvmWallet::class, Factory::create('eth'));
        $this->assertInstanceOf(EvmWallet::class, Factory::create('bsc'));
        $this->assertInstanceOf(EvmWallet::class, Factory::create('polygon'));
        $this->assertInstanceOf(TronWallet::class, Factory::create('tron'));
        $this->assertInstanceOf(SolWallet::class, Factory::create('sol'));
    }

    #[Test]
    public function factory_rejects_unknown_chains(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Factory::create('bitcoin');
    }

    #[Test]
    public function evm_wallet_generates_and_validates_address(): void
    {
        $wallet = (new EvmWallet)->generate();

        $this->assertTrue((new EvmWallet)->validate($wallet->address));
        $this->assertFalse((new EvmWallet)->validate('0x1234'));
    }

    #[Test]
    public function tron_wallet_generates_and_validates_address(): void
    {
        $wallet = (new TronWallet)->generate();

        $this->assertTrue((new TronWallet)->validate($wallet->address));
        $this->assertFalse((new TronWallet)->validate('T123'));
    }

    #[Test]
    public function sol_wallet_generates_and_validates_address(): void
    {
        $wallet = (new SolWallet)->generate();

        $this->assertTrue((new SolWallet)->validate($wallet->address));
        $this->assertFalse((new SolWallet)->validate('short'));
    }
}
