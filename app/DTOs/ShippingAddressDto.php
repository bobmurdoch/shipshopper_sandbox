<?php

namespace App\DTOs;

use App\Enums\AdministrativeAreaWithCodeForShippingApis;
use App\Enums\RegionWithCodeForShippingApis;

readonly class ShippingAddressDto
{
    public function __construct(
        public string $address_line_1,
        public string $locality,
        public AdministrativeAreaWithCodeForShippingApis $administrativeArea,
        public string $postalCode,
        public RegionWithCodeForShippingApis $region,
        public ?string $address_line_2 = null,
    ) {
        //
    }
}
