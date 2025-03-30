<?php

namespace Mitoop\Crypto\Tokens\Tron;

use Mitoop\Crypto\Concerns\Chain\AbstractChain;
use Mitoop\Crypto\Concerns\Tron\AddressFormatter;
use Mitoop\Crypto\Concerns\Tron\Resource;
use Mitoop\Crypto\Concerns\Tron\TransactionBuilder;
use Mitoop\Crypto\Contracts\Tron\TronChainInterface;
use Mitoop\Crypto\Exceptions\RpcException;
use Mitoop\Crypto\Support\Http\BizResponseInterface;
use Mitoop\Crypto\Support\Http\HttpMethod;
use Mitoop\Crypto\Support\Http\TronResponse;

class Chain extends AbstractChain implements TronChainInterface
{
    use AddressFormatter;

    public function getChainId(bool $preferLocal = true): int
    {
        return 0;
    }

    /**
     * @throws RpcException
     */
    public function getLatestBlockNum(): string
    {
        $response = $this->rpcRequest('wallet/getnowblock');

        // 🌰 55540457
        return (string) $response->json('block_header.raw_data.number');
    }

    public function getNativeCoinDecimals(): int
    {
        return 6;
    }

    /**
     * @throws RpcException
     */
    public function getAccountResource(string $address): array
    {
        $response = $this->rpcRequest('wallet/getaccountresource', [
            'address' => $address,
            'visible' => true,
        ]);

        return $response->json();
    }

    /**
     * @throws RpcException
     */
    public function stake(string $address, string $addressPrivateKey, $amount, Resource $resource): string
    {
        $response = $this->rpcRequest('wallet/freezebalancev2', [
            'owner_address' => $address,
            'frozen_balance' => (int) ($amount * $this->getNativeCoinDecimals()),
            'resource' => $resource->value,
            'visible' => true,
        ]);

        $data = $response->json();

        return $this->broadcast($data, $addressPrivateKey);
    }

    /**
     * @throws RpcException
     */
    public function unStake(string $address, string $addressPrivateKey, $amount, Resource $resource): string
    {
        $response = $this->rpcRequest('wallet/unfreezebalancev2', [
            'owner_address' => $address,
            'unfreeze_balance' => (int) ($amount * $this->getNativeCoinDecimals()),
            'resource' => $resource->value,
            'visible' => true,
        ]);

        $data = $response->json();

        return $this->broadcast($data, $addressPrivateKey);
    }

    /**
     * @throws RpcException
     */
    public function delegate(string $from, string $fromPrivateKey, string $to, $amount, Resource $resource): string
    {
        $response = $this->rpcRequest('wallet/delegateresource', [
            'owner_address' => $from,
            'resource' => $resource->value,
            'receiver_address' => $to,
            'balance' => (int) ($amount * $this->getNativeCoinDecimals()),
            'lock' => false,
            'visible' => true,
        ]);

        $data = $response->json();

        return $this->broadcast($data, $fromPrivateKey);
    }

    /**
     * @throws RpcException
     */
    public function unDelegate(string $from, string $fromPrivateKey, string $to, $amount, Resource $resource): string
    {
        $response = $this->rpcRequest('wallet/undelegateresource', [
            'owner_address' => $from,
            'resource' => $resource->value,
            'receiver_address' => $to,
            'balance' => (int) ($amount * $this->getNativeCoinDecimals()),
            'visible' => true,
        ]);

        $data = $response->json();

        return $this->broadcast($data, $fromPrivateKey);
    }

    /**
     * @throws RpcException
     */
    protected function broadcast(array $data, string $privateKey): string
    {
        $data['signature'] = (new TransactionBuilder)->sign($data['txID'], $privateKey);

        $response = $this->rpcRequest('wallet/broadcasttransaction', $data);

        return (string) $response->json('txid');
    }

    /**
     * @throws RpcException
     */
    public function rpcRequest(string $method, array $params = [], HttpMethod $httpMethod = HttpMethod::POST): BizResponseInterface
    {
        $response = match ($httpMethod) {
            HttpMethod::POST => $this->postJson($method, $params),
            HttpMethod::GET => $this->getQuery($method, $params),
        };

        if (! $response->bizOk()) {
            $message = sprintf('%s:%s', $method, $response->getBizErrorMsg());

            throw new RpcException($message);
        }

        return $response;
    }

    public function getExplorerAddressUrl(string $address): string
    {
        return sprintf('%s/#/address/%s', $this->getExplorerUrl(), $address);
    }

    public function getExplorerTransactionUrl(string $txId): string
    {
        return sprintf('%s/#/transaction/%s', $this->getExplorerUrl(), $txId);
    }

    protected function getGuzzleOptions(): array
    {
        return [
            'base_uri' => $this->config('rpc_url'),
            'timeout' => $this->config('rpc_timeout', 120),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'TRON-PRO-API-KEY' => $this->config('rpc_api_key'),
            ],
        ];
    }

    protected function newResponse($response): TronResponse
    {
        return new TronResponse($response);
    }
}
