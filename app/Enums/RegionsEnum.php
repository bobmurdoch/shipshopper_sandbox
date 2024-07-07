<?php

namespace App\Enums;

enum RegionsEnum: string implements RegionWithCodeForShippingApis
{
    case US = 'USA';
    public function getAdministrativeAreasEnum()
    {
        return match ($this) {
            self::US => \App\Enums\UsaStatesEnum::class,
        };
    }
    public function getCodeForShippingApi(ShippingCarrierEnum $shippingCarrier): string
    {
        /*
        * For now these carriers all use the same format for the 'country' but
        * add a method here for future carriers that have a different format.
        */
        return match ($shippingCarrier) {
            ShippingCarrierEnum::UPS, ShippingCarrierEnum::FEDEX, ShippingCarrierEnum::USPS => $this->name,
        };
    }
}
