<?php
namespace App\ShipShopperLibrary\Providers\Responses\AddressValidation;

use App\ShipShopperLibrary\DTOs\AddressValidationCandidateDTO;
use App\ShipShopperLibrary\Enums\ShippingAddressClassificationTypeEnum;
use Illuminate\Http\Client\Response;

interface AddressValidationResponseProviderInterface
{

    public function loadResponse(Response $response): void;
    public function hasErrors(): bool;
    public function getErrorSummary(): string;
    public function validated(): bool;
    public function addressType(): ShippingAddressClassificationTypeEnum;
    /**
     * @return AddressValidationCandidateDTO[]
     */
    public function addressCandidates(): array;
}
