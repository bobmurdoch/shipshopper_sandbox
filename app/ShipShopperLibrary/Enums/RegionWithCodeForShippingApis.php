<?php
namespace App\ShipShopperLibrary\Enums;

interface RegionWithCodeForShippingApis
{
    public function getCodeForShippingApi(ShippingCarrierEnum $shippingCarrier): string;
}
