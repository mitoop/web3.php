<?php

namespace Mitoop\Web3\Concerns\Evm\Transactions;

use Mitoop\Web3\Enums\MethodSelector;

class TransactionBuilder
{
    public function encode(string $toAddress, $amount, $decimals): string
    {
        $abiMethodId = MethodSelector::EvmTransfer->value;
        $abiAddress = str_pad(substr($toAddress, 2), 64, '0', STR_PAD_LEFT);

        $scaledAmount = bcmul((string) $amount, bcpow('10', (string) $decimals, 0), 0);
        $abiAmount = str_pad(gmp_strval(gmp_init($scaledAmount, 10), 16), 64, '0', STR_PAD_LEFT);

        return $abiMethodId.$abiAddress.$abiAmount;
    }
}
