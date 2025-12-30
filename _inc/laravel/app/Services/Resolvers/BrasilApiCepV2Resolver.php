<?php

namespace App\Services\Resolvers;

use App\Services\{Contracts\ZipGeoResolver, DTO\ZipGeoResult};
use Illuminate\Support\Facades\{Http, Log};

final class BrasilApiCepV2Resolver implements ZipGeoResolver
{
	private const BASE_URL = 'https://brasilapi.com.br/api/cep/v2/';
	private const PROVIDER = 'brasilapi:cep_v2';

	public function supports(string $countryCode): bool
	{
		return strtoupper(trim($countryCode)) === 'BR';
	}

	public function resolve(string $zip, string $countryCode): ?ZipGeoResult
	{
		if (!$this->supports($countryCode)) return null;

		if (!preg_match('/^\d{8}$/', $zip)) return null;

		try {
			$resp = Http::timeout(6)
				->retry(2, 250, throw: false)
				->acceptJson()
				->get(self::BASE_URL . $zip);

			if (!$resp->ok()) return null;

			$data = $resp->json();
			if (!is_array($data)) return null;

			$state = $this->strOrNull($data['state'] ?? null);
			$city = $this->strOrNull($data['city'] ?? null);
			$neighborhood = $this->strOrNull($data['neighborhood'] ?? null);
			$street = $this->strOrNull($data['street'] ?? null);

			$lon = $this->floatOrNull($data['location']['coordinates']['longitude'] ?? null);
			$lat = $this->floatOrNull($data['location']['coordinates']['latitude'] ?? null);

			if ($state !== null && !preg_match('/^[A-Z]{2}$/', $state)) {
				$state = null;
			}

			if ($city === null && $state === null && $street === null && $neighborhood === null) {
				return null;
			}

			return new ZipGeoResult(
				countryCode: 'BR',
				state: $state,
				city: $city,
				neighborhood: $neighborhood,
				street: $street,
				latitude: $lat,
				longitude: $lon,
				provider: self::PROVIDER,
				raw: $data
			);
		} catch (\Throwable $e) {
			Log::debug(self::PROVIDER . ' failed', [
				'zip' => $zip,
				'error' => $e->getMessage(),
			]);
			return null;
		}
	}

	private function strOrNull(mixed $v): ?string
	{
		if (!is_scalar($v)) return null;
		$s = trim((string) $v);
		return $s === '' ? null : $s;
	}

	private function floatOrNull(mixed $v): ?float
	{
		if ($v === null) return null;
		if (is_numeric($v)) return (float) $v;
		if (is_string($v)) {
			$s = trim($v);
			if ($s !== '' && is_numeric($s)) return (float) $s;
		}
		return null;
	}
}
