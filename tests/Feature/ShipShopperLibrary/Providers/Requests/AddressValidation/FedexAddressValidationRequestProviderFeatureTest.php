<?php

namespace Tests\Feature\ShipShopperLibrary\Providers\Requests\AddressValidation;

use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\Enums\RegionsEnum;
use App\ShipShopperLibrary\Enums\ShippingCarrierEnum;
use App\ShipShopperLibrary\Enums\UsaStatesEnum;
use App\ShipShopperLibrary\Providers\Requests\AddressValidation\FedexAddressValidationRequestProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use Tests\TestCase;

#[CoversClass(\App\ShipShopperLibrary\Providers\Requests\AddressValidation\FedexAddressValidationRequestProvider::class)]
#[CoversFunction('getUrl')]
#[CoversFunction('getRequestData')]
#[CoversFunction('getHeaders')]
class FedexAddressValidationRequestProviderFeatureTest extends TestCase
{
    public function testSandbox(): void
    {
        $dto = new ShippingAddressDto(
            address_line_1: 'add line 1',
            locality: 'Hollywood',
            administrativeArea: UsaStatesEnum::CA,
            postalCode: '90210',
            region: RegionsEnum::US,
            address_line_2: 'add line 2',
            urbanization: 'urban',
        );
        $provider = new FedexAddressValidationRequestProvider(
            shippingAddress: $dto,
            sandboxMode: true,
            token: 'fedex-token',
        );
        $this->assertSame('https://apis-sandbox.fedex.com/address/v1/addresses/resolve', $provider->getUrl());
        $this->assertSame([
            'addressesToValidate' => [
                [
                    'address'=>[
                        'streetLines'=>[
                            'add line 1',
                            'add line 2',
                        ],
                        'city'=>'Hollywood',
                        'stateOrProvinceCode'=>'CA',
                        'postalCode'=>'90210',
                        'countryCode'=>'US',
                    ],
                ],
            ],
        ], $provider->getRequestData());
        $this->assertSame([
            'Authorization'=>'Bearer fedex-token',
            'X-locale'=>'en_US',
            'Content-Type'=>'application/json',
        ], $provider->getHeaders());
    }

    public function testProduction(): void
    {
        $dto = new ShippingAddressDto(
            address_line_1: 'add line 1',
            locality: 'Hollywood',
            administrativeArea: UsaStatesEnum::CA,
            postalCode: '90210',
            region: RegionsEnum::US,
        );
        $provider = new FedexAddressValidationRequestProvider(
            shippingAddress: $dto,
            sandboxMode: false,
            token: 'fedex-token',
        );
        $this->assertSame('https://apis.fedex.com/address/v1/addresses/resolve', $provider->getUrl());
        $this->assertSame([
            'addressesToValidate' => [
                [
                    'address'=>[
                        'streetLines'=>[
                            'add line 1',
                            null,
                        ],
                        'city'=>'Hollywood',
                        'stateOrProvinceCode'=>'CA',
                        'postalCode'=>'90210',
                        'countryCode'=>'US',
                    ],
                ],
            ],
        ], $provider->getRequestData());
        $this->assertSame([
            'Authorization'=>'Bearer fedex-token',
            'X-locale'=>'en_US',
            'Content-Type'=>'application/json',
        ], $provider->getHeaders());
    }
}
