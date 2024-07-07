<?php
namespace App\ShipShopperLibrary\Providers\Requests\AddressValidation;

interface AddressValidationRequestProviderInterface
{
    public function getUrl(): string;
    public function getRequestData(): array;
    public function getHeaders(): array;
}
