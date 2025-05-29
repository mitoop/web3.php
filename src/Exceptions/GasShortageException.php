<?php

namespace Mitoop\Crypto\Exceptions;

class GasShortageException extends CryptoException
{
    protected string $balance;

    protected string $fee;

    public function __construct(string $balance, string $fee)
    {
        parent::__construct(sprintf('balance: %s, fee: %s', $balance, $fee));

        $this->balance = $balance;
        $this->fee = $fee;
    }

    public function getBalance(): string
    {
        return $this->balance;
    }

    public function getFee(): string
    {
        return $this->fee;
    }
}
