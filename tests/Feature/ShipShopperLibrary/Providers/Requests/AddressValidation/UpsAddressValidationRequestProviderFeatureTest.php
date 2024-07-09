<?php

namespace Tests\Feature\ShipShopperLibrary\Providers\Requests\AddressValidation;

use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\Enums\RegionsEnum;
use App\ShipShopperLibrary\Enums\ShippingCarrierEnum;
use App\ShipShopperLibrary\Enums\UsaStatesEnum;
use App\ShipShopperLibrary\Providers\Requests\AddressValidation\UpsAddressValidationRequestProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use Tests\TestCase;

#[CoversClass(\App\ShipShopperLibrary\Providers\Requests\AddressValidation\UpsAddressValidationRequestProvider::class)]
#[CoversFunction('getUrl')]
#[CoversFunction('getRequestData')]
#[CoversFunction('getHeaders')]
class UpsAddressValidationRequestProviderFeatureTest extends TestCase
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
        $provider = new UpsAddressValidationRequestProvider(
            shippingAddress: $dto,
            sandboxMode: true,
            upsToken: 'ups-token',
        );
        $this->assertSame('https://wwwcie.ups.com/api/addressvalidation/v2/3', $provider->getUrl());
        $this->assertSame([
            'XAVRequest' => [
                'AddressKeyFormat' => [
                    'AddressLine' => [
                        'add line 1',
                        'add line 2',
                    ],
                    'PoliticalDivision2' => 'Hollywood',
                    'PoliticalDivision1' => 'CA',
                    'PostcodePrimaryLow' => '90210',
                    'Urbanization' => 'urban',
                    'CountryCode' => 'US',
                ],
            ],
        ], $provider->getRequestData());
        $this->assertSame([
            'Authorization'=>'Bearer ups-token',
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
        $provider = new UpsAddressValidationRequestProvider(
            shippingAddress: $dto,
            sandboxMode: false,
            upsToken: 'ups-token2',
        );
        $this->assertSame('https://onlinetools.ups.com/api/addressvalidation/v2/3', $provider->getUrl());
        $this->assertSame([
            'XAVRequest' => [
                'AddressKeyFormat' => [
                    'AddressLine' => [
                        'add line 1',
                        null,
                    ],
                    'PoliticalDivision2' => 'Hollywood',
                    'PoliticalDivision1' => 'CA',
                    'PostcodePrimaryLow' => '90210',
                    'Urbanization' => null,
                    'CountryCode' => 'US',
                ],
            ],
        ], $provider->getRequestData());
        $this->assertSame([
            'Authorization'=>'Bearer ups-token2',
            'Content-Type'=>'application/json',
        ], $provider->getHeaders());
    }
}
