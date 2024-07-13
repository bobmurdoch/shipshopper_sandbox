<?php
namespace App\ShipShopperLibrary\Providers\Responses\AddressValidation;

use App\ShipShopperLibrary\DTOs\AddressValidationCandidateDTO;
use App\ShipShopperLibrary\DTOs\AddressValidationResponseDTO;
use App\ShipShopperLibrary\Enums\ShippingAddressClassificationTypeEnum;
use Illuminate\Support\Arr;

class FedexAddressValidationResponseProvider implements AddressValidationResponseProviderInterface
{
    public static function getResponseDTO(array $responseData, int $httpStatus): AddressValidationResponseDTO
    {
        if ($httpStatus !== 200) {
            return new AddressValidationResponseDTO(
                matched: false,
                addressCandidates: [],
                addressType: ShippingAddressClassificationTypeEnum::UNKNOWN,
                errorSummary: json_encode(Arr::get($responseData, 'errors', [])),
            );
        }
        $addressCandidates = array_map(function ($candidate) {
            $addressTypeEnum = match (Arr::get($candidate, 'classification')) {
                'BUSINESS'=>ShippingAddressClassificationTypeEnum::COMMERCIAL,
                'RESIDENTIAL'=>ShippingAddressClassificationTypeEnum::RESIDENTIAL,
                'MIXED'=>ShippingAddressClassificationTypeEnum::MIXED,
                default=>ShippingAddressClassificationTypeEnum::UNKNOWN,
            };
            $parsePostalCodePrimary = Arr::get($candidate, 'parsedPostalCode.base');
            $parsePostalCodeAddOn = Arr::get($candidate, 'parsedPostalCode.addOn');
            if (empty($parsePostalCodePrimary)) {
                // if we can't parse, fall back to full code.
                $parsePostalCodePrimary = Arr::get($candidate, 'postalCode');
                $parsePostalCodeAddOn = null;
            }
            return new AddressValidationCandidateDTO(
                address_line_1: (string)Arr::get($candidate, 'streetLinesToken.0'),
                locality: (string)Arr::get($candidate, 'city'),
                administrativeArea: (string)Arr::get($candidate, 'stateOrProvinceCode'),
                postalCode: (string)$parsePostalCodePrimary,
                region: (string)Arr::get($candidate, 'countryCode'),
                addressTypeEnum: $addressTypeEnum,
                address_line_2: (string)Arr::get($candidate, 'streetLinesToken.1'),
                urbanization: null,
                postalCodeExtended: (string)$parsePostalCodeAddOn,
            );
        }, Arr::get($responseData, 'output.resolvedAddresses', []));

        return new AddressValidationResponseDTO(
            // Fedex doesn't have a distinct field to signal this, so use existence of candidates
            // as this signal.
            matched: count($addressCandidates),
            addressCandidates: $addressCandidates,
            addressType: count($addressCandidates)
                ?
                $addressCandidates[0]->addressTypeEnum
                :
                ShippingAddressClassificationTypeEnum::UNKNOWN,
        );
    }
}
