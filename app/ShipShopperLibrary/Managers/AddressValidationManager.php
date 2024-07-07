<?php
namespace App\ShipShopperLibrary\Managers;

use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\DTOs\UpsTokenResponseDTO;
use App\ShipShopperLibrary\Providers\Requests\AddressValidation\UpsAddressValidationRequestProvider;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class AddressValidationManager
{
    private bool $sandboxMode = false;
    private ShippingAddressDto $shippingAddress;
    private bool $checkUps = false;
    private ?string $upsToken = null;
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
                    dd($upsProvider->getRequestData());
                    $poolArray[] = $pool
                        ->as(\App\ShipShopperLibrary\Enums\ShippingCarrierEnum::UPS->name)
                        ->withHeaders($upsProvider->getHeaders())
                        ->post($upsProvider->getUrl(), $upsProvider->getRequestData());
                }
                return $poolArray;
            });
            if ($useUps === true) {
                $upsResponse = $responses[\App\ShipShopperLibrary\Enums\ShippingCarrierEnum::UPS->name];
                dd($upsResponse->json());
            }
            // check fedex and usps responses.
        }
    }
}
