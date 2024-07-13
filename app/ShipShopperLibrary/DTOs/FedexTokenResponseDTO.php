<?php
namespace App\ShipShopperLibrary\DTOs;

use Carbon\CarbonImmutable;

readonly class FedexTokenResponseDTO
{
    public function __construct(
        public string $tokenType,
        public CarbonImmutable $issuedAt,
        public string $accessToken,
        public CarbonImmutable $expiresAt,
    ) {
        //
    }
}
