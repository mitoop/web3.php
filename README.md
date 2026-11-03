<h2 align="center">🍎 为 PHP 提供统一的多链钱包、余额与转账能力</h2>

支持以太坊（ERC-20）、币安智能链（BEP-20）、Polygon 以及波场（TRC-20），并覆盖各链原生币（ETH、BNB、POL、TRX）。Solana 目前仅支持钱包生成与地址校验。

- 支持钱包生成与地址校验
- 支持代币和原生币余额查询
- 支持交易状态与详情查询
- 支持代币和原生币转账

## 环境要求

- PHP ^8.3
- ext-bcmath
- ext-gmp
- ext-ctype
- ext-sodium（仅 Solana 钱包需要）

## 安装

```bash
composer require mitoop/web3.php
```

## 快速开始

```php
use Mitoop\Web3\TokenBuilder;
use Mitoop\Web3\CoinBuilder;

$token = TokenBuilder::fromArray([
    'chain' => 'eth',
    'chain_id' => 11155111,
    'contract_address' => '0x779877A7B0D9E8603169DdbD7836e478b4624789',
    'decimals' => 18,
    'rpc_url' => 'https://sepolia.infura.io/v3/your_api_key',
    'rpc_api_key' => '',
    'explorer_url' => 'https://sepolia.etherscan.io',
])->build();

$wallet = $token->generateWallet();
$token->validateAddress($wallet->address);
$token->getBalance($wallet->address);

$coin = $token->getNativeCoin();
// 也可直接：CoinBuilder::fromArray([...])->build();
$coin->getBalance($wallet->address);
```

`chain` 取值：`eth` / `bsc` / `polygon` / `tron`。Solana 钱包请用 `Mitoop\Web3\Wallets\Factory::create('sol')->generate()`。

### Tron `fee_limit`

Tron 代币/原生转账可用 `setFeeLimit` 设置单笔最多愿意燃烧的能量费用上限，单位是 sun，默认 `30_000_000`（30 TRX）：

```php
$tronToken = TokenBuilder::fromArray([
    'chain' => 'tron',
    'chain_id' => 0,
    'contract_address' => 'TXYZopYRdj2D9XRtbG411XZZ3kM5VkAeBf', // 示例
    'decimals' => 6,
    'rpc_url' => 'https://nile.trongrid.io',
    'rpc_api_key' => '',
    'explorer_url' => 'https://nile.tronscan.org',
])->build();

$tronToken->setFeeLimit(50_000_000)->transfer($from, $privateKey, $to, $amount);
```

### EVM 入账列表

EVM `getTransactions` **必须**传入 `from_block`，且只返回转入该地址的交易（不含转出）：

```php
$token->getTransactions($address, [
    'from_block' => '0x0',
    'to_block' => 'latest',
]);
```

## 测试

单元测试（默认；CI 也只跑这一套，不访问链上、不需要密钥）：

```bash
composer test
```

本地真实测试网转账（自备测试网钱包与余额，私钥不进仓库）：

```bash
cp .env.example .env
# 填写密钥与 WEB3_RUN_INTEGRATION=1
composer test:integration
```

余额不足或 RPC 不可用时用例会 skip。

## 打赏

如果该项目对您有所帮助，希望可以请我喝一杯咖啡

- **Ethereum:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
- **Binance Smart Chain:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
- **Polygon:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
- **Tron:** `TSB2wHyR9XbBSypkj2CrbRzAwVkXaNrjNJ`
- **Solana:** `Hwpo37icHRirLfNR7LN6fopkzsTHt91wfzF1iHK3wz44`

## 声明

本项目为开源产品，仅用于学习交流。不可用于任何违反中华人民共和国（含台湾省）或使用者所在地区法律法规的用途。作者仅完成代码开发与开源，不参与任何运营和盈利活动；因使用本项目产生的法律责任由使用者自行承担。

```
！！！Warning！！！
项目中所涉及区块链代币均为学习用途，作者并不赞成区块链所繁衍出代币的金融属性
亦不鼓励和支持任何"挖矿"，"炒币"，"虚拟币ICO"等非法行为
虚拟币市场行为不受监管要求和控制，投资交易需谨慎，仅供学习区块链知识
```

## License

MIT
