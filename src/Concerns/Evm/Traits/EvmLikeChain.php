<?php

namespace Mitoop\Crypto\Concerns\Evm\Traits;

use Mitoop\Crypto\Exceptions\RpcException;
use Mitoop\Crypto\Support\Http\BizResponseInterface;
use Mitoop\Crypto\Support\Http\EvmResponse;
use Mitoop\Crypto\Support\Http\HttpMethod;

trait EvmLikeChain
{
    use AddressFormatter;

    /**
     * @throws RpcException
     */
    public function getChainId(bool $preferLocal = true): int
    {
        if ($preferLocal) {
            return (int) $this->config('chain_id');
        }

        $response = $this->rpcRequest('eth_chainId');

        return hexdec($response->json('result'));
    }

    public function getNativeCoinDecimals(): int
    {
        return 18;
    }

    /**
     * @throws RpcException
     */
    public function getLatestBlockNum(): string
    {
        $response = $this->rpcRequest('eth_blockNumber');

        // 🌰 "0x2e29731"
        return $response->json('result');
    }

    /**
     * @throws RpcException
     */
    public function getTransactionCount(string $address, string $block = 'latest'): string
    {
        $response = $this->rpcRequest('eth_getTransactionCount', [
            $address,
            $block,
        ]);

        // 🌰 "0x1" => "1"
        return gmp_strval(gmp_init($response->json('result'), 16));
    }

    /**
     * @throws RpcException
     */
    public function getBaseFeePerGas(): array
    {
        $response = $this->rpcRequest('eth_feeHistory', [
            1,
            'latest',
            [50],
        ]);

        // 🌰 ["0xc5f55767", "0x3b9aca00"]
        return [$response->json('result.baseFeePerGas.0'), $response->json('result.reward.0.0')];
    }

    /**
     * @throws RpcException
     */
    public function rpcRequest(string $method, array $params = [], HttpMethod $httpMethod = HttpMethod::POST): BizResponseInterface
    {
        $response = $this->postJson('', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => $method,
            'params' => $params,
        ]);

        if (! $response->bizOk()) {
            $message = sprintf('%s:%s', $method, $response->getBizErrorMsg());

            throw new RpcException($message);
        }

        return $response;
    }

    public function getExplorerAddressUrl(string $address): string
    {
        return sprintf('%s/address/%s', $this->getExplorerUrl(), $address);
    }

    public function getExplorerTransactionUrl(string $txId): string
    {
        return sprintf('%s/tx/%s', $this->getExplorerUrl(), $txId);
    }

    protected function newResponse($response): EvmResponse
    {
        return new EvmResponse($response);
    }

    protected function supportsEIP1559Transaction(): bool
    {
        return true;
    }
}
