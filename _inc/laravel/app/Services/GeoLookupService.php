<?php

namespace App\Services;

use App\Services\{DTO\GeoZipResolution, Providers\BrasilApiCepProvider};

final class GeoLookupService
{
	public function __construct(
		private readonly BrasilApiCepProvider $brasilApiCepProvider
	) {}

	public function resolveFromZip(string $zip, ?string $countryCode = null): ?GeoZipResolution
	{
		$cc = is_string($countryCode) ? strtoupper(trim($countryCode)) : null;
		if ($cc === null || $cc === '' || $cc === 'BR') {
			$r = $this->brasilApiCepProvider->resolve($zip);
			if ($r) return $r;
		}
		return null;
	}
}
