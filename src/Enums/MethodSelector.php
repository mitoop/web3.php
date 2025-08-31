<?php

namespace Mitoop\Web3\Enums;

enum MethodSelector: string
{
    case EvmBalance = '0x70a08231'; // balanceOf(address)
    case EvmTransfer = '0xa9059cbb'; // transfer(address,uint256)
}
