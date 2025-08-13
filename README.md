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
use Mitoop\Crypto\TokenBuilder;

// 获取代币实例
$token = TokenBuilder::fromArray([
   'chain' => 'eth',
   'chain_id' => 11155111,
   'contract_address' => '0x779877A7B0D9E8603169DdbD7836e478b4624789',
   'decimals' => 18,
   'rpc_url' => 'https://sepolia.infura.io/v3/your_api_key',
   'rpc_api_key' => '',
   'explorer_url' => 'https://sepolia.etherscan.io'
])->build();

$token->generateWallet();
$token->getBalance();
// ...

// 通过代币实例获取原生币实例
$coin = $token->getNativeCoin();
$coin->getBalance();
// ...
```

## 打赏

如果该项目对您有所帮助，希望可以请我喝一杯咖啡☕️

- **Ethereum:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
- **Binance Smart Chain:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
- **Polygon:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
- **Tron:** `TSB2wHyR9XbBSypkj2CrbRzAwVkXaNrjNJ`
- **Solana:** `Hwpo37icHRirLfNR7LN6fopkzsTHt91wfzF1iHK3wz44`

## 声明
本项目为开源的产品，仅用于学习交流使用！       
不可用于任何违反中华人民共和国(含台湾省)或使用者所在地区法律法规的用途。           
因为作者即本人仅完成代码的开发和开源活动(开源即任何人都可以下载使用或修改分发)，从未参与用户的任何运营和盈利活动。       
且不知晓用户后续将程序源代码用于何种用途，故用户使用过程中所带来的任何法律责任即由用户自己承担。
```
！！！Warning！！！
项目中所涉及区块链代币均为学习用途，作者并不赞成区块链所繁衍出代币的金融属性
亦不鼓励和支持任何"挖矿"，"炒币"，"虚拟币ICO"等非法行为
虚拟币市场行为不受监管要求和控制，投资交易需谨慎，仅供学习区块链知识
```
