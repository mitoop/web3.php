<?php

namespace Mitoop\Web3\Concerns\Evm\Traits;

use Mitoop\Web3\Exceptions\BalanceShortageException;
use Mitoop\Web3\Exceptions\GasShortageException;
use Mitoop\Web3\Exceptions\InvalidArgumentException;
use Mitoop\Web3\Exceptions\RpcException;
use Mitoop\Web3\Support\UnitFormatter;
use Mitoop\Web3\Transactions\TransactionInfo;
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
        $balance = $this->hexToDecimal($response->json('result'));

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
            $this->hexToDecimal($response->json('result.effectiveGasPrice')),
            $this->hexToDecimal($response->json('result.gasUsed')),
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
        if (bccomp($amount, 0, $this->getDecimals()) <= 0) {
            throw new InvalidArgumentException('Invalid amount');
        }

        $balance = $this->getBalance($fromAddress);
        $amount = $this->parseUnits($amount, $this->getDecimals());

        if (bccomp($balance, $amount, $this->getDecimals()) <= 0) {
            throw new BalanceShortageException(sprintf('balance: %s, amount: %s', $balance, $amount));
        }

        return $this->createTransaction($fromAddress, $fromPrivateKey, $toAddress, $balance, $amount);
    }
}
