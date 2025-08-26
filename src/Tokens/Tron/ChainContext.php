<?php

namespace Mitoop\Web3\Tokens\Tron;

use Mitoop\Web3\Concerns\AbstractChainContext;
use Mitoop\Web3\Concerns\Tron\AddressFormatter;
use Mitoop\Web3\Concerns\Tron\Resource;
use Mitoop\Web3\Concerns\Tron\TransactionBuilder;
use Mitoop\Web3\Contracts\Tron\TronChainContextInterface;
use Mitoop\Web3\Exceptions\BroadcastException;
use Mitoop\Web3\Exceptions\RpcException;
use Mitoop\Web3\Support\Http\BizResponseInterface;
use Mitoop\Web3\Support\Http\HttpMethod;
use Mitoop\Web3\Support\Http\TronResponse;
use SensitiveParameter;

class ChainContext extends AbstractChainContext implements TronChainContextInterface
{
    use AddressFormatter;

    protected ?string $bandwidthPrice = null;

    protected ?string $energyPrice = null;

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
     * @throws BroadcastException
     */
    public function stake(
        string $address,
        #[SensitiveParameter] string $addressPrivateKey,
        $amount,
        Resource $resource
    ): string {
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
     * @throws BroadcastException
     */
    public function unStake(
        string $address,
        #[SensitiveParameter] string $addressPrivateKey,
        $amount,
        Resource $resource
    ): string {
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
     * @throws BroadcastException
     */
    public function delegate(
        string $from,
        #[SensitiveParameter] string $fromPrivateKey,
        string $to,
        $amount,
        Resource $resource
    ): string {
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
     * @throws BroadcastException
     */
    public function unDelegate(
        string $from,
        #[SensitiveParameter] string $fromPrivateKey,
        string $to,
        $amount,
        Resource $resource
    ): string {
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
     * @throws BroadcastException
     */
    protected function broadcast(array $data, #[SensitiveParameter] string $privateKey): string
    {
        $data['signature'] = (new TransactionBuilder)->sign($data['txID'], $privateKey);

        $response = $this->rpcRequest('wallet/broadcasttransaction', $data);

        if ($response->json('result') !== true) {
            throw new BroadcastException(sprintf('%s:%s', $response->json('code'), $response->json('message')));
        }

        return (string) $response->json('txid');
    }

    /**
     * @throws RpcException
     */
    public function getBandwidthPrices(): array
    {
        $response = $this->rpcRequest('wallet/getbandwidthprices');

        return $this->parsePriceHistoryString($response->json('prices'));
    }

    /**
     * @throws RpcException
     */
    public function getBandwidthPrice(): string
    {
        if (! is_null($this->bandwidthPrice)) {
            return $this->bandwidthPrice;
        }

        $prices = $this->getBandwidthPrices();

        return (string) end($prices);
    }

    public function setBandwidthPrice(string $price): static
    {
        $this->bandwidthPrice = $price;

        return $this;
    }

    /**
     * @throws RpcException
     */
    public function getEnergyPrices(): array
    {
        $response = $this->rpcRequest('wallet/getenergyprices');

        return $this->parsePriceHistoryString($response->json('prices'));
    }

    /**
     * @throws RpcException
     */
    public function getEnergyPrice(): string
    {
        if (! is_null($this->energyPrice)) {
            return $this->energyPrice;
        }

        $prices = $this->getEnergyPrices();

        return (string) end($prices);
    }

    public function setEnergyPrice(string $price): static
    {
        $this->energyPrice = $price;

        return $this;
    }

    /**
     * @throws RpcException
     */
    protected function parsePriceHistoryString(?string $input): array
    {
        if (empty($input)) {
            throw new RpcException('Input string is empty or null.');
        }

        $map = [];

        foreach (explode(',', $input) as $pair) {
            if (! str_contains($pair, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $pair, 2);

            $map[trim($key)] = trim($value);
        }

        if (empty($map)) {
            throw new RpcException('Parsed result is empty after processing input string');
        }

        return $map;
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

    protected function newResponse($response): TronResponse
    {
        return new TronResponse($response);
    }
}
