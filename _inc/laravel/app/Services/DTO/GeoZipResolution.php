<?php

namespace App\Services\DTO;

final class GeoZipResolution
{
	public function __construct(
		public readonly string $countryCode,
		public readonly ?string $state = null,
		public readonly ?string $city = null,
		public readonly ?string $street = null,
		public readonly ?string $neighborhood = null,
		public readonly ?string $service = null
	) {}
}
