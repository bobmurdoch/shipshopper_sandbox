<?php
namespace App\Support;

use Illuminate\Support\Facades\Cache;

class GetUpsAccessToken
{
    public function getToken()
    {
        $cachKey = 'ups_access_token';
        $cachedTokenDTO = Cache::get($cachKey);
        if ($cachedTokenDTO !== null) {
            /* @var \App\ShipShopperLibrary\DTOs\UpsTokenResponseDTO $cachedTokenDTO */
            // Check if the token expiration time is after 2 minutes from now (2 minutes just to give some padding
            // while we perform this request). If yes, use the cached token.
            if ($cachedTokenDTO->expiresAt->gt(now()->addMinutes(2))) {
                return $cachedTokenDTO->accessToken;
            }
        }
        $freshTokenDTO = resolve(\App\ShipShopperLibrary\Support\GetUpsAccessToken::class)::getToken();
        // cache until expiration date
        Cache::put($cachKey, $freshTokenDTO, now()->addMinutes($freshTokenDTO->expiresAt->diff(now())->minutes));

        return $freshTokenDTO->accessToken;
    }
}
