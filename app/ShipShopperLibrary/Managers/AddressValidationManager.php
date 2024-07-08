<?php
namespace App\ShipShopperLibrary\Managers;

use App\ShipShopperLibrary\DTOs\AddressValidationResponseDTO;
use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\Providers\Requests\AddressValidation\UpsAddressValidationRequestProvider;
use App\ShipShopperLibrary\Providers\Responses\AddressValidation\UpsAddressValidationResponseProvider;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class AddressValidationManager
{
    private ShippingAddressDto $shippingAddress;
    private bool $checkUps = false;
    private ?string $upsToken = null;
    private ?AddressValidationResponseDTO $upsResponse = null;
    public function loadAddress(
        ShippingAddressDto $shippingAddress,
    ): void {
        $this->shippingAddress = $shippingAddress;
    }
    public function checkUps(string $upsToken): void
    {
        $this->checkUps = true;
        $this->upsToken = $upsToken;
    }
    public function validate(): void
    {
        $upsProvider = null;
        if ($this->checkUps === true) {
            $upsProvider = resolve(UpsAddressValidationRequestProvider::class, [
                'upsToken' => $this->upsToken,
                'sandboxMode' => config('shipshopper.carriers.ups.sandbox'),
                'shippingAddress' => $this->shippingAddress,
            ]);
        }
        // build other providers
        // usps coming soon
        // ...
        // fedex coming soon
        // ...
        // make temp vars for closure
        $useUps = $this->checkUps;
        if ($this->checkUps === true) {
            $responses = Http::pool(function (Pool $pool) use (
                $useUps,
                $upsProvider,
            ) {
                $poolArray = [];
                if ($useUps === true) {
                    $poolArray[] = $pool
                        ->as(\App\ShipShopperLibrary\Enums\ShippingCarrierEnum::UPS->name)
                        ->withHeaders($upsProvider->getHeaders())
                        ->post($upsProvider->getUrl(), $upsProvider->getRequestData());
                }
                return $poolArray;
            });
            if ($useUps === true) {
                $upsRawResponse = $responses[\App\ShipShopperLibrary\Enums\ShippingCarrierEnum::UPS->name];
                $this->upsResponse = resolve(
                    UpsAddressValidationResponseProvider::class
                )::getResponseDTO($upsRawResponse->json());
            }
            // check fedex and usps responses.
        }
    }
    public function getUpsResponse(): ?AddressValidationResponseDTO
    {
        return $this->upsResponse;
    }
}
