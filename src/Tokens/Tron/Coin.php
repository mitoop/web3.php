<?php

namespace Mitoop\Crypto\Tokens\Tron;

use Mitoop\Crypto\Contracts\CoinInterface;
use Mitoop\Crypto\Exceptions\BalanceShortageException;
use Mitoop\Crypto\Exceptions\BroadcastException;
use Mitoop\Crypto\Exceptions\RpcException;
use Mitoop\Crypto\Exceptions\TransactionExecutionFailedException;
use Mitoop\Crypto\Support\UnitFormatter;
use Mitoop\Crypto\Transactions\TransactionInfo;
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
            return UnitFormatter::formatUnits($balance, $this->getDecimals());
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
            throw new TransactionExecutionFailedException(hex2bin($response->json('resMessage')));
        }

        if (is_null($response->json('blockNumber'))) {
            return null;
        }

        $fee = UnitFormatter::formatUnits($response->json('fee'), $this->getDecimals());

        $response = $this->rpcRequest('walletsolidity/gettransactionbyid', [
            'value' => $txId,
            'visible' => true,
        ]);

        if (empty($response->json())) {
            return null;
        }

        return new TransactionInfo(
            true,
            (string) $response->json('txID'),
            (string) $response->json('raw_data.contract.0.parameter.value.owner_address'),
            (string) $response->json('raw_data.contract.0.parameter.value.to_address'),
            UnitFormatter::formatUnits((string) $response->json('raw_data.contract.0.parameter.value.amount'), $this->getDecimals()),
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
        $amount = bcmul($amount, bcpow(10, $this->getDecimals(), 0), 0);

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
