<?php
namespace App\Support;

use Illuminate\Support\Facades\Cache;

class GetFedexAccessToken
{
    public function getToken()
    {
        $cachKey = 'fedex_access_token';
        $cachedTokenDTO = Cache::get($cachKey);
        if ($cachedTokenDTO !== null) {
            /* @var \App\ShipShopperLibrary\DTOs\FedexTokenResponseDTO $cachedTokenDTO */
            // Check if the token expiration time is after 2 minutes from now (2 minutes just to give some padding
            // while we perform this request). If yes, use the cached token.
            if ($cachedTokenDTO->expiresAt->gt(now()->addMinutes(2))) {
                return $cachedTokenDTO->accessToken;
            }
        }
        $freshTokenDTO = resolve(\App\ShipShopperLibrary\Support\GetFedexAccessToken::class)::getToken();
        // cache until expiration date
        Cache::put($cachKey, $freshTokenDTO, now()->addMinutes($freshTokenDTO->expiresAt->diff(now())->minutes));

        return $freshTokenDTO->accessToken;
    }
}
