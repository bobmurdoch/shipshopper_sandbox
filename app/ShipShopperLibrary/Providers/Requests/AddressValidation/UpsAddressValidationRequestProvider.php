<?php
namespace App\ShipShopperLibrary\Providers\Requests\AddressValidation;

use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\Enums\ShippingCarrierEnum;

class UpsAddressValidationRequestProvider implements AddressValidationRequestProviderInterface
{
    public function __construct(
        private readonly ShippingAddressDto $shippingAddress,
        private readonly bool $sandboxMode,
        private readonly string $upsToken,
    ) {
        //
    }
    public function getUrl(): string
    {
        return $this->sandboxMode === true
            ?
            'https://wwwcie.ups.com/api/addressvalidation/v2/3'
            :
            'https://onlinetools.ups.com/api/addressvalidation/v2/3';
    }

    public function getRequestData(): array
    {
        return [
            'XAVRequest' => [
                'AddressKeyFormat' => [
//                    'ConsigneeName' => '',
//                    'BuildingName' => '',
                    'AddressLine' => [
                        $this->shippingAddress->address_line_1,
                        $this->shippingAddress->address_line_2,
                        // line 3 skipped
                    ],
                    //'Region' => '',
                    'PoliticalDivision2' => $this->shippingAddress->locality,
                    'PoliticalDivision1' => $this->shippingAddress->administrativeArea->getCodeForShippingApi(ShippingCarrierEnum::UPS),
                    'PostcodePrimaryLow' => $this->shippingAddress->postalCode,
                    // 4 digit after postal code
                    //'PostcodeExtendedLow' => '',
                    // think this is only used for PR in US
                    'Urbanization' => $this->shippingAddress->urbanization,
                    'CountryCode' => $this->shippingAddress->region->getCodeForShippingApi(ShippingCarrierEnum::UPS),
                ],
            ],
        ];
    }

    public function getHeaders(): array
    {
        return [
            'Authorization'=>'Bearer '.$this->upsToken,
            'Content-Type'=>'application/json',
        ];
    }
}
