<?php

namespace Mitoop\Crypto\Tokens\Tron;

use Mitoop\Crypto\Concerns\HasTokenProperties;
use Mitoop\Crypto\Concerns\Tron\TransactionBuilder;
use Mitoop\Crypto\Contracts\TokenInterface;
use Mitoop\Crypto\Exceptions\BalanceShortageException;
use Mitoop\Crypto\Exceptions\RpcException;
use Mitoop\Crypto\Support\Http\HttpMethod;
use Mitoop\Crypto\Support\NumberFormatter;
use Mitoop\Crypto\Transactions\Token\Transaction;
use Mitoop\Crypto\Transactions\Token\TransactionInfo;
use SensitiveParameter;

class Token extends Chain implements TokenInterface
{
    use HasTokenProperties;

    /**
     * @throws RpcException
     */
    public function getBalance(string $address): string
    {
        $response = $this->rpcRequest('/wallet/triggersmartcontract', [
            'contract_address' => $this->getContractAddress(),
            'function_selector' => 'balanceOf(address)',
            'parameter' => $this->toPaddedAddress($address),
            'owner_address' => $address,
            'visible' => true,
        ]);

        return NumberFormatter::toDisplayAmount('0x'.$response->json('constant_result.0'), $this->getDecimals());
    }

    /**
     * @throws RpcException
     */
    public function getTransactions($address, array $params = []): array
    {
        $params = array_merge([
            'limit' => 50,
            'min_timestamp' => 0,
        ], $params);

        $response = $this->rpcRequest("v1/accounts/{$address}/transactions/trc20", [
            'only_confirmed' => true,
            'only_to' => true,
            'limit' => $params['limit'],
            'min_timestamp' => $params['min_timestamp'],
            'contract_address' => $this->getContractAddress(),
        ], HttpMethod::GET);

        $transactions = [];
        foreach ($response->json('data') as $item) {
            $transactions[] = new Transaction(
                $item['transaction_id'],
                $item['token_info']['address'],
                $item['from'],
                $item['to'],
                $item['value'],
                NumberFormatter::toDisplayAmount($item['value'], $decimals = (int) $item['token_info']['decimals']),
                $decimals,
            );
        }

        return $transactions;
    }

    /**
     * @throws RpcException
     */
    public function getTransaction(string $txId): ?TransactionInfo
    {
        $response = $this->rpcRequest('walletsolidity/gettransactioninfobyid', [
            'value' => $txId,
        ]);

        if (empty($response->json())) {
            return null;
        }

        if ($response->json('receipt.result') !== 'SUCCESS') {
            return null;
        }

        $logs = $response->json('log', []);

        $from = '';
        $to = '';
        $value = 0;
        foreach ($logs as $log) {
            if (! empty($log['topics'][0])
                &&
                $log['topics'][0] === 'ddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef'
                &&
                strtolower($log['address']) === strtolower($this->toHexAddress($this->getContractAddress(), true))
            ) {
                $value = NumberFormatter::toDisplayAmount('0x'.$log['data'], $this->getDecimals());
                $from = $this->toAddressFormat($log['topics'][1]);
                $to = $this->toAddressFormat($log['topics'][2]);
                break;
            }
        }

        return new TransactionInfo(
            true,
            $response->json('id'),
            $from,
            $to,
            $value,
            json_encode(['net' => $response->json('receipt.net_usage'), 'energy' => $response->json('receipt.energy_usage_total')]),
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

        if ($response->json('receipt.result') === 'SUCCESS') {
            return true;
        }

        return false;
    }

    /**
     * @throws BalanceShortageException
     * @throws RpcException
     */
    public function transfer(
        string $fromAddress,
        #[SensitiveParameter] string $fromPrivateKey,
        string $toAddress,
        string $amount,
        bool $bestEffort = false
    ): string {
        $balance = $this->getBalance($fromAddress);

        if (bccomp($balance, $amount, $this->getDecimals()) <= 0) {
            throw new BalanceShortageException(sprintf('balance: %s, amount: %s', $balance, $amount));
        }

        $response = $this->rpcRequest('wallet/triggersmartcontract', [
            'owner_address' => $fromAddress,
            'contract_address' => $this->getContractAddress(),
            'function_selector' => 'transfer(address,uint256)',
            'parameter' => (new TransactionBuilder)->encode($this->toHexAddress($toAddress), $amount, $this->getDecimals()),
            'fee_limit' => 30_000_000,
            'call_value' => 0,
            'visible' => true,
        ]);

        $data = $response->json('transaction');

        return $this->broadcast($data, $fromPrivateKey);
    }
}
