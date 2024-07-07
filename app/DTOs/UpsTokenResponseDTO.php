<?php
namespace App\DTOs;

use Carbon\CarbonImmutable;

readonly class UpsTokenResponseDTO
{
    public function __construct(
        public string $tokenType,
        public CarbonImmutable $issuedAt,
        public string $clientId,
        public string $accessToken,
        public CarbonImmutable $expiresAt,
        public string $status,
    ) {
        //
    }
}
