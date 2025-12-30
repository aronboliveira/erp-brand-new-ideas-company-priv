<?php

namespace App\Services\DTO;

final class ZipGeoResult
{
	public function __construct(
		public readonly string $countryCode,
		public readonly ?string $state,
		public readonly ?string $city,
		public readonly ?string $neighborhood,
		public readonly ?string $street,
		public readonly ?float $latitude,
		public readonly ?float $longitude,
		public readonly string $provider,
		public readonly array $raw = [],
	) {}
}
