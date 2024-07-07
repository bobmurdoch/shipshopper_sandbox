<?php
namespace App\ShipShopperLibrary\Enums;

interface AdministrativeAreaWithCodeForShippingApis
{
    public function getCodeForShippingApi(ShippingCarrierEnum $shippingCarrier): string;
}
