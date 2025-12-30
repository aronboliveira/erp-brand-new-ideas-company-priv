<?php

namespace App\Services\Contracts;

use App\Services\DTO\ZipGeoResult;

interface ZipGeoResolver
{
	public function supports(string $countryCode): bool;

	/**
	 * @param string $zip Normalizado (somente dígitos quando aplicável).
	 */
	public function resolve(string $zip, string $countryCode): ?ZipGeoResult;
}
