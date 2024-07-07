<?php

namespace App\ShipShopperLibrary\DTOs;

use App\ShipShopperLibrary\Enums\AdministrativeAreaWithCodeForShippingApis;
use App\ShipShopperLibrary\Enums\RegionWithCodeForShippingApis;

readonly class ShippingAddressDto
{
    public function __construct(
        public string $address_line_1,
        public string $locality,
        public AdministrativeAreaWithCodeForShippingApis $administrativeArea,
        public string $postalCode,
        public RegionWithCodeForShippingApis $region,
        public ?string $address_line_2 = null,
        public ?string $urbanization = null,
    ) {
        //
    }
}
