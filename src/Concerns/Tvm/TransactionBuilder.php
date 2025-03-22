<?php

namespace Mitoop\Crypto\Concerns\Tvm;

use kornrunner\Secp256k1;
use kornrunner\Signature\Signature;

class TransactionBuilder
{
    public function encode(string $toAddress, $amount, $decimals): string
    {
        $paddedAddress = str_pad(substr($toAddress, 2), 64, '0', STR_PAD_LEFT);
        $scaledAmount = bcmul((string) $amount, bcpow('10', (string) $decimals, 0), 0);
        $amountHex = str_pad(gmp_strval(gmp_init($scaledAmount, 10), 16), 64, '0', STR_PAD_LEFT);

        return $paddedAddress.$amountHex;
    }

    public function sign($txId, $privateKey): array
    {
        /** @var Signature $sign */
        $sign = (new Secp256k1)->sign($txId, $privateKey, ['canonical' => false]);

        $signatureHex = $sign->toHex();

        $recoveryParamHex = str_pad(dechex($sign->getRecoveryParam()), 2, '0', STR_PAD_LEFT);

        return [$signatureHex.$recoveryParamHex];
    }
}
