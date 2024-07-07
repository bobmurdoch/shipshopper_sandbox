<?php
namespace App\ShipShopperLibrary\Support;

use App\ShipShopperLibrary\DTOs\UpsTokenResponseDTO;
use App\ShipShopperLibrary\Exceptions\UpsAuthTokenException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

class GetUpsOauthToken
{
    public static function getToken(): UpsTokenResponseDTO
    {
        $url = config('shipshopper.carriers.ups.sandbox')
            ?
            'https://wwwcie.ups.com/security/v1/oauth/token'
            :
            'https://onlinetools.ups.com/security/v1/oauth/token';
        try {
            $response = Http::asForm()
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Basic ' . base64_encode(
                        config('shipshopper.carriers.ups.api_credentials.client_id')
                            . ':' . config('shipshopper.carriers.ups.api_credentials.client_secret')
                    )
                ])->post($url, [
                    'grant_type' => 'client_credentials',
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $timeoutException) {
            throw new UpsAuthTokenException(__('shipshopper.ups.token.timeout'));
        }
        if ($response->ok() === false) {
            $exception = new UpsAuthTokenException(__('shipshopper.ups.token.http_error', [
                'http_status'=>$response->status(),
            ]));
            $exception->addHttpResponseBody($response->body());
            $exception->addHttpResponseCode($response->status());
            throw $exception;
        }
        $issuedAt = CarbonImmutable::createFromTimestampMs($response->json('issued_at'), date_default_timezone_get());
        return new UpsTokenResponseDTO(
            (string)$response->json('token_type'),
            $issuedAt,
            (string)$response->json('client_id'),
            (string)$response->json('access_token'),
            $issuedAt->addSeconds((int)$response->json('expires_in')),
            (string)$response->json('status'),
        );
    }
}
