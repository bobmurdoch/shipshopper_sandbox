<?php
namespace App\ShipShopperLibrary\Providers\Responses\AddressValidation;

use App\ShipShopperLibrary\DTOs\AddressValidationCandidateDTO;
use App\ShipShopperLibrary\DTOs\AddressValidationResponseDTO;
use App\ShipShopperLibrary\Enums\ShippingAddressClassificationTypeEnum;
use Illuminate\Support\Arr;

class UpsAddressValidationResponseProvider implements AddressValidationResponseProviderInterface
{
    public static function getResponseDTO(array $responseData, int $httpStatus): AddressValidationResponseDTO
    {
        if ($httpStatus !== 200) {
            return new AddressValidationResponseDTO(
                matched: false,
                addressCandidates: [],
                addressType: ShippingAddressClassificationTypeEnum::UNKNOWN,
                errorSummary: json_encode(Arr::get($responseData, 'response.errors', [])),
            );
        }
        $responseStatus = Arr::get($responseData, 'XAVResponse.Response.ResponseStatus.Code');
        if ($responseStatus !== '1') {
            return new AddressValidationResponseDTO(
                matched: false,
                addressCandidates: [],
                addressType: ShippingAddressClassificationTypeEnum::UNKNOWN,
                // @TODO parse through this for more readable return string
                // https://developer.ups.com/api/reference#operation/AddressValidation!c=200&path=XAVResponse/Response/Alert&t=response
                errorSummary: json_encode(Arr::get($responseData, 'XAVResponse.Response.Alert'))
            );
        }
        $addressCandidates = array_map(function ($candidate) {
            $addressTypeEnum = match (Arr::get($candidate, 'AddressClassification.Code')) {
                "1"=>ShippingAddressClassificationTypeEnum::COMMERCIAL,
                "2"=>ShippingAddressClassificationTypeEnum::RESIDENTIAL,
                default=>ShippingAddressClassificationTypeEnum::UNKNOWN,
            };

            return new AddressValidationCandidateDTO(
                address_line_1: (string)Arr::get($candidate, 'AddressKeyFormat.AddressLine.0'),
                locality: (string)Arr::get($candidate, 'AddressKeyFormat.PoliticalDivision2'),
                administrativeArea: (string)Arr::get($candidate, 'AddressKeyFormat.PoliticalDivision1'),
                postalCode: (string)Arr::get($candidate, 'AddressKeyFormat.PostcodePrimaryLow'),
                region: (string)Arr::get($candidate, 'AddressKeyFormat.CountryCode'),
                addressTypeEnum: $addressTypeEnum,
                address_line_2: (string)Arr::get($candidate, 'AddressKeyFormat.AddressLine.1'),
                urbanization: (string)Arr::get($candidate, 'AddressKeyFormat.Urbanization'),
                postalCodeExtended: (string)Arr::get($candidate, 'AddressKeyFormat.PostcodeExtendedLow'),
            );
        }, Arr::get($responseData, 'XAVResponse.Candidate', []));
        $addressTypeEnum = match (Arr::get($responseData, 'XAVResponse.AddressClassification.Code')) {
            "1"=>ShippingAddressClassificationTypeEnum::COMMERCIAL,
            "2"=>ShippingAddressClassificationTypeEnum::RESIDENTIAL,
            default=>ShippingAddressClassificationTypeEnum::UNKNOWN,
        };

        return new AddressValidationResponseDTO(
            // is present as key with empty string for value
            matched: Arr::get($responseData, 'XAVResponse.ValidAddressIndicator', null) === '',
            addressCandidates: $addressCandidates,
            addressType: $addressTypeEnum,
        );
    }
}
