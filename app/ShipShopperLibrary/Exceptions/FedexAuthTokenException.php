<?php
namespace App\ShipShopperLibrary\Exceptions;

class FedexAuthTokenException extends \Exception
{
    public ?int $httpResponseCode = null;
    public ?string $httpResponseBody = null;
    public function addHttpResponseCode(int $code): void
    {
        $this->httpResponseCode = $code;
    }
    public function addHttpResponseBody(string $body): void
    {
        $this->httpResponseBody = $body;
    }
}
