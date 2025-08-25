<?php

namespace Mitoop\Crypto\Concerns\Evm\Traits;

use Mitoop\Crypto\Concerns\Evm\Transactions\EIP1559Transaction;
use Mitoop\Crypto\Concerns\Evm\Transactions\LegacyTransaction;
use Mitoop\Crypto\Exceptions\GasShortageException;
use Mitoop\Crypto\Exceptions\RpcException;
use SensitiveParameter;

trait EvmLikeToken
{
    /**
     * @throws RpcException
     */
    public function getTransactionStatus(string $txId): bool
    {
        $response = $this->rpcRequest('eth_getTransactionReceipt', [
            $txId,
        ]);

        $result = $response->json('result');

        if ($result === null) {
            return false;
        }

        return hexdec($response->json('result.status', 0)) === 1;
    }

    /**
     * @throws RpcException
     */
    protected function getNonce(string $address): string
    {
        return gmp_strval(gmp_init($this->getTransactionCount($address), 10), 16);
    }

    /**
     * @throws RpcException
     */
    protected function getGasPrice(): string
    {
        $response = $this->rpcRequest('eth_gasPrice');

        // 🌰 "0x77359400" => "2000000000" wei
        return gmp_strval(gmp_init($response->json('result'), 16));
    }

    /**
     * @throws RpcException
     */
    protected function estimateGas(string $fromAddress, string $toAddress, ?string $data = null): string
    {
        $params = [
            'from' => $fromAddress,
            'to' => $toAddress,
            'block' => 'latest',
        ];

        if (! is_null($data)) {
            $params['data'] = $data;
        }

        $response = $this->rpcRequest('eth_estimateGas', [
            $params,
        ]);

        // 🌰 "0x5208" => "21000" gas
        return gmp_strval(gmp_init($response->json('result'), 16));
    }

    /**
     * @throws GasShortageException
     * @throws RpcException
     */
    protected function computeGas(string $estimatedGas, string $nativeBalance, string $amount = '0'): array
    {
        $gasPrice = $this->getGasPrice();
        $fee = bcmul(bcmul($gasPrice, $estimatedGas, 0), $this->getFeeBuffer(), 0);
        $totalCost = bcadd($amount, $fee, 0);

        if (bccomp($nativeBalance, $totalCost, 0) < 0) {
            throw new GasShortageException($nativeBalance, $totalCost);
        }

        $gasLimit = bcmul($estimatedGas, $this->getFeeBuffer(), 0);
        $gasLimit = gmp_strval(gmp_init($gasLimit, 10), 16);
        $gasPrice = gmp_strval(gmp_init($gasPrice, 10), 16);

        return [$gasPrice, $gasLimit];
    }

    /**
     * @throws RpcException
     */
    protected function createLegacyTransaction(
        #[SensitiveParameter] string $fromPrivateKey,
        string $nonce,
        string $gasPrice,
        string $gasLimit,
        string $to = '',
        string $value = '',
        string $data = ''): string
    {
        $transaction = new LegacyTransaction($nonce, $gasPrice, $gasLimit, $to, $value, $data);

        $response = $this->rpcRequest('eth_sendRawTransaction', [
            $transaction->build($fromPrivateKey, $this->getChainId()),
        ]);

        // 🌰 "0x0f09e12c4c3dbfcad9bc71c3c73adb0c00c2a13bf9f5e04366c841ee9f61fb5e"
        return $response->json('result');
    }

    /**
     * @throws RpcException
     */
    protected function createEIP1559Transaction(
        #[SensitiveParameter] string $fromPrivateKey,
        string $nonce,
        string $gasLimit,
        string $to = '',
        string $value = '',
        string $data = ''
    ): string {
        [$baseFeePerGasHex, $maxPriorityFeePerGasHex] = $this->getBaseFeePerGas();

        $baseFeeWei = gmp_strval(gmp_init($baseFeePerGasHex, 16));
        $priorityFeeWei = gmp_strval(gmp_init($maxPriorityFeePerGasHex, 16));

        if (bccomp($priorityFeeWei, '0', 0) <= 0) {
            $priorityFeeWei = '25000000000';
        }

        $totalFeeWei = bcadd($baseFeeWei, $priorityFeeWei, 0);
        $totalFeeWei = bcmul($totalFeeWei, $this->getFeeBuffer(), 0);

        $maxFeePerGas = '0x'.gmp_strval(gmp_init($totalFeeWei, 10), 16);
        $maxPriorityFeePerGas = '0x'.gmp_strval(gmp_init($priorityFeeWei, 10), 16);

        $transaction = new EIP1559Transaction(
            $nonce,
            $maxPriorityFeePerGas,
            $maxFeePerGas,
            $gasLimit,
            $to,
            $value,
            $data);

        $response = $this->rpcRequest('eth_sendRawTransaction', [
            $transaction->build($fromPrivateKey, $this->getChainId()),
        ]);

        // 🌰 "0x5ec2cfcec7693750992a26f07b4eaa7d3fc792021d105dfdbf78989c9d4df18a"
        return $response->json('result');
    }

    protected function getFeeBuffer(): string
    {
        return '1.2';
    }
}
