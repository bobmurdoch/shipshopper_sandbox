<?php

namespace Tests\Feature\ShipShopperLibrary\Managers;

use App\ShipShopperLibrary\DTOs\AddressValidationResponseDTO;
use App\ShipShopperLibrary\DTOs\ShippingAddressDto;
use App\ShipShopperLibrary\Enums\RegionsEnum;
use App\ShipShopperLibrary\Enums\ShippingAddressClassificationTypeEnum;
use App\ShipShopperLibrary\Enums\UsaStatesEnum;
use App\ShipShopperLibrary\Managers\AddressValidationManager;
use App\ShipShopperLibrary\Providers\Requests\AddressValidation\FedexAddressValidationRequestProvider;
use App\ShipShopperLibrary\Providers\Requests\AddressValidation\UpsAddressValidationRequestProvider;
use App\ShipShopperLibrary\Providers\Responses\AddressValidation\FedexAddressValidationResponseProvider;
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
#[CoversFunction('checkFedex')]
#[CoversFunction('validate')]
#[CoversFunction('getUpsResponse')]
#[CoversFunction('getFedexResponse')]
class AddressValidationManagerFeatureTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function testAllSkipped(): void
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
        $fedexRequestProviderMock = \Mockery::mock(FedexAddressValidationRequestProvider::class);
        $fedexRequestProviderMock->shouldNotReceive('getUrl');
        $fedexRequestProviderMock->shouldNotReceive('getRequestData');
        $fedexRequestProviderMock->shouldNotReceive('getHeaders');
        $this->app->bind(FedexAddressValidationRequestProvider::class, function () use ($fedexRequestProviderMock) {
            return $fedexRequestProviderMock;
        });
        $manager = new AddressValidationManager();
        $manager->loadAddress($addressDto);
        $manager->validate();
        $this->assertNull($manager->getUpsResponse());
        $this->assertNull($manager->getFedexResponse());
    }
    public function testUpsOnly(): void
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
        // fedex not touched.
        $fedexRequestProviderMock = \Mockery::mock(FedexAddressValidationRequestProvider::class);
        $fedexRequestProviderMock->shouldNotReceive('getUrl');
        $fedexRequestProviderMock->shouldNotReceive('getRequestData');
        $fedexRequestProviderMock->shouldNotReceive('getHeaders');
        $this->app->bind(FedexAddressValidationRequestProvider::class, function () use ($fedexRequestProviderMock) {
            return $fedexRequestProviderMock;
        });
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
            ->withArgs([['some shaped request data'], 200])
            ->andReturn($upsValidationResponseMock);
        $this->instance(UpsAddressValidationResponseProvider::class, $upsResponseProviderMock);
        $manager = new AddressValidationManager();
        $manager->loadAddress($addressDto);
        $manager->checkUps('ups-token');
        $manager->validate();
        $actualAddressValidationResponseDTO = $manager->getUpsResponse();
        $this->assertSame(ShippingAddressClassificationTypeEnum::COMMERCIAL, $actualAddressValidationResponseDTO->addressType);
        $this->assertSame([], $actualAddressValidationResponseDTO->addressCandidates);
        $this->assertSame(true, $actualAddressValidationResponseDTO->matched);
        $this->assertNull($manager->getFedexResponse());
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://fake.ups.com'
                && $request->data() === ['some raw data']
                && $request->method() === 'POST'
                && $request->headers()['x-custom'] === ['headers'];
        });
    }
    public function testFedexOnly(): void
    {
        Http::fake([
            'http://fake.fedex.com' => Http::response(json_encode(['some shaped request data']), 200),
        ]);
        $addressDto = new ShippingAddressDto(
            address_line_1: '123 My St',
            locality: 'Beverly Hills',
            administrativeArea: UsaStatesEnum::CA,
            postalCode: '90210',
            region: RegionsEnum::US,
        );
        $fedexRequestProviderMock = \Mockery::mock(FedexAddressValidationRequestProvider::class);
        $fedexRequestProviderMock->shouldReceive('getUrl')->andReturn('http://fake.fedex.com');
        $fedexRequestProviderMock->shouldReceive('getRequestData')->andReturn(['some raw data']);
        $fedexRequestProviderMock->shouldReceive('getHeaders')->andReturn(['x-custom'=>'headers']);
        $this->app->bind(FedexAddressValidationRequestProvider::class, function () use ($fedexRequestProviderMock) {
            return $fedexRequestProviderMock;
        });
        // ups not touched.
        $upsRequestProviderMock = \Mockery::mock(UpsAddressValidationRequestProvider::class);
        $upsRequestProviderMock->shouldNotReceive('getUrl');
        $upsRequestProviderMock->shouldNotReceive('getRequestData');
        $upsRequestProviderMock->shouldNotReceive('getHeaders');
        $this->instance(UpsAddressValidationRequestProvider::class, $upsRequestProviderMock);
        $fedexValidationResponseMock = new AddressValidationResponseDTO(true, [], ShippingAddressClassificationTypeEnum::COMMERCIAL);
        $fedexResponseProviderMock = \Mockery::mock(FedexAddressValidationResponseProvider::class);
        $fedexResponseProviderMock->shouldReceive('getResponseDTO')
            ->withArgs([['some shaped request data'], 200])
            ->andReturn($fedexValidationResponseMock);
        $this->app->bind(FedexAddressValidationResponseProvider::class, function () use ($fedexResponseProviderMock) {
            return $fedexResponseProviderMock;
        });
        $manager = new AddressValidationManager();
        $manager->loadAddress($addressDto);
        $manager->checkFedex('fedex-token');
        $manager->validate();
        $actualAddressValidationResponseDTO = $manager->getFedexResponse();
        $this->assertSame(ShippingAddressClassificationTypeEnum::COMMERCIAL, $actualAddressValidationResponseDTO->addressType);
        $this->assertSame([], $actualAddressValidationResponseDTO->addressCandidates);
        $this->assertSame(true, $actualAddressValidationResponseDTO->matched);
        $this->assertNull($manager->getUpsResponse());
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://fake.fedex.com'
                && $request->data() === ['some raw data']
                && $request->method() === 'POST'
                && $request->headers()['x-custom'] === ['headers'];
        });
    }
    public function testFedexAndUps(): void
    {
        Http::fake([
            'http://fake.fedex.com' => Http::response(json_encode(['some shaped fedex request data']), 200),
            'http://fake.ups.com' => Http::response(json_encode(['some shaped ups request data']), 200),
        ]);
        $addressDto = new ShippingAddressDto(
            address_line_1: '123 My St',
            locality: 'Beverly Hills',
            administrativeArea: UsaStatesEnum::CA,
            postalCode: '90210',
            region: RegionsEnum::US,
        );
        $fedexRequestProviderMock = \Mockery::mock(FedexAddressValidationRequestProvider::class);
        $fedexRequestProviderMock->shouldReceive('getUrl')->andReturn('http://fake.fedex.com');
        $fedexRequestProviderMock->shouldReceive('getRequestData')->andReturn(['some raw fedex data']);
        $fedexRequestProviderMock->shouldReceive('getHeaders')->andReturn(['x-custom'=>'headers']);
        $this->app->bind(FedexAddressValidationRequestProvider::class, function () use ($fedexRequestProviderMock) {
            return $fedexRequestProviderMock;
        });
        $upsRequestProviderMock = \Mockery::mock(UpsAddressValidationRequestProvider::class);
        $upsRequestProviderMock->shouldReceive('getUrl')->andReturn('http://fake.ups.com');
        $upsRequestProviderMock->shouldReceive('getRequestData')->andReturn(['some raw ups data']);
        $upsRequestProviderMock->shouldReceive('getHeaders')->andReturn(['x-custom'=>'headers']);
        $this->app->bind(UpsAddressValidationRequestProvider::class, function () use ($upsRequestProviderMock) {
            return $upsRequestProviderMock;
        });
        $fedexValidationResponseMock = new AddressValidationResponseDTO(true, [], ShippingAddressClassificationTypeEnum::COMMERCIAL);
        $fedexResponseProviderMock = \Mockery::mock(FedexAddressValidationResponseProvider::class);
        $fedexResponseProviderMock->shouldReceive('getResponseDTO')
            ->withArgs([['some shaped fedex request data'], 200])
            ->andReturn($fedexValidationResponseMock);
        $this->app->bind(FedexAddressValidationResponseProvider::class, function () use ($fedexResponseProviderMock) {
            return $fedexResponseProviderMock;
        });
        $upsResponseProviderMock = \Mockery::mock(UpsAddressValidationResponseProvider::class);
        // set some other results from fedex above so we can see correct response is coming from carrier we expect
        $upsValidationResponseMock = new AddressValidationResponseDTO(false, [], ShippingAddressClassificationTypeEnum::RESIDENTIAL);
        $upsResponseProviderMock->shouldReceive('getResponseDTO')
            ->withArgs([['some shaped ups request data'], 200])
            ->andReturn($upsValidationResponseMock);
        $this->instance(UpsAddressValidationResponseProvider::class, $upsResponseProviderMock);
        $manager = new AddressValidationManager();
        $manager->loadAddress($addressDto);
        $manager->checkFedex('fedex-token');
        $manager->checkUps('ups-token');
        $manager->validate();
        $actualAddressValidationResponseDTO = $manager->getFedexResponse();
        $this->assertSame(ShippingAddressClassificationTypeEnum::COMMERCIAL, $actualAddressValidationResponseDTO->addressType);
        $this->assertSame([], $actualAddressValidationResponseDTO->addressCandidates);
        $this->assertSame(true, $actualAddressValidationResponseDTO->matched);
        $actualAddressValidationResponseDTO = $manager->getUpsResponse();
        $this->assertSame(ShippingAddressClassificationTypeEnum::RESIDENTIAL, $actualAddressValidationResponseDTO->addressType);
        $this->assertSame([], $actualAddressValidationResponseDTO->addressCandidates);
        $this->assertSame(false, $actualAddressValidationResponseDTO->matched);
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://fake.fedex.com'
                && $request->data() === ['some raw fedex data']
                && $request->method() === 'POST'
                && $request->headers()['x-custom'] === ['headers'];
        });
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://fake.ups.com'
                && $request->data() === ['some raw ups data']
                && $request->method() === 'POST'
                && $request->headers()['x-custom'] === ['headers'];
        });
    }
}
