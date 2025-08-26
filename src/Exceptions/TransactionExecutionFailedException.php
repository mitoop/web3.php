<?php

namespace Mitoop\Web3\Exceptions;

class TransactionExecutionFailedException extends CryptoException
{
    public static function fromResMessage(?string $resMessage): self
    {
        return new self($resMessage ? hex2bin($resMessage) : 'Transaction execution failed');
    }
}
