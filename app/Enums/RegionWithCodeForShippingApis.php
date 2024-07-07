<?php
namespace App\Enums;

interface RegionWithCodeForShippingApis
{
    public function getCodeForShippingApi(ShippingCarrierEnum $shippingCarrier): string;
}
