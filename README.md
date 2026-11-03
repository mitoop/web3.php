# web3.php

PHP 多链 Web3 工具库：统一封装钱包生成、余额查询与转账，面向 EVM 兼容链与 Tron。

| 能力 | eth / bsc / polygon | tron | sol |
| --- | --- | --- | --- |
| 钱包生成 / 地址校验 | ✅ | ✅ | ✅ |
| 原生币余额 / 转账 | ✅ | ✅ | — |
| 代币余额 / 转账 | ERC-20 / BEP-20 | TRC-20 | — |
| 交易查询 / 状态 | ✅ | ✅ | — |
| 资源质押 / 委托 | — | ✅ | — |

## 环境要求

- PHP `^8.3`
- 扩展：`bcmath`、`gmp`、`ctype`
- `ext-sodium`（仅 Solana 钱包需要）

## 安装

```bash
composer require mitoop/web3.php
```

## 钱包

钱包为离线能力，不依赖 RPC。推荐通过 `Wallets\Factory` 直接调用：

```php
use Mitoop\Web3\Wallets\Factory;

$wallet = Factory::create('eth')->generate();
// $wallet->address / $wallet->privateKey / $wallet->publicKey

Factory::create('eth')->validate($wallet->address); // bool
```

支持的 `chain`：`eth`、`bsc`、`polygon`、`tron`、`sol`。

Token / Coin 实例上也提供同名能力，便于在已有链上下文中顺手调用；内部委托给 Factory：

```php
$wallet = $token->generateWallet();
$token->validateAddress($wallet->address);
```

## Token 与 Coin

通过 Builder 注入链配置后即可查询余额、发起转账：

```php
use Mitoop\Web3\TokenBuilder;
use Mitoop\Web3\CoinBuilder;

$token = TokenBuilder::fromArray([
    'chain' => 'eth',
    'chain_id' => 11155111, // Sepolia
    'contract_address' => '0x779877A7B0D9E8603169DdbD7836e478b4624789',
    'decimals' => 18,
    'rpc_url' => 'https://sepolia.infura.io/v3/your_api_key',
    'rpc_api_key' => '',
    'explorer_url' => 'https://sepolia.etherscan.io',
])->build();

$balance = $token->getBalance($address);
$txId = $token->transfer($from, $privateKey, $to, '1.5');
$status = $token->getTransactionStatus($txId);

// 同源生币：从 Token 取，或直接用 CoinBuilder
$coin = $token->getNativeCoin();
// $coin = CoinBuilder::fromArray([...])->build();

$coin->getBalance($address);
$coin->transfer($from, $privateKey, $to, '0.01');
```

### 配置项

| 字段 | Token | Coin | 说明 |
| --- | --- | --- | --- |
| `chain` | 必填 | 必填 | `eth` / `bsc` / `polygon` / `tron` |
| `chain_id` | 必填 | 必填 | 链 ID；Tron 可填 `0` |
| `contract_address` | 必填 | — | 代币合约地址 |
| `decimals` | 必填 | — | 代币精度 |
| `rpc_url` | 必填 | 必填 | RPC 节点 |
| `rpc_api_key` | 可选 | 可选 | 节点 API Key |
| `rpc_timeout` | 可选 | 可选 | 请求超时（秒） |
| `explorer_url` | 必填 | 必填 | 浏览器地址，用于拼装链接 |

### EVM：入账交易列表

`getTransactions` **必须**传入 `from_block`，且仅返回转入该地址的记录（不含转出）：

```php
$token->getTransactions($address, [
    'from_block' => '0x0',
    'to_block' => 'latest',
]);
```

### Tron：`fee_limit`

代币 / 原生转账可通过 `setFeeLimit` 设置单笔可燃烧能量费用上限，单位为 sun，默认 `30_000_000`（30 TRX）：

```php
$tronToken = TokenBuilder::fromArray([
    'chain' => 'tron',
    'chain_id' => 0,
    'contract_address' => 'TXYZopYRdj2D9XRtbG411XZZ3kM5VkAeBf',
    'decimals' => 6,
    'rpc_url' => 'https://nile.trongrid.io',
    'rpc_api_key' => '',
    'explorer_url' => 'https://nile.tronscan.org',
])->build();

$tronToken->setFeeLimit(50_000_000)->transfer($from, $privateKey, $to, $amount);
```

## 测试

单元测试（默认；CI 仅跑此套，不访问链上、无需密钥）：

```bash
composer test
```

集成测试（真实测试网转账；私钥仅放本地 `.env`，勿提交仓库）：

```bash
cp .env.example .env
# 填写密钥，并设置 WEB3_RUN_INTEGRATION=1
composer test:integration
```

余额不足或 RPC 不可用时，相关用例会自动 skip。

## 安全提示

- 私钥仅用于本地签名，请勿写入代码仓库或日志。
- 生产环境请使用受信 RPC，并严格控制热钱包余额。
- 本库侧重学习与工程封装，上线前请自行完成充分测试与审计。

## 打赏

如果该项目对您有所帮助，欢迎请作者喝杯咖啡：

| 网络 | 地址 |
| --- | --- |
| Ethereum | `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa` |
| BNB Smart Chain | `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa` |
| Polygon | `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa` |
| Tron | `TSB2wHyR9XbBSypkj2CrbRzAwVkXaNrjNJ` |
| Solana | `Hwpo37icHRirLfNR7LN6fopkzsTHt91wfzF1iHK3wz44` |

## 声明

本项目为开源产品，仅用于学习交流。不可用于任何违反中华人民共和国（含台湾省）或使用者所在地区法律法规的用途。作者仅完成代码开发与开源，不参与任何运营和盈利活动；因使用本项目产生的法律责任由使用者自行承担。

> **Warning**  
> 项目中涉及的区块链代币均为学习用途。作者不赞成将代币作为投机标的，亦不鼓励或支持挖矿、炒币、虚拟币 ICO 等违法违规行为。虚拟币市场不受传统金融监管约束，投资交易需谨慎，本项目仅供学习区块链相关知识。

## License

[MIT](LICENSE)
