<?php

namespace App\Enums;

enum CollectionRarity: string
{
    case COMMON = 'Common';
    case UNCOMMON = 'Uncommon';
    case RARE = 'Rare';
    case LEGENDARY = 'Legendary';
}
