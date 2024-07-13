<?php
namespace App\ShipShopperLibrary\Support;

use App\ShipShopperLibrary\DTOs\FedexTokenResponseDTO;
use App\ShipShopperLibrary\Exceptions\FedexAuthTokenException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

class GetFedexAccessToken
{
    public static function getToken(): FedexTokenResponseDTO
    {
        // FedEx doesn't return an issued at time, so use start of our request as that time.
        $issuedAt = CarbonImmutable::now();
        $url = config('shipshopper.carriers.fedex.sandbox')
            ?
            'https://apis-sandbox.fedex.com/oauth/token'
            :
            'https://apis.fedex.com/oauth/token';
        try {
            $response = Http::asForm()
                ->acceptJson()
                ->post($url, [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('shipshopper.carriers.fedex.api_credentials.client_id'),
                    'client_secret' => config('shipshopper.carriers.fedex.api_credentials.client_secret'),
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $timeoutException) {
            throw new FedexAuthTokenException(__('shipshopper.fedex.token.timeout'));
        }
        if ($response->ok() === false) {
            $exception = new FedexAuthTokenException(__('shipshopper.fedex.token.http_error', [
                'http_status'=>$response->status(),
            ]));
            $exception->addHttpResponseBody($response->body());
            $exception->addHttpResponseCode($response->status());
            throw $exception;
        }
        return new FedexTokenResponseDTO(
            (string)$response->json('token_type'),
            $issuedAt,
            (string)$response->json('access_token'),
            $issuedAt->addSeconds((int)$response->json('expires_in')),
        );
    }
}
