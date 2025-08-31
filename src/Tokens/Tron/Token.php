<?php

namespace Mitoop\Web3\Tokens\Tron;

use Mitoop\Web3\Concerns\HasTokenProperties;
use Mitoop\Web3\Concerns\Tron\TransactionBuilder;
use Mitoop\Web3\Contracts\TokenInterface;
use Mitoop\Web3\Exceptions\BalanceShortageException;
use Mitoop\Web3\Exceptions\BroadcastException;
use Mitoop\Web3\Exceptions\GasShortageException;
use Mitoop\Web3\Exceptions\RpcException;
use Mitoop\Web3\Exceptions\TransactionExecutionFailedException;
use Mitoop\Web3\Support\Http\HttpMethod;
use Mitoop\Web3\Transactions\Transaction;
use Mitoop\Web3\Transactions\TransactionInfo;
use SensitiveParameter;

class Token extends ChainContext implements TokenInterface
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
            'parameter' => $this->toAbiPaddedAddress($address),
            'owner_address' => $address,
            'visible' => true,
        ]);

        return $this->formatUnits('0x'.$response->json('constant_result.0'), $this->getDecimals());
    }

    /**
     * @param  array{
     *      limit?: int,  // 默认 20 最大 200
     *      min_timestamp?: int|string // 毫秒级，默认0
     *  }  $params  查询参数
     *
     * @throws RpcException
     */
    public function getTransactions(string $address, array $params = []): array
    {
        $params = array_merge([
            'limit' => 20,
            'min_timestamp' => 0,
        ], $params);

        $response = $this->rpcRequest("v1/accounts/{$address}/transactions/trc20", [
            'only_confirmed' => true,
            'only_to' => true,
            'limit' => $params['limit'],
            'min_timestamp' => (string) $params['min_timestamp'],
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
                $this->formatUnits($item['value'], $decimals = (int) $item['token_info']['decimals']),
                $decimals,
            );
        }

        return $transactions;
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

        if ($response->json('receipt.result') !== 'SUCCESS') {
            return null;
        }

        $logs = $response->json('log', []);

        $from = '';
        $to = '';
        $value = '';
        $amount = '';
        foreach ($logs as $log) {
            if (! empty($log['topics'][0])
                &&
                $log['topics'][0] === 'ddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef'
                &&
                strtolower($log['address']) === strtolower($this->toHexAddress($this->getContractAddress(), true))
            ) {
                $value = '0x'.$log['data'];
                $amount = $this->formatUnits($value, $this->getDecimals());
                $from = $this->normalizeAddress($log['topics'][1]);
                $to = $this->normalizeAddress($log['topics'][2]);
                break;
            }
        }

        return new TransactionInfo(
            true,
            $response->json('id'),
            $from,
            $to,
            $value,
            $amount,
            $this->formatUnits($response->json('fee'), $this->getNativeCoinDecimals()),
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
     * @throws GasShortageException
     * @throws BroadcastException
     */
    public function transfer(
        string $fromAddress,
        #[SensitiveParameter] string $fromPrivateKey,
        string $toAddress,
        string $amount,
        bool $bestEffort = false
    ): string {
        $balance = $this->getBalance($fromAddress);

        if (bccomp($balance, $amount, $this->getDecimals()) === -1) {
            if (! $bestEffort) {
                throw new BalanceShortageException(sprintf('balance: %s, amount: %s', $balance, $amount));
            }

            if (bccomp($balance, 0, $this->getDecimals()) <= 0) {
                throw new BalanceShortageException(sprintf('balance: %s', $balance));
            }

            $amount = $balance;
        }

        $data = (new TransactionBuilder)->encode($this->toHexAddress($toAddress), $amount, $this->getDecimals());

        $estimateResponse = $this->rpcRequest('wallet/estimateenergy', [
            'owner_address' => $fromAddress,
            'contract_address' => $this->getContractAddress(),
            'function_selector' => 'transfer(address,uint256)',
            'parameter' => $data,
            'visible' => true,
        ]);

        if ($estimateResponse->json('result.result') !== true) {
            throw new RpcException('Failed to estimate energy for transfer');
        }

        $estimateEnergyRequired = $estimateResponse->json('energy_required');

        $response = $this->rpcRequest('wallet/triggersmartcontract', [
            'owner_address' => $fromAddress,
            'contract_address' => $this->getContractAddress(),
            'function_selector' => 'transfer(address,uint256)',
            'parameter' => $data,
            'fee_limit' => 30_000_000,
            'call_value' => 0,
            'visible' => true,
        ]);

        if ($response->json('result.result') !== true) {
            throw new RpcException('Failed to trigger smart contract for transfer');
        }

        $data = $response->json('transaction');

        $estimatedSize = strlen(json_encode($data));
        $adjustFactor = '0.8';
        $estimatedSize = bcmul((string) $estimatedSize, $adjustFactor, 0);

        $resource = $this->getAccountResource($fromAddress);
        $energyAvailable = ((int) ($resource['EnergyLimit'] ?? 0)) - ((int) ($resource['EnergyUsed'] ?? 0));
        $freeNet = ((int) ($resource['freeNetLimit'] ?? 0)) - ((int) ($resource['freeNetUsed'] ?? 0));
        $net = ((int) ($resource['NetLimit'] ?? 0)) - ((int) ($resource['NetUsed'] ?? 0));
        $bandwidthAvailable = max($freeNet, 0) + max($net, 0);

        $missingEnergy = max(0, $estimateEnergyRequired - $energyAvailable);
        $missingBandwidth = max(0, $estimatedSize - $bandwidthAvailable);
        $burnEnergySun = bcmul((string) $missingEnergy, $this->getEnergyPrice());
        $burnBandwidthSun = bcmul((string) $missingBandwidth, $this->getBandwidthPrice());
        $totalSun = bcadd($burnEnergySun, $burnBandwidthSun);
        $fee = $this->formatUnits($totalSun, $this->getNativeCoinDecimals(), false);

        if (bccomp($fee, '0', $this->getNativeCoinDecimals()) > 0) {
            $nativeCoinBalance = $this->getNativeCoin()->getBalance($fromAddress, true);
            if (bccomp($nativeCoinBalance, $fee, $this->getNativeCoinDecimals()) < 0) {
                throw new GasShortageException($nativeCoinBalance, $fee);
            }
        }

        return $this->broadcast($data, $fromPrivateKey);
    }
}
