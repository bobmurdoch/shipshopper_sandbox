<?php
namespace App\Enums;

interface AdministrativeAreaWithCodeForShippingApis
{
    public function getCodeForShippingApi(ShippingCarrierEnum $shippingCarrier): string;
}
