<?php

namespace Mitoop\Crypto\Concerns\Evm\Token;

use Mitoop\Crypto\Exceptions\BalanceShortageException;
use Mitoop\Crypto\Exceptions\GasShortageException;
use Mitoop\Crypto\Exceptions\InvalidArgumentException;
use Mitoop\Crypto\Exceptions\RpcException;
use Mitoop\Crypto\Support\NumberFormatter;
use Mitoop\Crypto\Transactions\Coin\TransactionInfo;

trait CoinTrait
{
    use EvmLike;

    public function getDecimals(): int
    {
        return $this->getNativeCoinDecimals();
    }

    /**
     * @throws RpcException
     */
    public function getBalance(string $address, bool $asUiAmount = false): string
    {
        $response = $this->rpcRequest('eth_getBalance', [
            $address,
            'latest',
        ]);

        // 🌰 "0x853a0d2313c0000" => "600000000000000000" wei
        $balance = gmp_strval(gmp_init($response->json('result'), 16));

        if ($asUiAmount) {
            return NumberFormatter::removeTrailingZeros(bcdiv($balance, bcpow(10, $this->getDecimals(), 0), $this->getDecimals()));
        }

        return $balance;
    }

    /**
     * @throws RpcException
     */
    public function getTransaction(string $txId): ?TransactionInfo
    {
        $response = $this->rpcRequest('eth_getTransactionByHash', [
            $txId,
        ]);

        if ($response->json('result') === null) {
            return null;
        }

        return new TransactionInfo(
            $response->json('result.hash'),
            $response->json('result.from'),
            $response->json('result.to'),
            NumberFormatter::toDecimalAmount($response->json('result.value'), $this->getDecimals()),
        );
    }

    /**
     * @throws BalanceShortageException
     * @throws RpcException
     * @throws GasShortageException
     * @throws InvalidArgumentException
     */
    public function transfer(string $fromAddress, string $fromPrivateKey, string $toAddress, string $amount): string
    {
        if (bccomp($amount, 0, 0) <= 0) {
            throw new InvalidArgumentException('Invalid amount');
        }

        $balance = $this->getBalance($fromAddress);
        $amount = bcmul($amount, bcpow(10, $this->getDecimals(), 0), 0);

        if (bccomp($balance, $amount, $this->getDecimals()) <= 0) {
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
