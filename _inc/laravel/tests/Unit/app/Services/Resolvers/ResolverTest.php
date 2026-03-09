<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services\Resolvers;

use App\Services\DTO\ZipGeoResult;
use App\Services\Resolvers\{BrasilApiCepV2Resolver, ViaCepResolver};
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, Group, Test};
use Tests\TestCase;

#[CoversClass(BrasilApiCepV2Resolver::class)]
#[CoversClass(ViaCepResolver::class)]
#[Group('services')]
class ResolverTest extends TestCase
{
	/* ══════════════ BrasilApiCepV2Resolver::supports ══════════════ */

	#[Test]
	public function brasil_api_supports_br(): void
	{
		$r = new BrasilApiCepV2Resolver();
		$this->assertTrue($r->supports('BR'));
		$this->assertTrue($r->supports('br'));
		$this->assertTrue($r->supports(' BR '));
	}

	#[Test]
	public function brasil_api_does_not_support_us(): void
	{
		$this->assertFalse((new BrasilApiCepV2Resolver())->supports('US'));
	}

	/* ══════════════ BrasilApiCepV2Resolver::resolve ══════════════ */

	#[Test]
	public function brasil_api_rejects_non_br(): void
	{
		$this->assertNull((new BrasilApiCepV2Resolver())->resolve('01311000', 'US'));
	}

	#[Test]
	public function brasil_api_rejects_invalid_zip_format(): void
	{
		$this->assertNull((new BrasilApiCepV2Resolver())->resolve('1234', 'BR'));
		$this->assertNull((new BrasilApiCepV2Resolver())->resolve('01311-000', 'BR'));
		$this->assertNull((new BrasilApiCepV2Resolver())->resolve('', 'BR'));
	}

	#[Test]
	public function brasil_api_returns_result_on_success(): void
	{
		Http::fake([
			'brasilapi.com.br/*' => Http::response([
				'state' => 'SP',
				'city' => 'São Paulo',
				'neighborhood' => 'Bela Vista',
				'street' => 'Avenida Paulista',
				'location' => ['coordinates' => ['latitude' => -23.56, 'longitude' => -46.65]],
			], 200),
		]);

		$result = (new BrasilApiCepV2Resolver())->resolve('01311000', 'BR');
		$this->assertInstanceOf(ZipGeoResult::class, $result);
		$this->assertSame('SP', $result->state);
		$this->assertSame('São Paulo', $result->city);
		$this->assertSame('Bela Vista', $result->neighborhood);
		$this->assertSame('Avenida Paulista', $result->street);
		$this->assertEqualsWithDelta(-23.56, $result->latitude, 0.01);
		$this->assertEqualsWithDelta(-46.65, $result->longitude, 0.01);
		$this->assertSame('brasilapi:cep_v2', $result->provider);
	}

	#[Test]
	public function brasil_api_returns_null_on_404(): void
	{
		Http::fake(['brasilapi.com.br/*' => Http::response(null, 404)]);
		$this->assertNull((new BrasilApiCepV2Resolver())->resolve('00000000', 'BR'));
	}

	#[Test]
	public function brasil_api_returns_null_on_empty_data(): void
	{
		Http::fake(['brasilapi.com.br/*' => Http::response([
			'state' => null,
			'city' => null,
			'neighborhood' => null,
			'street' => null,
			'location' => ['coordinates' => ['latitude' => null, 'longitude' => null]],
		], 200)]);
		$this->assertNull((new BrasilApiCepV2Resolver())->resolve('01311000', 'BR'));
	}

	#[Test]
	public function brasil_api_invalid_state_code_set_to_null(): void
	{
		Http::fake(['brasilapi.com.br/*' => Http::response([
			'state' => 'INVALID',
			'city' => 'Rio',
			'neighborhood' => 'Centro',
			'street' => 'Rua X',
			'location' => ['coordinates' => ['latitude' => null, 'longitude' => null]],
		], 200)]);
		$result = (new BrasilApiCepV2Resolver())->resolve('01311000', 'BR');
		$this->assertInstanceOf(ZipGeoResult::class, $result);
		$this->assertNull($result->state);
		$this->assertSame('Rio', $result->city);
	}

	/* ══════════════ ViaCepResolver::supports ══════════════ */

	#[Test]
	public function viacep_supports_br(): void
	{
		$r = new ViaCepResolver();
		$this->assertTrue($r->supports('BR'));
		$this->assertTrue($r->supports('br'));
	}

	#[Test]
	public function viacep_does_not_support_us(): void
	{
		$this->assertFalse((new ViaCepResolver())->supports('US'));
	}

	/* ══════════════ ViaCepResolver::resolve ══════════════ */

	#[Test]
	public function viacep_rejects_non_br(): void
	{
		$this->assertNull((new ViaCepResolver())->resolve('01311000', 'US'));
	}

	#[Test]
	public function viacep_rejects_invalid_zip(): void
	{
		$this->assertNull((new ViaCepResolver())->resolve('1234', 'BR'));
	}

	#[Test]
	public function viacep_returns_result_on_success(): void
	{
		Http::fake([
			'viacep.com.br/*' => Http::response([
				'uf' => 'SP',
				'localidade' => 'São Paulo',
				'bairro' => 'Consolação',
				'logradouro' => 'Rua Augusta',
			], 200),
		]);

		$result = (new ViaCepResolver())->resolve('01304001', 'BR');
		$this->assertInstanceOf(ZipGeoResult::class, $result);
		$this->assertSame('SP', $result->state);
		$this->assertSame('São Paulo', $result->city);
		$this->assertSame('Consolação', $result->neighborhood);
		$this->assertSame('Rua Augusta', $result->street);
		$this->assertNull($result->latitude);
		$this->assertNull($result->longitude);
		$this->assertSame('viacep', $result->provider);
	}

	#[Test]
	public function viacep_returns_null_on_erro_flag(): void
	{
		Http::fake(['viacep.com.br/*' => Http::response(['erro' => true], 200)]);
		$this->assertNull((new ViaCepResolver())->resolve('00000000', 'BR'));
	}

	#[Test]
	public function viacep_returns_null_on_404(): void
	{
		Http::fake(['viacep.com.br/*' => Http::response(null, 404)]);
		$this->assertNull((new ViaCepResolver())->resolve('01304001', 'BR'));
	}

	/* ══════════════ performance ══════════════ */

	#[Test]
	public function resolver_supports_performance(): void
	{
		$bra = new BrasilApiCepV2Resolver();
		$via = new ViaCepResolver();
		$start = hrtime(true);
		for ($i = 0; $i < 5000; $i++) {
			$bra->supports('BR');
			$via->supports('BR');
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(50, $elapsed, '10k supports() calls should be < 50ms');
	}
}
