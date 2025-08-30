<?php

namespace Mitoop\Web3\Concerns\Evm\Traits;

use Mitoop\Web3\Exceptions\RpcException;
use Mitoop\Web3\Support\Http\BizResponseInterface;
use Mitoop\Web3\Support\Http\EvmResponse;
use Mitoop\Web3\Support\Http\HttpMethod;

trait EvmLikeChain
{
    use EvmAddressTrait;

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
    public function getBaseFeePerGas(int $rewardPercentile = 50): array
    {
        $response = $this->rpcRequest('eth_feeHistory', [
            1,
            'latest',
            [$rewardPercentile],
        ]);

        // 🌰 ["0xc5f55767", "0x3b9aca00"]
        return [$response->json('result.baseFeePerGas.1'), $response->json('result.reward.0.0')];
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

    protected function newResponse($response): EvmResponse
    {
        return new EvmResponse($response);
    }

    protected function supportsEIP1559Transaction(): bool
    {
        return true;
    }
}
