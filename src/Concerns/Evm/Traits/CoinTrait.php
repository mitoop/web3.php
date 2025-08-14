<?php

namespace Mitoop\Crypto\Concerns\Evm\Traits;

use Mitoop\Crypto\Exceptions\BalanceShortageException;
use Mitoop\Crypto\Exceptions\GasShortageException;
use Mitoop\Crypto\Exceptions\InvalidArgumentException;
use Mitoop\Crypto\Exceptions\RpcException;
use Mitoop\Crypto\Support\UnitFormatter;
use Mitoop\Crypto\Transactions\TransactionInfo;
use SensitiveParameter;

trait CoinTrait
{
    use EvmLikeToken;

    public function getDecimals(): int
    {
        return $this->getNativeCoinDecimals();
    }

    /**
     * @throws RpcException
     */
    public function getBalance(string $address, bool $asUnit = false): string
    {
        $response = $this->rpcRequest('eth_getBalance', [
            $address,
            'latest',
        ]);

        // 🌰 "0x853a0d2313c0000" => "600000000000000000" wei
        $balance = gmp_strval(gmp_init($response->json('result'), 16));

        if ($asUnit) {
            return UnitFormatter::formatUnits($balance, $this->getDecimals());
        }

        return $balance;
    }

    /**
     * @throws RpcException
     */
    public function getTransaction(string $txId): ?TransactionInfo
    {
        $response = $this->rpcRequest('eth_getTransactionReceipt', [
            $txId,
        ]);

        if (hexdec($response->json('result.status', 0)) !== 1) {
            return null;
        }

        $hash = $response->json('result.transactionHash');
        $from = $response->json('result.from');
        $to = $response->json('result.to');

        $fee = bcmul(
            gmp_strval(gmp_init($response->json('result.effectiveGasPrice'), 16)),
            gmp_strval(gmp_init($response->json('result.gasUsed'), 16)),
            0
        );

        $fee = UnitFormatter::formatUnits($fee, $this->getNativeCoinDecimals());

        $response = $this->rpcRequest('eth_getTransactionByHash', [
            $txId,
        ]);

        $value = (string) $response->json('result.value');
        $amount = UnitFormatter::formatUnits($value, $this->getDecimals());

        return new TransactionInfo(
            true,
            (string) $hash,
            (string) $from,
            (string) $to,
            $value,
            $amount,
            $fee,
        );
    }

    /**
     * @throws BalanceShortageException
     * @throws RpcException
     * @throws GasShortageException
     * @throws InvalidArgumentException
     */
    public function transfer(
        string $fromAddress,
        #[SensitiveParameter] string $fromPrivateKey,
        string $toAddress,
        string $amount
    ): string {
        if (bccomp($amount, 0, 0) <= 0) {
            throw new InvalidArgumentException('Invalid amount');
        }

        $balance = $this->getBalance($fromAddress);
        $amount = bcmul($amount, bcpow(10, $this->getDecimals(), 0), 0);

        if (bccomp($balance, $amount, 0) <= 0) {
            throw new BalanceShortageException(sprintf('balance: %s, amount: %s', $balance, $amount));
        }

        [$gasPrice, $gasLimit] = $this->computeGas($this->estimateGas($fromAddress, $toAddress), $balance, $amount);

        $nonce = gmp_strval(gmp_init($this->getTransactionCount($fromAddress), 10), 16);
        $amount = gmp_strval(gmp_init($amount, 10), 16);

        if (! $this->supportsEIP1559Transaction()) {
            return $this->createLegacyTransaction($fromPrivateKey, $nonce, $gasPrice, $gasLimit, $toAddress, $amount);
        }

        return $this->createEIP1559Transaction($fromPrivateKey, $nonce, $gasLimit, $toAddress, $amount);
    }
}
