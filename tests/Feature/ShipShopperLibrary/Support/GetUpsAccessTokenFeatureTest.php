<?php

namespace Tests\Feature\ShipShopperLibrary\Support;

use App\ShipShopperLibrary\Exceptions\UpsAuthTokenException;
use App\ShipShopperLibrary\Support\GetUpsAccessToken;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use Tests\TestCase;

#[CoversClass(\App\ShipShopperLibrary\Support\GetUpsAccessToken::class)]
#[CoversFunction('getToken')]
class GetUpsAccessTokenFeatureTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }
    public function testGetTokenTimesOut(): void
    {
        config()->set('shipshopper.carriers.ups.sandbox', true);
        config()->set('shipshopper.carriers.ups.api_credentials.client_id', 'my_id');
        config()->set('shipshopper.carriers.ups.api_credentials.client_secret', 'secret');
        Http::fake([
            'wwwcie.ups.com/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException(),
        ]);
        $this->expectException(UpsAuthTokenException::class);
        $this->expectExceptionMessage(__('shipshopper.ups.token.timeout'));
        $actualToken = resolve(GetUpsAccessToken::class)->getToken();
    }
    public function testGetTokenHttpError(): void
    {
        config()->set('shipshopper.carriers.ups.sandbox', true);
        config()->set('shipshopper.carriers.ups.api_credentials.client_id', 'my_id');
        config()->set('shipshopper.carriers.ups.api_credentials.client_secret', 'secret');
        Http::fake([
            'wwwcie.ups.com/*' => Http::response('', 500),
        ]);
        $this->expectException(UpsAuthTokenException::class);
        $this->expectExceptionMessage(__('shipshopper.ups.token.http_error', [
            'http_status'=>500,
        ]));
        $actualToken = resolve(GetUpsAccessToken::class)->getToken();
    }
    public function testGetsTokenSuccessfully(): void
    {
        config()->set('shipshopper.carriers.ups.sandbox', true);
        config()->set('shipshopper.carriers.ups.api_credentials.client_id', 'my_id');
        config()->set('shipshopper.carriers.ups.api_credentials.client_secret', 'secret');
        $fakeTime = CarbonImmutable::now();
        $this->travelTo($fakeTime);
        Http::fake([
            'wwwcie.ups.com/*' => Http::response(json_encode([
                'issued_at'=>$fakeTime->getTimestampMs(),
                'token_type'=>'a_type',
                'client_id'=>'my_id',
                'access_token'=>'secret',
                'expires_in'=>120,
                'status'=>'success',
            ])),
        ]);
        $actualToken = resolve(GetUpsAccessToken::class)->getToken();
        $this->assertSame('a_type', $actualToken->tokenType);
        $this->assertSame('my_id', $actualToken->clientId);
        $this->assertSame('secret', $actualToken->accessToken);
        $this->assertTrue($actualToken->issuedAt->is($fakeTime));
        $this->assertTrue($actualToken->expiresAt->is($fakeTime->addSeconds(120)));
        Http::assertSent(function (Request $request) {
            $this->assertSame('https://wwwcie.ups.com/security/v1/oauth/token', $request->url());
            $this->assertSame(['grant_type' => 'client_credentials'], $request->data());
            $this->assertSame('POST', $request->method());
            $this->assertSame('Basic ' . base64_encode('my_id:secret'), $request->header('Authorization')[0]);
            return true;
        });
    }
}
