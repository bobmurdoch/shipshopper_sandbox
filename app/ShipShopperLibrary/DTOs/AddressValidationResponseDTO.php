<?php
namespace App\ShipShopperLibrary\DTOs;

use App\ShipShopperLibrary\Enums\ShippingAddressClassificationTypeEnum;

readonly class AddressValidationResponseDTO
{
    public function __construct(
        public bool $validated,
        /**
         * @var AddressValidationCandidateDTO[] $addressCandidates
         */
        public array $addressCandidates,
        public ShippingAddressClassificationTypeEnum $addressType,
        public ?string $errorSummary = null,
    ) {
        //
    }
    public function hasErrors(): bool
    {
        return $this->errorSummary !== null;
    }
}
