<?php

namespace Mitoop\Web3\Support\Http;

use GuzzleHttp\Client;

/**
 * @method array getGuzzleOptions()
 * @method Response newResponse($response)
 */
trait HttpRequestClient
{
    protected function postForm($url, $formData, $headers = []): Response
    {
        return $this->send('post', $url, [
            'headers' => $headers,
            'form_params' => $formData,
        ]);
    }

    protected function postJson($endpoint, $jsonData = [], $headers = []): Response
    {
        return $this->send('post', $endpoint, [
            'headers' => $headers,
            'json' => $jsonData,
        ]);
    }

    protected function getQuery($endpoint, $data, $headers = []): Response
    {
        return $this->send('get', $endpoint, [
            'headers' => $headers,
            'query' => $data,
        ]);
    }

    protected function send($method, $endpoint, $options = []): Response
    {
        $response = $this->getHttpClient($this->getBaseOptions())->{$method}($endpoint, $options);

        if (method_exists($this, 'newResponse')) {
            return $this->newResponse($response);
        }

        return new Response($response);
    }

    protected function getBaseOptions(): array
    {
        $options = method_exists($this, 'getGuzzleOptions') ? $this->getGuzzleOptions() : [];

        return array_merge($options, [
            'verify' => false,
            'http_errors' => false,
            'timeout' => method_exists($this, 'getTimeout') ? $this->getTimeout() : 60,
        ]);
    }

    protected function getHttpClient(array $options = []): Client
    {
        return new Client($options);
    }
}
