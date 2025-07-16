<?php

namespace Mitoop\Crypto\Explorers;

enum ExplorerType: string
{
    case OKLINK = 'oklink';
    case ETHERSCAN = 'etherscan';
    case BSCSCAN = 'bscscan';
    case POLYGONSCAN = 'polygonscan';
    case TRONSCAN = 'tronscan';
}
