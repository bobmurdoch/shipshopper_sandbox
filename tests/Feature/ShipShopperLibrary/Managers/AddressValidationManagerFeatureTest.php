<?php

namespace Tests\Feature\ShipShopperLibrary\Managers;

use App\ShipShopperLibrary\DTOs\AddressValidationResponseDTO;
use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\Enums\RegionsEnum;
use App\ShipShopperLibrary\Enums\ShippingAddressClassificationTypeEnum;
use App\ShipShopperLibrary\Enums\UsaStatesEnum;
use App\ShipShopperLibrary\Managers\AddressValidationManager;
use App\ShipShopperLibrary\Providers\Requests\AddressValidation\UpsAddressValidationRequestProvider;
use App\ShipShopperLibrary\Providers\Responses\AddressValidation\UpsAddressValidationResponseProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use Tests\TestCase;

#[CoversClass(\App\ShipShopperLibrary\Managers\AddressValidationManager::class)]
#[CoversFunction('loadAddress')]
#[CoversFunction('checkUps')]
#[CoversFunction('validate')]
#[CoversFunction('getUpsResponse')]
class AddressValidationManagerFeatureTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function testUpsSkipped(): void
    {
        Http::fake();
        $addressDto = new ShippingAddressDto(
            address_line_1: '123 My St',
            locality: 'Beverly Hills',
            administrativeArea: UsaStatesEnum::CA,
            postalCode: '90210',
            region: RegionsEnum::US,
        );
        $upsRequestProviderMock = \Mockery::mock(UpsAddressValidationRequestProvider::class);
        $upsRequestProviderMock->shouldNotReceive('getUrl');
        $upsRequestProviderMock->shouldNotReceive('getRequestData');
        $upsRequestProviderMock->shouldNotReceive('getHeaders');
        $this->app->bind(UpsAddressValidationRequestProvider::class, function () use ($upsRequestProviderMock) {
            return $upsRequestProviderMock;
        });
        $manager = new AddressValidationManager();
        $manager->loadAddress($addressDto);
        $manager->validate();
        $this->assertNull($manager->getUpsResponse());
    }
    public function testUps(): void
    {
        Http::fake([
            'http://fake.ups.com' => Http::response(json_encode(['some shaped request data']), 200),
        ]);
        $addressDto = new ShippingAddressDto(
            address_line_1: '123 My St',
            locality: 'Beverly Hills',
            administrativeArea: UsaStatesEnum::CA,
            postalCode: '90210',
            region: RegionsEnum::US,
        );
        $upsRequestProviderMock = \Mockery::mock(UpsAddressValidationRequestProvider::class);
        $upsRequestProviderMock->shouldReceive('getUrl')->andReturn('http://fake.ups.com');
        $upsRequestProviderMock->shouldReceive('getRequestData')->andReturn(['some raw data']);
        $upsRequestProviderMock->shouldReceive('getHeaders')->andReturn(['x-custom'=>'headers']);
        $this->app->bind(UpsAddressValidationRequestProvider::class, function () use ($upsRequestProviderMock) {
            return $upsRequestProviderMock;
        });
        $upsResponseProviderMock = \Mockery::mock(UpsAddressValidationResponseProvider::class);
        $upsValidationResponseMock = new AddressValidationResponseDTO(true, [], ShippingAddressClassificationTypeEnum::COMMERCIAL);
        $upsResponseProviderMock->shouldReceive('getResponseDTO')
            ->withArgs([['some shaped request data']])
            ->andReturn($upsValidationResponseMock);
        $this->instance(UpsAddressValidationResponseProvider::class, $upsResponseProviderMock);
        $manager = new AddressValidationManager();
        $manager->loadAddress($addressDto);
        $manager->checkUps('ups-token');
        $manager->validate();
        $actualAddressValidationResponseDTO = $manager->getUpsResponse();
        $this->assertSame(ShippingAddressClassificationTypeEnum::COMMERCIAL, $actualAddressValidationResponseDTO->addressType);
        $this->assertSame([], $actualAddressValidationResponseDTO->addressCandidates);
        $this->assertSame(true, $actualAddressValidationResponseDTO->validated);
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://fake.ups.com'
                && $request->data() === ['some raw data']
                && $request->method() === 'POST'
                && $request->headers()['x-custom'] === ['headers'];
        });
    }
}
