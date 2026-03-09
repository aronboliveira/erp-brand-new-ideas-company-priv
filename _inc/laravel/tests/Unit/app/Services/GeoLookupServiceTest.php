<?php

declare(strict_types=1);

namespace Tests\Unit\app\Services;

use App\Services\DTO\GeoZipResolution;
use App\Services\GeoLookupService;
use App\Services\Providers\BrasilApiCepProvider;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\{CoversClass, Group, Test};
use Tests\TestCase;

/**
 * Tests for GeoLookupService.
 *
 * BrasilApiCepProvider is final, so we use Http::fake() to control
 * its behaviour rather than mocking the class directly.
 */
#[CoversClass(GeoLookupService::class)]
#[Group('services')]
class GeoLookupServiceTest extends TestCase
{
	private function makeService(): GeoLookupService
	{
		return new GeoLookupService(new BrasilApiCepProvider());
	}

	private function fakeSuccessResponse(
		string $state = 'SP',
		string $city = 'São Paulo',
		?string $street = null,
		?string $neighborhood = null,
	): void {
		Http::fake([
			'brasilapi.com.br/*' => Http::response([
				'cep'          => '01311000',
				'state'        => $state,
				'city'         => $city,
				'street'       => $street ?? '',
				'neighborhood' => $neighborhood ?? '',
				'service'      => 'open-cep',
			], 200),
		]);
	}

	#[Test]
	public function resolve_from_zip_delegates_to_br_provider(): void
	{
		$this->fakeSuccessResponse('SP', 'São Paulo');
		$svc = $this->makeService();

		$result = $svc->resolveFromZip('01311000', 'BR');

		$this->assertInstanceOf(GeoZipResolution::class, $result);
		$this->assertSame('BR', $result->countryCode);
		$this->assertSame('SP', $result->state);
		$this->assertSame('São Paulo', $result->city);
	}

	#[Test]
	public function resolve_from_zip_null_country_tries_br(): void
	{
		$this->fakeSuccessResponse('RJ', 'Rio');
		$svc = $this->makeService();

		$result = $svc->resolveFromZip('20040020', null);

		$this->assertNotNull($result);
		$this->assertSame('RJ', $result->state);
		$this->assertSame('Rio', $result->city);
	}

	#[Test]
	public function resolve_from_zip_empty_country_tries_br(): void
	{
		$this->fakeSuccessResponse('MG', 'BH');
		$svc = $this->makeService();

		$result = $svc->resolveFromZip('30130000', '');

		$this->assertNotNull($result);
		$this->assertSame('MG', $result->state);
	}

	#[Test]
	public function resolve_from_zip_non_br_returns_null(): void
	{
		Http::fake(); // nothing should be called
		$svc = $this->makeService();

		$this->assertNull($svc->resolveFromZip('10001', 'US'));
		Http::assertNothingSent();
	}

	#[Test]
	public function resolve_from_zip_provider_returns_null_on_bad_zip(): void
	{
		Http::fake(); // 5 digits → BrasilApiCepProvider returns null before HTTP call
		$svc = $this->makeService();

		$this->assertNull($svc->resolveFromZip('00000', 'BR'));
	}

	#[Test]
	public function resolve_from_zip_provider_returns_null_on_api_404(): void
	{
		Http::fake([
			'brasilapi.com.br/*' => Http::response(null, 404),
		]);
		$svc = $this->makeService();

		$this->assertNull($svc->resolveFromZip('00000000', 'BR'));
	}

	#[Test]
	public function resolve_from_zip_lowercase_br(): void
	{
		$this->fakeSuccessResponse('SP', 'São Paulo');
		$svc = $this->makeService();

		$result = $svc->resolveFromZip('01311000', 'br');

		$this->assertNotNull($result);
		$this->assertSame('SP', $result->state);
	}

	#[Test]
	public function resolve_from_zip_strips_non_digits(): void
	{
		$this->fakeSuccessResponse('PR', 'Curitiba');
		$svc = $this->makeService();

		$result = $svc->resolveFromZip('80.010-000', 'BR');

		$this->assertNotNull($result);
		$this->assertSame('PR', $result->state);
	}

	#[Test]
	public function resolve_from_zip_handles_api_exception_gracefully(): void
	{
		Http::fake([
			'brasilapi.com.br/*' => fn() => throw new \RuntimeException('Network error'),
		]);
		$svc = $this->makeService();

		$this->assertNull($svc->resolveFromZip('01311000', 'BR'));
	}

	/* ══════════════ performance ══════════════ */

	#[Test]
	public function geo_lookup_performance(): void
	{
		$this->fakeSuccessResponse('SP', 'São Paulo');
		$svc = $this->makeService();

		$start = hrtime(true);
		for ($i = 0; $i < 500; $i++) {
			$svc->resolveFromZip('01311000', 'BR');
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(2000, $elapsed, '500 resolveFromZip (Http::fake) should be < 2s');
	}
}
