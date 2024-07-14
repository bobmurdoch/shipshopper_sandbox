<?php

namespace App\Http\Controllers;

use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\Exceptions\FedexAuthTokenException;
use App\ShipShopperLibrary\Exceptions\UpsAuthTokenException;
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
                    // only allow 5 digit zip codes for start of demo so that we can demonstrate
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
        $address = new ShippingAddressDto(
            $request->input('address'),
            $request->input('city'),
            \App\ShipShopperLibrary\Enums\UsaStatesEnum::{$request->input('state')},
            $request->input('zip'),
            \App\ShipShopperLibrary\Enums\RegionsEnum::{$request->input('country')},
        );
        $addressValidationManager->loadAddress($address);
        $useUps = $request->boolean('useUps');
        if ($useUps) {
            try {
                $addressValidationManager->checkUps($getUpsAccessToken->getToken());
            } catch (UpsAuthTokenException $e) {
                return response()->json(['error'=>'Unable to fetch UPS Auth Token']);
            }
        }
        $useFedex = $request->boolean('useFedex');
        if ($useFedex) {
            try {
                $addressValidationManager->checkFedex($getFedexAccessToken->getToken());
            } catch (FedexAuthTokenException $e) {
                return response()->json(['error'=>'Unable to fetch Fedex Auth Token']);
            }
        }
        $addressValidationManager->validate();
        $response = [];
        $upsResponse = $addressValidationManager->getUpsResponse();
        if ($useUps && $upsResponse && $upsResponse->hasErrors()) {
            $response['ups'] = [
                'matched'=>$upsResponse?->matched === true,
                'type'=>$upsResponse?->addressType,
                'candidateCount'=>0,
                'error'=>$upsResponse->errorSummary,
            ];
        } elseif ($useUps && $upsResponse) {
            $response['ups'] = [
                'matched'=>$upsResponse?->matched === true,
                'type'=>$upsResponse?->addressType,
                'candidateCount'=>count($upsResponse?->addressCandidates),
            ];
        }
        $fedexResponse = $addressValidationManager->getFedexResponse();
        if ($useFedex && $fedexResponse && $fedexResponse->hasErrors()) {
            $response['fedex'] = [
                'matched'=>$fedexResponse?->matched === true,
                'type'=>$fedexResponse?->addressType,
                'candidateCount'=>0,
                'error'=>$fedexResponse->errorSummary,
            ];
        } elseif ($useFedex && $fedexResponse) {
            $response['fedex'] = [
                'matched'=>$fedexResponse->matched === true,
                'type'=>$fedexResponse->addressType,
                'candidateCount'=>count($fedexResponse?->addressCandidates),
            ];
        }
        return response()->json($response);
    }
}
