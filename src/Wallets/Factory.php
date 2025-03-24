<?php

namespace Mitoop\Crypto\Wallets;

use Mitoop\Crypto\Exceptions\InvalidArgumentException;

class Factory
{
    /**
     * @throws InvalidArgumentException
     */
    public static function create(string $chain): WalletInterface
    {
        return match (strtolower($chain)) {
            'eth', 'bsc', 'polygon' => new EvmWallet,
            'sol' => new SolWallet,
            'tron' => new TronWallet,
            default => throw new InvalidArgumentException(
                sprintf('Unsupported chain "%s". Supported chains are: eth, bsc, polygon, tron', $chain)
            ),
        };
    }
}
