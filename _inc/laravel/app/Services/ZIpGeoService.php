<?php

namespace App\Services;

use App\Services\{Contracts\ZipGeoResolver, DTO\ZipGeoResult};
use Illuminate\Support\Facades\Cache;

final class ZipGeoService
{
	/**
	 * @param ZipGeoResolver[] $resolvers
	 */
	public function __construct(private readonly array $resolvers) {}

	public function resolve(string $zip, string $countryCode): ?ZipGeoResult
	{
		$countryCode = strtoupper(trim($countryCode));
		$zip = $this->normalizeZipDigits($zip);

		if ($zip === null || $countryCode === '') return null;

		$cacheKey = "geo:zip:{$countryCode}:{$zip}";
		return Cache::remember($cacheKey, now()->addDays(14), function () use ($zip, $countryCode) {
			foreach ($this->resolvers as $r) {
				if (!$r->supports($countryCode)) continue;
				$hit = $r->resolve($zip, $countryCode);
				if ($hit) return $hit;
			}
			return null;
		});
	}

	private function normalizeZipDigits(string $zip): ?string
	{
		$z = preg_replace('/\D+/', '', $zip);
		$z = is_string($z) ? $z : '';
		$z = trim($z);
		return $z === '' ? null : $z;
	}
}
