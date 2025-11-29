<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait NormalizesAddresses
{
	use NormalizesArrays;

	public static function normalizeEmail(?string $email, string $context, string|int|null $ownerId): ?string
	{
		$email = strtolower(trim((string) $email));
		if ($email === '')
			return null;

		if (!preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $email)) {
			Log::warning(static::class . " invalid {$context} email", [
				'owner_id' => $ownerId,
				'email'    => $email,
			]);
			return $email;
		}

		return $email;
	}

	public static function normalizePhone(?string $phone, string $context, string|int|null $ownerId): ?string
	{
		$phone = trim((string) $phone);
		if ($phone === '')
			return null;

		if (!preg_match('/^\+?[0-9 ()\-]{7,20}$/', $phone)) {
			Log::warning(static::class . " invalid {$context} phone", [
				'owner_id' => $ownerId,
				'phone'    => $phone,
			]);
			return $phone;
		}

		return $phone;
	}

	public static function normalizeZip(?string $zip, ?string $country, string $context, string|int|null $ownerId): ?string
	{
		$zip = trim((string) $zip);
		if ($zip === '')
			return null;

		$countryNorm = strtolower(trim((string) $country));

		// Brasil – CEP
		if (in_array($countryNorm, ['br', 'bra', 'brazil', 'brasil'], true)) {
			$digits = preg_replace('/\D+/', '', $zip);

			if (strlen($digits) === 8)
				return substr($digits, 0, 5) . '-' . substr($digits, 5);

			if (!preg_match('/^\d{5}\-?\d{3}$/', $zip)) {
				Log::warning(static::class . " invalid Brazilian {$context} zip", [
					'owner_id' => $ownerId,
					'zip'      => $zip,
				]);
				return $zip;
			}

			if (preg_match('/^\d{8}$/', $zip))
				return substr($zip, 0, 5) . '-' . substr($zip, 5);

			return $zip;
		}

		// Algo genérico para outros países
		if (!preg_match('/^[A-Za-z0-9\- ]{3,12}$/', $zip)) {
			Log::warning(static::class . " invalid {$context} zip", [
				'owner_id' => $ownerId,
				'zip'      => $zip,
			]);
		}

		return $zip;
	}

	public static function looksLikeUuid(string $value): bool
	{
		return (bool) preg_match(
			'/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$/',
			$value
		);
	}
}
