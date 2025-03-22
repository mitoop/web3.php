<?php

namespace Mitoop\Crypto\Concerns\Evm\Transactions;

use kornrunner\Ethereum\Transaction;

class LegacyTransaction extends Transaction
{
    use Buildable;
}
