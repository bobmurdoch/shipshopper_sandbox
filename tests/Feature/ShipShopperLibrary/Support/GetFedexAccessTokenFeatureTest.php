<?php

namespace Tests\Feature\ShipShopperLibrary\Support;

use App\ShipShopperLibrary\Exceptions\FedexAuthTokenException;
use App\ShipShopperLibrary\Support\GetFedexAccessToken;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use Tests\TestCase;

#[CoversClass(\App\ShipShopperLibrary\Support\GetFedexAccessToken::class)]
#[CoversFunction('getToken')]
class GetFedexAccessTokenFeatureTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }
    public function testGetTokenTimesOut(): void
    {
        config()->set('shipshopper.carriers.fedex.sandbox', true);
        config()->set('shipshopper.carriers.fedex.api_credentials.client_id', 'my_id');
        config()->set('shipshopper.carriers.fedex.api_credentials.client_secret', 'secret');
        Http::fake([
            'apis-sandbox.fedex.com/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException(),
        ]);
        $this->expectException(FedexAuthTokenException::class);
        $this->expectExceptionMessage(__('shipshopper.fedex.token.timeout'));
        $actualToken = resolve(GetFedexAccessToken::class)->getToken();
    }
    public function testGetTokenHttpError(): void
    {
        config()->set('shipshopper.carriers.fedex.sandbox', true);
        config()->set('shipshopper.carriers.fedex.api_credentials.client_id', 'my_id');
        config()->set('shipshopper.carriers.fedex.api_credentials.client_secret', 'secret');
        Http::fake([
            'apis-sandbox.fedex.com/*' => Http::response('', 500),
        ]);
        $this->expectException(FedexAuthTokenException::class);
        $this->expectExceptionMessage(__('shipshopper.fedex.token.http_error', [
            'http_status'=>500,
        ]));
        $actualToken = resolve(GetFedexAccessToken::class)->getToken();
    }
    public function testGetsTokenSuccessfully(): void
    {
        config()->set('shipshopper.carriers.fedex.sandbox', true);
        config()->set('shipshopper.carriers.fedex.api_credentials.client_id', 'my_id');
        config()->set('shipshopper.carriers.fedex.api_credentials.client_secret', 'secret');
        $fakeTime = CarbonImmutable::now();
        $this->travelTo($fakeTime);
        Http::fake([
            'apis-sandbox.fedex.com/*' => Http::response(json_encode([
                'issued_at'=>$fakeTime->getTimestampMs(),
                'token_type'=>'a_type',
                'access_token'=>'secret',
                'expires_in'=>120,
            ])),
        ]);
        $actualToken = resolve(GetFedexAccessToken::class)->getToken();
        $this->assertSame('a_type', $actualToken->tokenType);
        $this->assertSame('secret', $actualToken->accessToken);
        $this->assertTrue($actualToken->issuedAt->is($fakeTime));
        $this->assertTrue($actualToken->expiresAt->is($fakeTime->addSeconds(120)));
        Http::assertSent(function (Request $request) {
            $this->assertSame('https://apis-sandbox.fedex.com/oauth/token', $request->url());
            $this->assertSame([
                'grant_type' => 'client_credentials',
                'client_id' => 'my_id',
                'client_secret' => 'secret',
            ], $request->data());
            $this->assertSame('POST', $request->method());
            return true;
        });
    }
}
