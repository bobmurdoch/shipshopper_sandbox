<?php

namespace Tests\Feature\ShipShopperLibrary\Providers\Responses\AddressValidation;

use App\ShipShopperLibrary\Enums\ShippingAddressClassificationTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use Tests\TestCase;

#[CoversClass(\App\ShipShopperLibrary\Providers\Responses\AddressValidation\FedexAddressValidationResponseProvider::class)]
#[CoversFunction('getResponseDTO')]
class FedexAddressValidationResponseProviderFeatureTest extends TestCase
{
    public function testResponseNoErrorsCommercial(): void
    {
        $responseData = [
            'output'=>[
                'resolvedAddresses'=>[
                    [
                        'streetLinesToken'=>[
                            '123 My St',
                            'Apt C',
                        ],
                        'city'=>'Beverly Hills',
                        'stateOrProvinceCode'=>'CA',
                        'postalCode'=>'not used',
                        'parsedPostalCode'=>[
                            'base'=>'90210',
                            'addOn'=>'4324',
                        ],
                        'countryCode'=>'US',
                        'classification'=>'BUSINESS',
                    ],
                ],
            ],
        ];
        $actualDTO = \App\ShipShopperLibrary\Providers\Responses\AddressValidation\FedexAddressValidationResponseProvider::getResponseDTO($responseData, 200);
        $this->assertSame(true, $actualDTO->matched);
        $this->assertSame(null, $actualDTO->errorSummary);
        $this->assertSame(false, $actualDTO->hasErrors());
        $this->assertSame(ShippingAddressClassificationTypeEnum::COMMERCIAL, $actualDTO->addressType);
        $this->assertSame(1, count($actualDTO->addressCandidates));
        $this->assertSame(ShippingAddressClassificationTypeEnum::COMMERCIAL, $actualDTO->addressCandidates[0]->addressTypeEnum);
        $this->assertSame('123 My St', $actualDTO->addressCandidates[0]->address_line_1);
        $this->assertSame('Apt C', $actualDTO->addressCandidates[0]->address_line_2);
        $this->assertSame('Beverly Hills', $actualDTO->addressCandidates[0]->locality);
        $this->assertSame('CA', $actualDTO->addressCandidates[0]->administrativeArea);
        $this->assertSame('US', $actualDTO->addressCandidates[0]->region);
        $this->assertSame(null, $actualDTO->addressCandidates[0]->urbanization);
        $this->assertSame('90210', $actualDTO->addressCandidates[0]->postalCode);
        $this->assertSame('4324', $actualDTO->addressCandidates[0]->postalCodeExtended);
    }
    public function testResponseNoErrorsMixed(): void
    {
        $responseData = [
            'output'=>[
                'resolvedAddresses'=>[
                    [
                        'streetLinesToken'=>[
                            '123 My St',
                            'Apt C',
                        ],
                        'city'=>'Beverly Hills',
                        'stateOrProvinceCode'=>'CA',
                        'postalCode'=>'not used',
                        'parsedPostalCode'=>[
                            'base'=>'90210',
                            'addOn'=>'4324',
                        ],
                        'countryCode'=>'US',
                        'classification'=>'MIXED',
                    ],
                ],
            ],
        ];
        $actualDTO = \App\ShipShopperLibrary\Providers\Responses\AddressValidation\FedexAddressValidationResponseProvider::getResponseDTO($responseData, 200);
        $this->assertSame(true, $actualDTO->matched);
        $this->assertSame(null, $actualDTO->errorSummary);
        $this->assertSame(false, $actualDTO->hasErrors());
        $this->assertSame(ShippingAddressClassificationTypeEnum::MIXED, $actualDTO->addressType);
        $this->assertSame(1, count($actualDTO->addressCandidates));
        $this->assertSame(ShippingAddressClassificationTypeEnum::MIXED, $actualDTO->addressCandidates[0]->addressTypeEnum);
        $this->assertSame('123 My St', $actualDTO->addressCandidates[0]->address_line_1);
        $this->assertSame('Apt C', $actualDTO->addressCandidates[0]->address_line_2);
        $this->assertSame('Beverly Hills', $actualDTO->addressCandidates[0]->locality);
        $this->assertSame('CA', $actualDTO->addressCandidates[0]->administrativeArea);
        $this->assertSame('US', $actualDTO->addressCandidates[0]->region);
        $this->assertSame(null, $actualDTO->addressCandidates[0]->urbanization);
        $this->assertSame('90210', $actualDTO->addressCandidates[0]->postalCode);
        $this->assertSame('4324', $actualDTO->addressCandidates[0]->postalCodeExtended);
    }
    public function testResponseNoErrorsUnknown(): void
    {
        $responseData = [
            'output'=>[
                'resolvedAddresses'=>[
                    [
                        'streetLinesToken'=>[
                            '123 My St',
                            'Apt C',
                        ],
                        'city'=>'Beverly Hills',
                        'stateOrProvinceCode'=>'CA',
                        'postalCode'=>'not used',
                        'parsedPostalCode'=>[
                            'base'=>'90210',
                            'addOn'=>'4324',
                        ],
                        'countryCode'=>'US',
                        'classification'=>'UNKNOWN',
                    ],
                ],
            ],
        ];
        $actualDTO = \App\ShipShopperLibrary\Providers\Responses\AddressValidation\FedexAddressValidationResponseProvider::getResponseDTO($responseData, 200);
        $this->assertSame(true, $actualDTO->matched);
        $this->assertSame(null, $actualDTO->errorSummary);
        $this->assertSame(false, $actualDTO->hasErrors());
        $this->assertSame(ShippingAddressClassificationTypeEnum::UNKNOWN, $actualDTO->addressType);
        $this->assertSame(1, count($actualDTO->addressCandidates));
        $this->assertSame(ShippingAddressClassificationTypeEnum::UNKNOWN, $actualDTO->addressCandidates[0]->addressTypeEnum);
        $this->assertSame('123 My St', $actualDTO->addressCandidates[0]->address_line_1);
        $this->assertSame('Apt C', $actualDTO->addressCandidates[0]->address_line_2);
        $this->assertSame('Beverly Hills', $actualDTO->addressCandidates[0]->locality);
        $this->assertSame('CA', $actualDTO->addressCandidates[0]->administrativeArea);
        $this->assertSame('US', $actualDTO->addressCandidates[0]->region);
        $this->assertSame(null, $actualDTO->addressCandidates[0]->urbanization);
        $this->assertSame('90210', $actualDTO->addressCandidates[0]->postalCode);
        $this->assertSame('4324', $actualDTO->addressCandidates[0]->postalCodeExtended);
    }
    public function testResponseNoErrorsResidential(): void
    {
        $responseData = [
            'output'=>[
                'resolvedAddresses'=>[
                    [
                        'streetLinesToken'=>[
                            '123 My St',
                            'Apt C',
                        ],
                        'city'=>'Beverly Hills',
                        'stateOrProvinceCode'=>'CA',
                        'postalCode'=>'90222',
                        'parsedPostalCode'=>[
                            'base'=>'',
                            'addOn'=>'4324',
                        ],
                        'countryCode'=>'US',
                        'classification'=>'RESIDENTIAL',
                    ],
                ],
            ],
        ];
        $actualDTO = \App\ShipShopperLibrary\Providers\Responses\AddressValidation\FedexAddressValidationResponseProvider::getResponseDTO($responseData, 200);
        $this->assertSame(true, $actualDTO->matched);
        $this->assertSame(null, $actualDTO->errorSummary);
        $this->assertSame(false, $actualDTO->hasErrors());
        $this->assertSame(ShippingAddressClassificationTypeEnum::RESIDENTIAL, $actualDTO->addressType);
        $this->assertSame(1, count($actualDTO->addressCandidates));
        $this->assertSame(ShippingAddressClassificationTypeEnum::RESIDENTIAL, $actualDTO->addressCandidates[0]->addressTypeEnum);
        $this->assertSame('123 My St', $actualDTO->addressCandidates[0]->address_line_1);
        $this->assertSame('Apt C', $actualDTO->addressCandidates[0]->address_line_2);
        $this->assertSame('Beverly Hills', $actualDTO->addressCandidates[0]->locality);
        $this->assertSame('CA', $actualDTO->addressCandidates[0]->administrativeArea);
        $this->assertSame('US', $actualDTO->addressCandidates[0]->region);
        $this->assertSame(null, $actualDTO->addressCandidates[0]->urbanization);
        $this->assertSame('90222', $actualDTO->addressCandidates[0]->postalCode);
        $this->assertSame('', $actualDTO->addressCandidates[0]->postalCodeExtended);
    }
    public function testResponseHasErrors(): void
    {
        $responseData = [
            'errors'=>['some error details'],
        ];
        $actualDTO = \App\ShipShopperLibrary\Providers\Responses\AddressValidation\FedexAddressValidationResponseProvider::getResponseDTO($responseData, 500);
        $this->assertSame(false, $actualDTO->matched);
        $this->assertSame(json_encode(['some error details']), $actualDTO->errorSummary);
        $this->assertSame(true, $actualDTO->hasErrors());
        $this->assertSame(ShippingAddressClassificationTypeEnum::UNKNOWN, $actualDTO->addressType);
        $this->assertSame(0, count($actualDTO->addressCandidates));
    }
}
