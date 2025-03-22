<?php

namespace Mitoop\Crypto\Concerns\Evm\Transactions;

use kornrunner\Ethereum\EIP1559Transaction as Transaction;

class EIP1559Transaction extends Transaction
{
    use Buildable;
}
