<?php
namespace App\ShipShopperLibrary\DTOs;

use App\ShipShopperLibrary\Enums\ShippingAddressClassificationTypeEnum;

readonly class AddressValidationCandidateDTO
{
    public function __construct(
        public string $address_line_1,
        public string $locality,
        public string $administrativeArea,
        public string $postalCode,
        public string $region,
        public ShippingAddressClassificationTypeEnum $addressTypeEnum,
        public ?string $address_line_2 = null,
        public ?string $urbanization = null,
        public ?string $postalCodeExtended = null,
    ) {
        //
    }
}
