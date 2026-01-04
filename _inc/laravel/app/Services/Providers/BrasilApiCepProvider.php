<?php

namespace App\Services\Providers;

use App\Services\DTO\GeoZipResolution;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class BrasilApiCepProvider
{
	public function resolve(string $zip): ?GeoZipResolution
	{
		$cep = preg_replace('/\D+/', '', $zip) ?: '';
		if (strlen($cep) !== 8) return null;

		try {
			$timeout = (int) env('GEO_HTTP_TIMEOUT', 5);

			$res = Http::timeout($timeout)
				->retry(2, 150)
				->acceptJson()
				->get("https://brasilapi.com.br/api/cep/v1/{$cep}");

			if (!$res->ok()) return null;

			$j = $res->json();
			if (!is_array($j)) return null;

			$state = isset($j['state']) && is_string($j['state']) ? trim($j['state']) : null;
			$city  = isset($j['city']) && is_string($j['city']) ? trim($j['city']) : null;
			$street = isset($j['street']) && is_string($j['street']) ? trim($j['street']) : null;
			$neighborhood = isset($j['neighborhood']) && is_string($j['neighborhood']) ? trim($j['neighborhood']) : null;
			$service = isset($j['service']) && is_string($j['service']) ? trim($j['service']) : null;

			return new GeoZipResolution(
				countryCode: 'BR',
				state: $state !== '' ? $state : null,
				city: $city !== '' ? $city : null,
				street: $street !== '' ? $street : null,
				neighborhood: $neighborhood !== '' ? $neighborhood : null,
				service: $service !== '' ? $service : null
			);
		} catch (\Throwable $t) {
			Log::notice('BrasilApiCepProvider failed', [
				'zip' => $zip,
				'error' => $t->getMessage(),
			]);
			return null;
		}
	}
}
