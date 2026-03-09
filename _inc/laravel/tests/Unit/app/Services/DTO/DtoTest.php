<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\DTO;

use App\Services\DTO\{GeoZipResolution, ZipGeoResult};
use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, Group, Test};
use PHPUnit\Framework\TestCase;

#[CoversClass(GeoZipResolution::class)]
#[CoversClass(ZipGeoResult::class)]
#[Group('services')]
class DtoTest extends TestCase
{
	/* ══════════════════════ GeoZipResolution ══════════════════════ */

	#[Test]
	public function geo_zip_resolution_stores_all_fields(): void
	{
		$dto = new GeoZipResolution(
			countryCode: 'BR',
			state: 'RJ',
			city: 'Rio de Janeiro',
			street: 'Av Brasil',
			neighborhood: 'Centro',
			service: 'brasilapi'
		);
		$this->assertSame('BR', $dto->countryCode);
		$this->assertSame('RJ', $dto->state);
		$this->assertSame('Rio de Janeiro', $dto->city);
		$this->assertSame('Av Brasil', $dto->street);
		$this->assertSame('Centro', $dto->neighborhood);
		$this->assertSame('brasilapi', $dto->service);
	}

	#[Test]
	public function geo_zip_resolution_defaults_to_null(): void
	{
		$dto = new GeoZipResolution(countryCode: 'US');
		$this->assertSame('US', $dto->countryCode);
		$this->assertNull($dto->state);
		$this->assertNull($dto->city);
		$this->assertNull($dto->street);
		$this->assertNull($dto->neighborhood);
		$this->assertNull($dto->service);
	}

	#[Test]
	public function geo_zip_resolution_is_readonly(): void
	{
		$dto = new GeoZipResolution(countryCode: 'BR', state: 'SP');
		$ref = new \ReflectionProperty($dto, 'countryCode');
		$this->assertTrue($ref->isReadOnly());
	}

	/* ══════════════════════ ZipGeoResult ══════════════════════ */

	#[Test]
	public function zip_geo_result_stores_all_fields(): void
	{
		$dto = new ZipGeoResult(
			countryCode: 'BR',
			state: 'SP',
			city: 'São Paulo',
			neighborhood: 'Pinheiros',
			street: 'Rua dos Pinheiros',
			latitude: -23.5631,
			longitude: -46.6869,
			provider: 'brasilapi:cep_v2',
			raw: ['cep' => '01311000']
		);
		$this->assertSame('BR', $dto->countryCode);
		$this->assertSame('SP', $dto->state);
		$this->assertSame('São Paulo', $dto->city);
		$this->assertSame('Pinheiros', $dto->neighborhood);
		$this->assertSame('Rua dos Pinheiros', $dto->street);
		$this->assertEqualsWithDelta(-23.5631, $dto->latitude, 0.0001);
		$this->assertEqualsWithDelta(-46.6869, $dto->longitude, 0.0001);
		$this->assertSame('brasilapi:cep_v2', $dto->provider);
		$this->assertSame(['cep' => '01311000'], $dto->raw);
	}

	#[Test]
	public function zip_geo_result_nullable_coords(): void
	{
		$dto = new ZipGeoResult(
			countryCode: 'BR',
			state: 'RJ',
			city: 'Rio',
			neighborhood: null,
			street: null,
			latitude: null,
			longitude: null,
			provider: 'viacep'
		);
		$this->assertNull($dto->latitude);
		$this->assertNull($dto->longitude);
		$this->assertNull($dto->neighborhood);
		$this->assertNull($dto->street);
		$this->assertSame([], $dto->raw);
	}

	#[Test]
	public function zip_geo_result_is_readonly(): void
	{
		$dto = new ZipGeoResult('BR', 'SP', 'SP', null, null, null, null, 'x');
		$ref = new \ReflectionProperty($dto, 'provider');
		$this->assertTrue($ref->isReadOnly());
	}

	/* ══════════════════════ performance ══════════════════════ */

	#[Test]
	public function dto_creation_performance(): void
	{
		$start = hrtime(true);
		for ($i = 0; $i < 5000; $i++) {
			new GeoZipResolution('BR', 'SP', 'São Paulo');
			new ZipGeoResult('BR', 'SP', 'São Paulo', null, null, null, null, 'x');
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(200, $elapsed, '10000 DTO instantiations should be < 200ms');
	}
}
