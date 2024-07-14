<?php

namespace App\Http\Controllers;

use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\Managers\AddressValidationManager;
use App\Support\GetFedexAccessToken;
use App\Support\GetUpsAccessToken;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DemoController extends Controller
{
    public function create()
    {
        return view('demo', [
            'states'=>\App\ShipShopperLibrary\Enums\UsaStatesEnum::getValuesByNamesArray(),
        ]);
    }
    public function validateAddress(
        GetUpsAccessToken $getUpsAccessToken,
        GetFedexAccessToken $getFedexAccessToken,
        AddressValidationManager $addressValidationManager,
        Request $request,
    ) {
        $request->validate(
            rules: [
                'address'=>[
                    'required',
                ],
                'city'=>[
                    'required',
                ],
                'state'=>[
                    'required',
                    // can't use laravel enum rule since form values are names not values of our enums
                    Rule::in(collect(\App\ShipShopperLibrary\Enums\UsaStatesEnum::cases())->pluck('name')->toArray()),
                ],
                'zip'=>[
                    'required',
                    // only allow 5 digit standard zips for start of demo so we can demonstrate
                    // some form validation
                    'regex:/^\d{5}$/',
                ],
                'country'=>[
                    'required',
                    // can't use laravel enum rule since form values are names not values of our enums
                    Rule::in(collect(\App\ShipShopperLibrary\Enums\RegionsEnum::cases())->pluck('name')->toArray()),
                ],
            ],
            messages: [
                'state.*'=>'Please select a US State',
                'zip.*'=>'Please enter a 5 digit numerical zip code',
                'country.*'=>'Please select a valid country',
            ],
        );
        // testing validation
        return response('we passed', 200);
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
        $addressValidationManager->checkFedex($getFedexAccessToken->getToken());
        $addressValidationManager->validate();
        dd(
            $addressValidationManager->getUpsResponse(),
            $addressValidationManager->getFedexResponse(),
        );
    }
}
