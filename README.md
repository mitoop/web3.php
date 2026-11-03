# web3.php

<p align="center">🍎 为 PHP 提供统一的多链钱包、余额与转账能力</p>

支持以太坊（ERC-20）、币安智能链（BEP-20）、Polygon 以及波场（TRC-20），并覆盖各链原生币（ETH、BNB、POL、TRX）。

- 支持钱包生成与地址校验
- 支持代币和原生币余额查询
- 支持交易状态与详情查询
- 支持代币和原生币转账

## 环境要求

* PHP ^8.3
* ext-bcmath
* ext-gmp
* ext-ctype

## 安装

通过 Composer 安装：

```bash
composer require mitoop/web3.php
```

## 快速开始

```php
use Mitoop\Web3\TokenBuilder;

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
$token->getBalance($wallet->address);

$coin = $token->getNativeCoin();
$coin->getBalance($wallet->address);
```

Tron 代币转账可用 `setFeeLimit` 设置单笔最多愿意燃烧的 TRX，单位是 sun，默认 `30_000_000`（30 TRX）：

```php
$token->setFeeLimit(50_000_000)->transfer($from, $privateKey, $to, $amount);
```

EVM 入账列表必须传入 `from_block`，只返回转入该地址的交易：

```php
$token->getTransactions($address, [
    'from_block' => '0x0',
    'to_block' => 'latest',
]);
```

## 测试

```bash
composer test
composer format
```

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
