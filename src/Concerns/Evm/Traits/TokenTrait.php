<?php

namespace Mitoop\Crypto\Concerns\Evm\Traits;

use Mitoop\Crypto\Concerns\Evm\Transactions\TransactionBuilder;
use Mitoop\Crypto\Concerns\HasTokenProperties;
use Mitoop\Crypto\Exceptions\BalanceShortageException;
use Mitoop\Crypto\Exceptions\GasShortageException;
use Mitoop\Crypto\Exceptions\InvalidArgumentException;
use Mitoop\Crypto\Exceptions\RpcException;
use Mitoop\Crypto\Support\UnitFormatter;
use Mitoop\Crypto\Transactions\Transaction;
use Mitoop\Crypto\Transactions\TransactionInfo;
use SensitiveParameter;

trait TokenTrait
{
    use EvmLikeToken,HasTokenProperties;

    /**
     * @throws RpcException
     */
    public function getBalance(string $address): string
    {
        $methodId = '0x70a08231';
        $paddedAddress = $this->toPaddedAddress($address);

        $data = $methodId.$paddedAddress;

        $response = $this->rpcRequest('eth_call', [
            [
                'to' => $this->getContractAddress(),
                'data' => $data,
            ],
            'latest',
        ]);

        return UnitFormatter::formatUnits($response->json('result'), $this->getDecimals());
    }

    /**
     * @param  array{
     *       latest_block_num?: string // 十六进制如 "0x2e2a650"
     *   }  $params  查询参数
     *
     * @throws RpcException
     */
    public function getTransactions(string $address, array $params = []): array
    {
        $topic0 = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';
        $topic2 = $this->toPaddedAddress($address, true);

        $response = $this->rpcRequest('eth_getLogs', [
            [
                'fromBlock' => $params['latest_block_num'] ?? '0x0',
                'toBlock' => 'latest',
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
                $this->toAddressFormat($item['topics'][1]),
                $this->toAddressFormat($item['topics'][2]),
                $item['data'],
                UnitFormatter::formatUnits($item['data'], $this->getDecimals()),
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

        $amount = 0;
        $from = $response->json('result.from');
        $to = '';
        $logs = $response->json('result.logs', []);
        foreach ($logs as $log) {
            if (! empty($log['topics'][0])
                &&
                $log['topics'][0] === '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef'
                &&
                strtolower($log['address']) === strtolower($this->getContractAddress())
                &&
                ! $log['removed']
            ) {
                $amount = UnitFormatter::formatUnits($log['data'] ?? '0x0', $this->getDecimals());
                $to = $this->toAddressFormat($log['topics'][2]);
            }
        }

        $fee = bcmul(
            gmp_strval(gmp_init($response->json('result.effectiveGasPrice'), 16)),
            gmp_strval(gmp_init($response->json('result.gasUsed'), 16)),
            0
        );

        $fee = UnitFormatter::formatUnits($fee, $this->getNativeCoinDecimals());

        return new TransactionInfo(
            true,
            (string) $response->json('result.transactionHash'),
            (string) $from,
            $to,
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
        [$gasPrice, $gasLimit] = $this->computeGas(
            $this->estimateGas($fromAddress, $this->getContractAddress(), $data),
            $this->getNativeCoin()->getBalance($fromAddress),
        );

        $nonce = gmp_strval(gmp_init($this->getTransactionCount($fromAddress), 10), 16);

        if (! $this->supportsEIP1559Transaction()) {
            return $this->createLegacyTransaction($fromPrivateKey, $nonce, $gasPrice, $gasLimit, $this->getContractAddress(), data: $data);
        }

        return $this->createEIP1559Transaction($fromPrivateKey, $nonce, $gasLimit, $this->getContractAddress(), data: $data);
    }
}
