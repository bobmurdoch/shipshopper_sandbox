<?php
namespace App\Enums;

enum ShippingCarrierEnum: string
{
    case UPS = 'ups';
    case USPS = 'usps';
    case FEDEX = 'fedex';
}
