<?php
namespace App\ShipShopperLibrary\Providers\Responses\AddressValidation;

use App\ShipShopperLibrary\DTOs\AddressValidationResponseDTO;

interface AddressValidationResponseProviderInterface
{
    public static function getResponseDTO(array $responseData, int $httpStatus): AddressValidationResponseDTO;
}
