<?php

namespace Mitoop\Crypto\Explorers;

use Mitoop\Crypto\Exceptions\InvalidArgumentException;

class ExplorerBuilder
{
    /**
     * @throws InvalidArgumentException
     */
    public static function build(string|array $explorerUrl): array
    {
        $list = [];

        foreach ((array) $explorerUrl as $url) {
            [$type, $class] = self::resolveExplorer($url);

            $list[$type->value] = new $class($url);
        }

        return $list;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected static function resolveExplorer(string $url): array
    {
        $map = [
            'oklink.com' => [ExplorerType::OKLINK, OKLinkExplorer::class],
            'etherscan.io' => [ExplorerType::ETHERSCAN, EvmExplorer::class],
            'bscscan.com' => [ExplorerType::BSCSCAN, EvmExplorer::class],
            'polygonscan.com' => [ExplorerType::POLYGONSCAN, EvmExplorer::class],
            'tronscan.org' => [ExplorerType::TRONSCAN, TronExplorer::class],
        ];

        foreach ($map as $needle => [$type, $class]) {
            if (str_contains($url, $needle)) {
                return [$type, $class];
            }
        }

        throw new InvalidArgumentException(sprintf('Unsupported explorer URL: %s', $url));
    }
}
