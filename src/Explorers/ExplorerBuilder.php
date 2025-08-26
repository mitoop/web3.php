<?php

namespace Mitoop\Web3\Explorers;

use Mitoop\Web3\Exceptions\InvalidArgumentException;

class ExplorerBuilder
{
    protected array $map = [];

    public function __construct(?array $customMap = null)
    {
        $this->map = $customMap ?? $this->defaultMap();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function build(string|array $explorerUrl): array
    {
        $list = [];

        foreach ((array) $explorerUrl as $url) {
            [$type, $class] = $this->resolveExplorer($url);

            $list[$type->value] = new $class($url);
        }

        return $list;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function resolveExplorer(string $url): array
    {
        foreach ($this->map as $needle => [$type, $class]) {
            if (str_contains($url, $needle)) {
                return [$type, $class];
            }
        }

        throw new InvalidArgumentException(sprintf('Unsupported explorer URL: %s', $url));
    }

    protected function defaultMap(): array
    {
        return [
            'oklink.com' => [ExplorerType::OKLINK, OKLinkExplorer::class],
            'etherscan.io' => [ExplorerType::ETHERSCAN, EvmExplorer::class],
            'bscscan.com' => [ExplorerType::BSCSCAN, EvmExplorer::class],
            'polygonscan.com' => [ExplorerType::POLYGONSCAN, EvmExplorer::class],
            'tronscan.org' => [ExplorerType::TRONSCAN, TronExplorer::class],
        ];
    }
}
