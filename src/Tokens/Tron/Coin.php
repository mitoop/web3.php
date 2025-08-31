<?php

namespace Mitoop\Web3\Tokens\Tron;

use Mitoop\Web3\Contracts\CoinInterface;
use Mitoop\Web3\Exceptions\BalanceShortageException;
use Mitoop\Web3\Exceptions\BroadcastException;
use Mitoop\Web3\Exceptions\RpcException;
use Mitoop\Web3\Exceptions\TransactionExecutionFailedException;
use Mitoop\Web3\Transactions\TransactionInfo;
use SensitiveParameter;

class Coin extends ChainContext implements CoinInterface
{
    public function symbol(): string
    {
        return 'TRX';
    }

    public function getDecimals(): int
    {
        return $this->getNativeCoinDecimals();
    }

    /**
     * @throws RpcException
     */
    public function getBalance(string $address, bool $asUnit = false): string
    {
        $response = $this->rpcRequest('walletsolidity/getaccount', [
            'address' => $address,
            'visible' => true,
        ]);

        // 🌰 6000000000 sun
        $balance = gmp_strval($response->json('balance'));

        if ($asUnit) {
            return $this->formatUnits($balance, $this->getDecimals());
        }

        return $balance;
    }

    /**
     * @throws RpcException
     * @throws TransactionExecutionFailedException
     */
    public function getTransaction(string $txId): ?TransactionInfo
    {
        $response = $this->rpcRequest('walletsolidity/gettransactioninfobyid', [
            'value' => $txId,
        ]);

        if (empty($response->json())) {
            return null;
        }

        if ($response->json('result') === 'FAILED') {
            throw TransactionExecutionFailedException::fromResMessage($response->json('resMessage'));
        }

        $fee = $this->formatUnits($response->json('fee'), $this->getDecimals());

        $response = $this->rpcRequest('walletsolidity/gettransactionbyid', [
            'value' => $txId,
            'visible' => true,
        ]);

        if (empty($response->json())) {
            return null;
        }

        if ($response->json('ret.0.contractRet') !== 'SUCCESS') {
            return null;
        }

        $value = (string) $response->json('raw_data.contract.0.parameter.value.amount');

        return new TransactionInfo(
            true,
            (string) $response->json('txID'),
            (string) $response->json('raw_data.contract.0.parameter.value.owner_address'),
            (string) $response->json('raw_data.contract.0.parameter.value.to_address'),
            $value,
            $this->formatUnits($value, $this->getDecimals()),
            $fee,
        );
    }

    /**
     * @throws RpcException
     */
    public function getTransactionStatus(string $txId): bool
    {
        $response = $this->rpcRequest('walletsolidity/gettransactioninfobyid', [
            'value' => $txId,
        ]);

        if (empty($response->json())) {
            return false;
        }

        if (is_null($response->json('blockNumber'))) {
            return false;
        }

        return true;
    }

    /**
     * @throws RpcException
     * @throws BalanceShortageException
     * @throws BroadcastException
     */
    public function transfer(
        string $fromAddress,
        #[SensitiveParameter] string $fromPrivateKey,
        string $toAddress,
        string $amount
    ): string {
        $balance = $this->getBalance($fromAddress);
        $amount = $this->parseUnits($amount, $this->getDecimals());

        if (bccomp($balance, $amount, $this->getDecimals()) <= 0) {
            throw new BalanceShortageException(sprintf('balance: %s, amount: %s', $balance, $amount));
        }

        $response = $this->rpcRequest('wallet/createtransaction', [
            'owner_address' => $fromAddress,
            'to_address' => $toAddress,
            'amount' => (int) $amount,
            'visible' => true,
        ]);

        $data = $response->json();

        return $this->broadcast($data, $fromPrivateKey);
    }
}
