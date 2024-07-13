<?php
namespace App\ShipShopperLibrary\Managers;

use App\ShipShopperLibrary\DTOs\AddressValidationResponseDTO;
use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\Providers\Requests\AddressValidation\FedexAddressValidationRequestProvider;
use App\ShipShopperLibrary\Providers\Requests\AddressValidation\UpsAddressValidationRequestProvider;
use App\ShipShopperLibrary\Providers\Responses\AddressValidation\FedexAddressValidationResponseProvider;
use App\ShipShopperLibrary\Providers\Responses\AddressValidation\UpsAddressValidationResponseProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class AddressValidationManager
{
    private ?ShippingAddressDto $shippingAddress = null;
    private bool $checkUps = false;
    private bool $checkFedex = false;
    private ?string $upsToken = null;
    private ?string $fedexToken = null;
    private ?AddressValidationResponseDTO $upsResponse = null;
    private ?AddressValidationResponseDTO $fedexResponse = null;
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
    public function checkFedex(string $fedexToken): void
    {
        $this->checkFedex = true;
        $this->fedexToken = $fedexToken;
    }
    public function validate(): void
    {
        $upsProvider = null;
        if ($this->checkUps === true) {
            $upsProvider = resolve(UpsAddressValidationRequestProvider::class, [
                'token' => $this->upsToken,
                'sandboxMode' => config('shipshopper.carriers.ups.sandbox'),
                'shippingAddress' => $this->shippingAddress,
            ]);
        }
        $fedexProvider = null;
        if ($this->checkFedex === true) {
            $fedexProvider = resolve(FedexAddressValidationRequestProvider::class, [
                'token' => $this->fedexToken,
                'sandboxMode' => config('shipshopper.carriers.fedex.sandbox'),
                'shippingAddress' => $this->shippingAddress,
            ]);
        }
        // build other providers
        // usps coming soon
        // ...
        // make temp vars for closure
        $useUps = $this->checkUps;
        $useFedex = $this->checkFedex;

        $responses = Http::pool(function (Pool $pool) use (
            $useUps,
            $upsProvider,
            $useFedex,
            $fedexProvider,
        ) {
            $poolArray = [];
            if ($useUps === true) {
                $poolArray[] = $pool
                    ->as(\App\ShipShopperLibrary\Enums\ShippingCarrierEnum::UPS->name)
                    ->withHeaders($upsProvider->getHeaders())
                    ->post($upsProvider->getUrl(), $upsProvider->getRequestData());
            }
            if ($useFedex === true) {
                $poolArray[] = $pool
                    ->as(\App\ShipShopperLibrary\Enums\ShippingCarrierEnum::FEDEX->name)
                    ->withHeaders($fedexProvider->getHeaders())
                    ->post($fedexProvider->getUrl(), $fedexProvider->getRequestData());
            }
            return $poolArray;
        });
        if ($useUps === true) {
            $upsRawResponse = $responses[\App\ShipShopperLibrary\Enums\ShippingCarrierEnum::UPS->name];
            $this->upsResponse = resolve(
                UpsAddressValidationResponseProvider::class
            )::getResponseDTO($upsRawResponse->json(), $upsRawResponse->status());
        }
        if ($useFedex === true) {
            /**
             * @var Response $fedexRawResponse
             */
            $fedexRawResponse = $responses[\App\ShipShopperLibrary\Enums\ShippingCarrierEnum::FEDEX->name];
            $this->fedexResponse = resolve(
                FedexAddressValidationResponseProvider::class
            )::getResponseDTO($fedexRawResponse->json(), $fedexRawResponse->status());
        }
        // check usps responses.
    }
    public function getUpsResponse(): ?AddressValidationResponseDTO
    {
        return $this->upsResponse;
    }
    public function getFedexResponse(): ?AddressValidationResponseDTO
    {
        return $this->fedexResponse;
    }
}
