<?php

namespace App\Http\Controllers;

use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\Managers\AddressValidationManager;
use App\Support\GetUpsAccessToken;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function validateAddress(
        GetUpsAccessToken $getUpsAccessToken,
        AddressValidationManager $addressValidationManager,
    ) {
        // Use CA address since that's the only one that works in UPS testing env.
        $exampleAddress = new ShippingAddressDto(
            '1315 10th St',
            'Sacramento',
            \App\ShipShopperLibrary\Enums\UsaStatesEnum::CA,
            '95814',
            \App\ShipShopperLibrary\Enums\RegionsEnum::US,
        );
        $addressValidationManager->loadAddress($exampleAddress);
        $addressValidationManager->checkUps($getUpsAccessToken->getToken());
        $addressValidationManager->validate();
    }
}
