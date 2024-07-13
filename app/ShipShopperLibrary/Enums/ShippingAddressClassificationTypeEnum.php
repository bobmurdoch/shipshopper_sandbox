<?php

namespace App\ShipShopperLibrary\Enums;

enum ShippingAddressClassificationTypeEnum: string
{
    case RESIDENTIAL = 'Residential';
    case COMMERCIAL = 'Commercial';
    case UNKNOWN = 'Unknown';
    case MIXED = 'Mixed';
}
