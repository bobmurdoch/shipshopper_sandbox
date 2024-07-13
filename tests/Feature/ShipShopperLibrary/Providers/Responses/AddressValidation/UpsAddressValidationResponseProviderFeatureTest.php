<?php

namespace Tests\Feature\ShipShopperLibrary\Providers\Responses\AddressValidation;

use App\ShipShopperLibrary\Enums\ShippingAddressClassificationTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use Tests\TestCase;

#[CoversClass(\App\ShipShopperLibrary\Providers\Responses\AddressValidation\UpsAddressValidationResponseProvider::class)]
#[CoversFunction('getResponseDTO')]
class UpsAddressValidationResponseProviderFeatureTest extends TestCase
{
    public function testResponseNoErrorsCommercial(): void
    {
        $responseData = [
            'XAVResponse'=>[
                'ValidAddressIndicator'=>'',
                'Response'=>[
                    'ResponseStatus'=>[
                        'Code'=>'1',
                    ]
                ],
                'AddressClassification'=>[
                    'Code'=>'1',
                ],
                'Candidate'=>[
                    [
                        'AddressClassification'=>[
                            'Code'=>'1',
                        ],
                        'AddressKeyFormat'=>[
                            'AddressLine'=>[
                                '123 My St',
                                'Apt C',
                            ],
                            'PoliticalDivision1'=>'CA',
                            'PoliticalDivision2'=>'Beverly Hills',
                            'PostcodePrimaryLow'=>'90210',
                            'CountryCode'=>'US',
                            'Urbanization'=>'',
                            'PostcodeExtendedLow'=>'4324',
                        ],
                    ],
                ],
            ],
        ];
        $actualDTO = \App\ShipShopperLibrary\Providers\Responses\AddressValidation\UpsAddressValidationResponseProvider::getResponseDTO($responseData, 200);
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
        $this->assertSame('', $actualDTO->addressCandidates[0]->urbanization);
        $this->assertSame('90210', $actualDTO->addressCandidates[0]->postalCode);
        $this->assertSame('4324', $actualDTO->addressCandidates[0]->postalCodeExtended);
    }
    public function testResponseNoErrorsResidential(): void
    {
        $responseData = [
            'XAVResponse'=>[
                'ValidAddressIndicator'=>'',
                'Response'=>[
                    'ResponseStatus'=>[
                        'Code'=>'1',
                    ]
                ],
                'AddressClassification'=>[
                    'Code'=>'2',
                ],
                'Candidate'=>[
                    [
                        'AddressClassification'=>[
                            'Code'=>'0',
                        ],
                        'AddressKeyFormat'=>[
                            'AddressLine'=>[
                                '123 My St',
                                '',
                            ],
                            'PoliticalDivision1'=>'CA',
                            'PoliticalDivision2'=>'Beverly Hills',
                            'PostcodePrimaryLow'=>'90210',
                            'CountryCode'=>'US',
                            'Urbanization'=>'urban',
                            'PostcodeExtendedLow'=>'',
                        ],
                    ],
                ],
            ],
        ];
        $actualDTO = \App\ShipShopperLibrary\Providers\Responses\AddressValidation\UpsAddressValidationResponseProvider::getResponseDTO($responseData, 200);
        $this->assertSame(true, $actualDTO->matched);
        $this->assertSame(null, $actualDTO->errorSummary);
        $this->assertSame(false, $actualDTO->hasErrors());
        $this->assertSame(ShippingAddressClassificationTypeEnum::RESIDENTIAL, $actualDTO->addressType);
        $this->assertSame(1, count($actualDTO->addressCandidates));
        $this->assertSame(ShippingAddressClassificationTypeEnum::UNKNOWN, $actualDTO->addressCandidates[0]->addressTypeEnum);
        $this->assertSame('123 My St', $actualDTO->addressCandidates[0]->address_line_1);
        $this->assertSame('', $actualDTO->addressCandidates[0]->address_line_2);
        $this->assertSame('Beverly Hills', $actualDTO->addressCandidates[0]->locality);
        $this->assertSame('CA', $actualDTO->addressCandidates[0]->administrativeArea);
        $this->assertSame('US', $actualDTO->addressCandidates[0]->region);
        $this->assertSame('urban', $actualDTO->addressCandidates[0]->urbanization);
        $this->assertSame('90210', $actualDTO->addressCandidates[0]->postalCode);
        $this->assertSame('', $actualDTO->addressCandidates[0]->postalCodeExtended);
    }
    public function testResponseNoErrorsUnknown(): void
    {
        $responseData = [
            'XAVResponse'=>[
                'ValidAddressIndicator'=>'',
                'Response'=>[
                    'ResponseStatus'=>[
                        'Code'=>'1',
                    ]
                ],
                'AddressClassification'=>[
                    'Code'=>'0',
                ],
                'Candidate'=>[
                    [
                        'AddressClassification'=>[
                            'Code'=>'2',
                        ],
                        'AddressKeyFormat'=>[
                            'AddressLine'=>[
                                '123 My St',
                                '',
                            ],
                            'PoliticalDivision1'=>'CA',
                            'PoliticalDivision2'=>'Beverly Hills',
                            'PostcodePrimaryLow'=>'90210',
                            'CountryCode'=>'US',
                            'Urbanization'=>'urban',
                            'PostcodeExtendedLow'=>'',
                        ],
                    ],
                ],
            ],
        ];
        $actualDTO = \App\ShipShopperLibrary\Providers\Responses\AddressValidation\UpsAddressValidationResponseProvider::getResponseDTO($responseData, 200);
        $this->assertSame(true, $actualDTO->matched);
        $this->assertSame(null, $actualDTO->errorSummary);
        $this->assertSame(false, $actualDTO->hasErrors());
        $this->assertSame(ShippingAddressClassificationTypeEnum::UNKNOWN, $actualDTO->addressType);
        $this->assertSame(1, count($actualDTO->addressCandidates));
        $this->assertSame(ShippingAddressClassificationTypeEnum::RESIDENTIAL, $actualDTO->addressCandidates[0]->addressTypeEnum);
        $this->assertSame('123 My St', $actualDTO->addressCandidates[0]->address_line_1);
        $this->assertSame('', $actualDTO->addressCandidates[0]->address_line_2);
        $this->assertSame('Beverly Hills', $actualDTO->addressCandidates[0]->locality);
        $this->assertSame('CA', $actualDTO->addressCandidates[0]->administrativeArea);
        $this->assertSame('US', $actualDTO->addressCandidates[0]->region);
        $this->assertSame('urban', $actualDTO->addressCandidates[0]->urbanization);
        $this->assertSame('90210', $actualDTO->addressCandidates[0]->postalCode);
        $this->assertSame('', $actualDTO->addressCandidates[0]->postalCodeExtended);
    }
    public function testResponseHasErrors(): void
    {
        $responseData = [
            'XAVResponse'=>[
                'Response'=>[
                    'Alert'=>'Some error details',
                    'ResponseStatus'=>[
                        'Code'=>'0',
                    ]
                ],
                'AddressClassification'=>[
                    'Code'=>'0',
                ],
                'Candidate'=>[
                    [
                        'AddressClassification'=>[
                            'Code'=>'0',
                        ],
                        'AddressKeyFormat'=>[
                            'AddressLine'=>[
                                '123 My St',
                                'Apt C',
                            ],
                            'PoliticalDivision1'=>'CA',
                            'PoliticalDivision2'=>'Beverly Hills',
                            'PostcodePrimaryLow'=>'90210',
                            'CountryCode'=>'US',
                            'Urbanization'=>'urban',
                            'PostcodeExtendedLow'=>'',
                        ],
                    ],
                ],
            ],
        ];
        $actualDTO = \App\ShipShopperLibrary\Providers\Responses\AddressValidation\UpsAddressValidationResponseProvider::getResponseDTO($responseData, 500);
        $this->assertSame(false, $actualDTO->matched);
        $this->assertSame(json_encode('Some error details'), $actualDTO->errorSummary);
        $this->assertSame(true, $actualDTO->hasErrors());
        $this->assertSame(ShippingAddressClassificationTypeEnum::UNKNOWN, $actualDTO->addressType);
        $this->assertSame(0, count($actualDTO->addressCandidates));
    }
}
