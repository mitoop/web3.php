# Crypto

Crypto 致力于为 PHP 开发者打造统一且高效的区块链接口，助力快速接入多链生态，实现跨链资产与协议的无缝集成。支持包括以太坊（ERC-20）、币安智能链（BEP-20）、Polygon 以及波场（TRC-20）等主流公链，同时全面覆盖各链原生代币（ETH、BNB、POL、TRX），为现代分布式应用提供坚实底座。

## 主要功能

- **钱包管理**：轻松创建和管理多链钱包。
- **余额查询**：快速获取代币及原生币余额。
- **交易查询**：支持交易状态、详情及历史记录查询。
- **转账执行**：安全便捷地完成代币及原生币转账。


## 安装

```bash
composer require mitoop/crypto-php
```

## 快速开始

```php
use Mitoop\Crypto\Factory;

// 获取原生币实例
$coin = Factory::createCoin([
   'chain' => 'eth', // 链类型：eth / bsc / polygon / tron
   'chain_id' => 11155111, // 链ID（Tron 设置为0）
   'rpc_url' => 'https://sepolia.infura.io/v3/your_api_key',
   'rpc_api_key' => '', // 如无可留空
   'explorer_url' => 'https://sepolia.etherscan.io'
]);

$coin->generateWallet();
$coin->getBalance();
// ...

// 获取代币实例
$token = Factory::createToken([
   'chain' => 'eth',
   'chain_id' => 11155111,
   'contract_address' => '0x779877A7B0D9E8603169DdbD7836e478b4624789',
   'decimals' => 18,
   'rpc_url' => 'https://sepolia.infura.io/v3/your_api_key',
   'rpc_api_key' => '',
   'explorer_url' => 'https://sepolia.etherscan.io'
]);

$token->generateWallet();
$token->getBalance();
// ...

// 通过代币实例获取原生币实例
$nativeCoin = $token->getNativeCoin();
$nativeCoin->getBalance();
// ...
```

## 捐赠支持

如果你觉得本项目对你有帮助，欢迎支持：

- **Ethereum:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
- **Binance Smart Chain:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
- **Polygon:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
- **Tron:** `TSB2wHyR9XbBSypkj2CrbRzAwVkXaNrjNJ`
