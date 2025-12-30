<?php

namespace App\Services\Resolvers;

use App\Services\{Contracts\ZipGeoResolver, DTO\ZipGeoResult};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class ViaCepResolver implements ZipGeoResolver
{
	private const BASE_URL = 'https://viacep.com.br/ws/';
	private const PROVIDER = 'viacep';

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
				->get(self::BASE_URL . $zip . '/json/');

			if (!$resp->ok()) return null;

			$data = $resp->json();
			if (!is_array($data)) return null;

			if (!empty($data['erro'])) return null;

			$state = $this->strOrNull($data['uf'] ?? null);
			$city = $this->strOrNull($data['localidade'] ?? null);
			$neighborhood = $this->strOrNull($data['bairro'] ?? null);
			$street = $this->strOrNull($data['logradouro'] ?? null);

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
				latitude: null,
				longitude: null,
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
}
