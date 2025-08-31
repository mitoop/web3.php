<?php

namespace Mitoop\Web3\Concerns\Evm\Traits;

use Mitoop\Web3\Concerns\Evm\Transactions\TransactionBuilder;
use Mitoop\Web3\Concerns\HasTokenProperties;
use Mitoop\Web3\Enums\EventTopic;
use Mitoop\Web3\Exceptions\BalanceShortageException;
use Mitoop\Web3\Exceptions\GasShortageException;
use Mitoop\Web3\Exceptions\InvalidArgumentException;
use Mitoop\Web3\Exceptions\RpcException;
use Mitoop\Web3\Transactions\Transaction;
use Mitoop\Web3\Transactions\TransactionInfo;
use SensitiveParameter;

trait TokenTrait
{
    use EvmLikeToken,HasTokenProperties;

    /**
     * @throws RpcException
     */
    public function getBalance(string $address): string
    {
        $abiMethodId = '0x70a08231';

        $response = $this->rpcRequest('eth_call', [
            [
                'to' => $this->getContractAddress(),
                'data' => $abiMethodId.$this->toAbiPaddedAddress($address),
            ],
            'latest',
        ]);

        return $this->formatUnits($response->json('result'), $this->getDecimals());
    }

    /**
     * @param  array{
     *      from_block: string, // 十六进制
     *      to_block: string, // 'latest'
     *   }  $params  查询参数
     *
     * @throws RpcException
     */
    public function getTransactions(string $address, array $params = []): array
    {
        $topic0 = EventTopic::EvmTransfer->value;
        $topic2 = $this->toAbiPaddedAddress($address, true);

        $response = $this->rpcRequest('eth_getLogs', [
            [
                'fromBlock' => $params['from_block'],
                'toBlock' => $params['to_block'] ?? 'latest',
                'address' => $this->getContractAddress(),
                'topics' => [$topic0, null, $topic2],
            ],
        ]);

        $transactions = [];
        foreach ($response->json('result') as $item) {
            if ($item['removed'] || ! $item['blockNumber']) {
                continue;
            }

            $transactions[] = new Transaction(
                $item['transactionHash'],
                $item['address'],
                $this->normalizeAddress($item['topics'][1]),
                $this->normalizeAddress($item['topics'][2]),
                $item['data'],
                $this->formatUnits($item['data'], $this->getDecimals()),
                $this->getDecimals(),
            );
        }

        return $transactions;
    }

    /**
     * @throws RpcException
     */
    public function getTransaction(string $txId): ?TransactionInfo
    {
        $response = $this->rpcRequest('eth_getTransactionReceipt', [
            $txId,
        ]);

        $result = $response->json('result');

        if ($result === null) {
            return null;
        }

        $status = hexdec($response->json('result.status', 0)) === 1;

        if (! $status) {
            return null;
        }

        $value = '';
        $amount = '';
        $from = $response->json('result.from');
        $to = '';
        $logs = $response->json('result.logs', []);
        foreach ($logs as $log) {
            if (! empty($log['topics'][0])
                &&
                $log['topics'][0] === EventTopic::EvmTransfer->value
                &&
                strtolower($log['address']) === strtolower($this->getContractAddress())
                &&
                ! $log['removed']
            ) {
                $value = (string) $log['data'];
                $amount = $this->formatUnits($value, $this->getDecimals());
                $to = $this->normalizeAddress($log['topics'][2]);
            }
        }

        $fee = bcmul(
            $this->hexToDecimal($response->json('result.effectiveGasPrice')),
            $this->hexToDecimal($response->json('result.gasUsed')),
            0
        );

        $fee = $this->formatUnits($fee, $this->getNativeCoinDecimals());

        return new TransactionInfo(
            true,
            (string) $response->json('result.transactionHash'),
            (string) $from,
            $to,
            $value,
            $amount,
            $fee,
        );
    }

    /**
     * @throws BalanceShortageException
     * @throws InvalidArgumentException
     * @throws RpcException
     * @throws GasShortageException
     */
    public function transfer(
        string $fromAddress,
        #[SensitiveParameter] string $fromPrivateKey,
        string $toAddress,
        string $amount,
        bool $bestEffort = false): string
    {
        if (bccomp($amount, 0, $this->getDecimals()) <= 0) {
            throw new InvalidArgumentException('Invalid amount');
        }

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

        $data = (new TransactionBuilder)->encode($toAddress, $amount, $this->getDecimals());
        $nativeBalance = $this->getNativeCoin()->getBalance($fromAddress);

        return $this->createTransaction(
            $fromAddress,
            $fromPrivateKey,
            $this->getContractAddress(),
            $nativeBalance,
            data: $data
        );
    }
}
