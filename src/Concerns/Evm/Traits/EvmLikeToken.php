<?php

namespace Mitoop\Web3\Concerns\Evm\Traits;

use Mitoop\Web3\Concerns\Evm\Transactions\EIP1559Transaction;
use Mitoop\Web3\Concerns\Evm\Transactions\LegacyTransaction;
use Mitoop\Web3\Exceptions\GasShortageException;
use Mitoop\Web3\Exceptions\RpcException;
use SensitiveParameter;

trait EvmLikeToken
{
    use EvmLikeChain;

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
        $response = $this->rpcRequest('eth_getTransactionCount', [
            $address,
            'latest',
        ]);

        // 🌰 "0x1" => "1"
        return $response->json('result');
    }

    /**
     * @throws RpcException
     */
    protected function getGasPrice(): string
    {
        $response = $this->rpcRequest('eth_gasPrice');

        // 🌰 "0x77359400" => "2000000000" wei
        return $this->hexToDecimal($response->json('result'));
    }

    /**
     * @throws RpcException
     */
    protected function estimateGas(string $fromAddress, string $toAddress, string $value = '', string $data = ''): string
    {
        $params = [
            'from' => $fromAddress,
            'to' => $toAddress,
        ];

        if ($data !== '') {
            $params['data'] = $data;
        }

        if ($value !== '') {
            $params['value'] = $this->decimalToHex($value);
        }

        $response = $this->rpcRequest('eth_estimateGas', [
            $params,
            'latest',
        ]);

        // 🌰 "0x5208" => "21000" gas
        return gmp_strval(gmp_init($response->json('result'), 16));
    }

    /**
     * @throws GasShortageException
     * @throws RpcException
     */
    protected function calculateGasForTransaction(string $estimatedGas, string $nativeBalance, string $amount): array
    {
        if ($amount === '') {
            $amount = '0';
        }
        $gasPrice = $this->getGasPrice();
        $fee = bcmul(bcmul($gasPrice, $estimatedGas, 0), $this->getFeeBuffer(), 0);
        $totalCost = bcadd($amount, $fee, 0);

        if (bccomp($nativeBalance, $totalCost, $this->getNativeCoinDecimals()) < 0) {
            throw new GasShortageException($nativeBalance, $totalCost);
        }

        $gasLimit = bcmul($estimatedGas, $this->getFeeBuffer(), 0);
        $gasLimit = $this->decimalToHex($gasLimit);
        $gasPrice = $this->decimalToHex($gasPrice);

        return [$gasPrice, $gasLimit];
    }

    /**
     * @throws GasShortageException
     * @throws RpcException
     */
    protected function createTransaction(
        string $fromAddress,
        #[SensitiveParameter] string $fromPrivateKey,
        string $toAddress,
        string $nativeBalance,
        string $value = '',
        string $data = ''): string
    {
        if ($this->supportsEIP1559Transaction()) {
            return $this->createEIP1559Transaction($fromAddress, $fromPrivateKey, $toAddress, $nativeBalance, $value, $data);
        }

        return $this->createLegacyTransaction($fromAddress, $fromPrivateKey, $toAddress, $nativeBalance, $value, $data);
    }

    /**
     * @throws RpcException
     * @throws GasShortageException
     */
    protected function createLegacyTransaction(
        string $fromAddress,
        #[SensitiveParameter] string $fromPrivateKey,
        string $toAddress,
        string $nativeBalance,
        string $value = '',
        string $data = ''): string
    {
        [$gasPrice, $gasLimit] = $this->calculateGasForTransaction(
            $this->estimateGas($fromAddress, $toAddress, $value, $data),
            $nativeBalance,
            $value
        );

        if ($value !== '') {
            $value = $this->decimalToHex($value);
        }

        $transaction = new LegacyTransaction(
            $this->getNonce($fromAddress),
            $gasPrice,
            $gasLimit,
            $toAddress,
            $value,
            $data
        );

        $response = $this->rpcRequest('eth_sendRawTransaction', [
            $transaction->build($fromPrivateKey, $this->getChainId()),
        ]);

        // 🌰 "0x0f09e12c4c3dbfcad9bc71c3c73adb0c00c2a13bf9f5e04366c841ee9f61fb5e"
        return $response->json('result');
    }

    /**
     * @throws RpcException
     * @throws GasShortageException
     */
    protected function createEIP1559Transaction(
        string $fromAddress,
        #[SensitiveParameter] string $fromPrivateKey,
        string $toAddress,
        string $nativeBalance,
        string $value = '',
        string $data = ''
    ): string {
        [$baseFeePerGasHex, $maxPriorityFeePerGasHex] = $this->getBaseFeePerGas();

        $baseFeeWei = $this->hexToDecimal($baseFeePerGasHex);
        $priorityFeeWei = $this->hexToDecimal($maxPriorityFeePerGasHex);
        if (bccomp($priorityFeeWei, '0', 0) <= 0) {
            $priorityFeeWei = '25000000000';
        }

        $totalFeeWei = bcmul(bcadd($baseFeeWei, $priorityFeeWei, 0), $this->getFeeBuffer(), 0);

        $maxFeePerGasHex = $this->decimalToHex($totalFeeWei);
        $maxPriorityFeePerGasHex = $this->decimalToHex($priorityFeeWei);

        $gasLimit = $this->estimateGas($fromAddress, $toAddress, $value, $data);
        $gasLimitHex = $this->decimalToHex($gasLimit);

        $amount = ($value === '' ? '0' : $value);
        $maxCost = bcadd($amount, bcmul($gasLimit, $totalFeeWei, 0), 0);
        if (bccomp($nativeBalance, $maxCost, $this->getNativeCoinDecimals()) < 0) {
            throw new GasShortageException($nativeBalance, $maxCost);
        }

        if ($value !== '') {
            $value = $this->decimalToHex($value);
        }

        $transaction = new EIP1559Transaction(
            $this->getNonce($fromAddress),
            $maxPriorityFeePerGasHex,
            $maxFeePerGasHex,
            $gasLimitHex,
            $toAddress,
            $value,
            $data
        );

        $response = $this->rpcRequest('eth_sendRawTransaction', [
            $transaction->build($fromPrivateKey, $this->getChainId()),
        ]);

        return $response->json('result');
    }

    protected function getFeeBuffer(): string
    {
        return '1.2';
    }
}
