<?php

namespace App\ShipShopperLibrary\Enums;

enum AddressTypeEnum: string
{
    case RESIDENTIAL = 'Residential';
    case COMMERCIAL = 'Commercial';
    case UNKNOWN = 'Unknown';
}
