<?php
namespace App\ShipShopperLibrary\Providers\Requests\AddressValidation;

use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\Enums\ShippingCarrierEnum;

class FedexAddressValidationRequestProvider implements AddressValidationRequestProviderInterface
{
    public function __construct(
        private readonly ShippingAddressDto $shippingAddress,
        private readonly bool $sandboxMode,
        private readonly string $token,
    ) {
        //
    }
    public function getUrl(): string
    {
        return $this->sandboxMode === true
            ?
            'https://apis-sandbox.fedex.com/address/v1/addresses/resolve'
            :
            'https://apis.fedex.com/address/v1/addresses/resolve';
    }

    public function getRequestData(): array
    {
        return [
            'addressesToValidate' => [
                [
                    'address'=>[
                        'streetLines'=>[
                            $this->shippingAddress->address_line_1,
                            $this->shippingAddress->address_line_2,
                        ],
                        'city'=>$this->shippingAddress->locality,
                        'stateOrProvinceCode'=>$this->shippingAddress->administrativeArea->getCodeForShippingApi(ShippingCarrierEnum::FEDEX),
                        'postalCode'=>$this->shippingAddress->postalCode,
                        'countryCode'=>$this->shippingAddress->region->getCodeForShippingApi(ShippingCarrierEnum::FEDEX),
                    ],
                ],
            ],
        ];
    }

    public function getHeaders(): array
    {
        return [
            'Authorization'=>'Bearer '.$this->token,
            'X-locale'=>'en_US',
            'Content-Type'=>'application/json',
        ];
    }
}
