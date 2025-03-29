# Crypto
Crypto provides a unified interface to simplify the integration of different blockchain protocols, allowing developers to easily embed blockchain functionality into PHP applications. It supports multiple blockchain networks, including Ethereum (ERC-20), Binance Smart Chain (BEP-20), Polygon, and Tron (TRC-20), as well as native coins (ETH, BNB, POL, TRX).

##### What can it do?
- **Manage wallets:** Easily create and manage wallets across different blockchains.
- **Check balances:** Quickly retrieve the current balance of tokens and native coins.
- **Handle transactions:** Query transaction status, details, and history.
- **Execute transfers:** Perform transfers of tokens and native coins.

## Installation

```text
composer require mitoop/crypto-php
```

## Quick Start
```php
use Mitoop\Crypto\Factory;

// Get native coin instance
$token = Factory::createCoin([
   'chain' => 'eth', // Blockchain: eth (Ethereum)/bsc (Binance Smart Chain)/polygon (Polygon)/tron (TRON)
   'chain_id' => 11155111, // Blockchain ID (Tron can be set to 0)
   'rpc_url' => 'https://sepolia.infura.io/v3/your_api_key', // RPC URL
   'rpc_api_key' => '', // API key (leave empty if none)
   'explorer_url' => 'https://sepolia.etherscan.io' // Explorer URL
]);
$token->generateWallet();
$token->getBalance();
//...

// Get token instance
$token = Factory::createToken([
   'chain' => 'eth', // Blockchain: eth (Ethereum)/bsc (Binance Smart Chain)/polygon (Polygon)/tron (TRON)
   'chain_id' => 11155111, // Blockchain ID (Tron can be set to 0)
   'contract_address' => '0x779877A7B0D9E8603169DdbD7836e478b4624789', // Token contract address
   'decimals' => 18, // Token decimals
   'rpc_url' => 'https://sepolia.infura.io/v3/your_api_key', // RPC URL
   'rpc_api_key' => '', // API key (leave empty if none)
   'explorer_url' => 'https://sepolia.etherscan.io' // Explorer URL
]);
$token->generateWallet();
$token->getBalance();
//...

// You can get the native coin instance from a token
$coin = $token->getNativeCoin();
$coin->getBalance();
//...
```

## Donations
If you find this project useful, consider donating:

- **Tron:** `TSB2wHyR9XbBSypkj2CrbRzAwVkXaNrjNJ`
- **Ethereum:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
- **Polygon:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
- **Binance Smart Chain:** `0x3C99992DAa67403A03ba18AD2f36e344cE0A6Bfa`
