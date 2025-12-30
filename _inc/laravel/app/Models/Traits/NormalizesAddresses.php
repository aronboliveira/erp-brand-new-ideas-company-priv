<?php

namespace App\Traits;

use App\Config\Constants\BillsConstants as BC;
use App\Enums\{BrazilState, ChinaState, ContactKeyType, CountryName, PortugalState, UnitedStatesState};
use App\Models\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{Log, Schema};

trait NormalizesAddresses
{
	use NormalizesArrays;

	public static function bootNormalizesAddresses(): void
	{
		static::saving(function (self $model) {
			Schema::hasColumn($model->getTable(), 'phone') && $model->setAttribute(
				'phone',
				self::normalizePhone(
					$model->getAttribute('phone'),
					'phone',
					$model->getKey(),
					false
				)
			);
			Schema::hasColumn($model->getTable(), 'email') && $model->setAttribute(
				'email',
				self::normalizeEmail(
					$model->getAttribute('email'),
					'email',
					$model->getKey()
				)
			);
			Schema::hasColumn($model->getTable(), BC::COL_BL_EMAIL) && $model->setAttribute(
				BC::COL_BL_EMAIL,
				self::normalizeEmail(
					$model->getAttribute(BC::COL_BL_EMAIL),
					'billing_email',
					$model->getKey()
				)
			);
			Schema::hasColumn($model->getTable(), BC::COL_BL_TEL) && $model->setAttribute(
				BC::COL_BL_TEL,
				self::normalizePhone(
					$model->getAttribute(BC::COL_BL_TEL),
					'billing_phone',
					$model->getKey(),
					false
				)
			);
			Schema::hasColumn($model->getTable(), BC::COL_BL_ZIP) && $model->setAttribute(
				BC::COL_BL_ZIP,
				self::normalizeZip(
					$model->getAttribute(BC::COL_BL_ZIP),
					$model->getAttribute(BC::COL_BL_CTR),
					'billing_zip',
					$model->getKey()
				)
			);
			Schema::hasColumn($model->getTable(), BC::COL_BL_CTR) && self::normalizeBillingCountry($model);
			Schema::hasColumn($model->getTable(), BC::COL_SHIP_CTR) && self::normalizeShippingCountry($model);
			Schema::hasColumn($model->getTable(), 'zip') && $model->setAttribute(
				'zip',
				self::normalizeZip(
					$model->getAttribute('zip'),
					$model->getAttribute('country'),
					'zip',
					$model->getKey()
				)
			);
		});
	}

	public static function normalizeEmail(?string $email, string|null $context = '', string|int|null $ownerId = ''): ?string
	{
		$email = strtolower(trim((string) $email));
		if ($email === '')
			return null;
		if (!preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $email)) {
			Log::warning("[" . self::class . "]: " . static::class . " invalid {$context} email", [
				'owner_id' => $ownerId,
				'email'    => $email,
				'method' => __METHOD__,
				'file' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? __FILE__,
				'line' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? __LINE__,
			]);
			return $email;
		}

		return $email;
	}

	public static function normalizePhone(?string $phone, ?string $context, string|int|null $ownerId, ?bool $isMock = false): ?string
	{
		if ($phone === null || gettype($phone) !== 'string' || trim((string) $phone) === '') {
			if ($isMock)
				return Utility::generateBrazilianPhone();
			else {
				Log::debug(static::class . " empty {$context} phone", [
					'owner_id' => $ownerId,
				]);
				return null;
			}
		}

		$phone = trim((string) $phone);
		if (empty($context)) $context = '#NO_CONTEXT';
		if (empty($ownerId)) $ownerId = '#NO_OWNER_ID';

		$digitsOnly = preg_replace('/[^0-9+]/', '', $phone);

		if (!preg_match('/^\+?\d{7,15}$/', $digitsOnly)) {
			if ($isMock)
				return Utility::generateBrazilianPhone();
			else {
				Log::warning("[" . self::class . "]: " . static::class . " invalid {$context} phone", [
					'owner_id' => $ownerId,
					'phone'    => $phone,
					'method' => __METHOD__,
					'file' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? __FILE__,
					'line' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? __LINE__,
					'original' => (is_string($phone) && !empty($phone)) ? $phone : '#NULL',
				]);
				return null;
			}
		}

		return $phone; // Return original formatted phone
	}

	public static function normalizeZip(?string $zip, ?string $country, ?string $context, string|int|null $ownerId): ?string
	{
		$zip = trim((string) $zip);
		if ($zip === '')
			return null;
		if ($country === null || trim((string) $country) === '') $country = 'brazil';
		if (empty($context)) $context = '#NO_CONTEXT';
		if (empty($ownerId)) $ownerId = '#NO_OWNER_ID';
		$countryNorm = strtolower(trim((string) $country));

		// Brasil – CEP
		if (in_array($countryNorm, ['br', 'bra', 'brazil', 'brasil'], true)) {
			$digits = preg_replace('/\D+/', '', $zip);

			if (strlen($digits) === 8)
				return substr($digits, 0, 5) . '-' . substr($digits, 5);

			if (!preg_match('/^\d{5}\-?\d{3}$/', $zip)) {
				Log::warning("[" . self::class . "]: " . static::class . " invalid Brazilian {$context} zip", [
					'owner_id' => $ownerId,
					'zip'      => $zip,
					'method' => __METHOD__,
					'file' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? __FILE__,
					'line' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? __LINE__,
					'original' => (is_string($zip) && !empty($zip)) ? $zip : '#NULL',
				]);
				return $zip;
			}

			if (preg_match('/^\d{8}$/', $zip))
				return substr($zip, 0, 5) . '-' . substr($zip, 5);

			return $zip;
		}

		// Algo genérico para outros países
		if (!preg_match('/^[A-Za-z0-9\- ]{3,12}$/', $zip)) {
			Log::warning("[" . self::class . "]: " . static::class . " invalid {$context} zip", [
				'owner_id' => $ownerId,
				'zip'      => $zip,
				'method' => __METHOD__,
				'file' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? __FILE__,
				'line' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? __LINE__,
				'original' => (is_string($zip) && !empty($zip)) ? $zip : '#NULL',
			]);
		}

		return $zip;
	}

	public static function normalizeContactKey(string $key): string
	{
		$key = mb_strtolower(trim($key));
		$key = str_replace([' ', '-', '.', '–', '—'], '_', $key);
		return preg_replace('/_+/', '_', $key);
	}

	public static function keyIsEmail(string $normalizedKey): bool
	{
		return in_array($normalizedKey, ContactKeyType::EMAIL_KEYS, true);
	}

	public static function keyIsPhone(string $normalizedKey): bool
	{
		return in_array($normalizedKey, ContactKeyType::PHONE_KEYS, true);
	}

	public static function keyIsGenericContact(string $normalizedKey): bool
	{
		return in_array($normalizedKey, ContactKeyType::CONTACT_KEYS, true);
	}

	protected function normalizeContactFields(array $attr, array $secondaryAttrs, string|int $ownerId): void
	{
		if (is_array($attr)) {
			$attr = $this->normalizeContactArrayRecursive(
				$attr,
				'event.participants',
				$ownerId
			);
		}

		// organizers / confirmed / sponsors
		foreach ($secondaryAttrs as $field) {
			$value = $this->{$field};

			if (!is_array($value)) {
				continue;
			}

			$this->{$field} = $this->normalizeContactArrayRecursive(
				$value,
				'event.' . $field,
				$ownerId
			);
		}
	}

	protected function normalizeContactArrayRecursive(
		array $data,
		string $context,
		string|int|null $ownerId
	): array {
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$data[$key] = $this->normalizeContactArrayRecursive(
					$value,
					$context . '.' . (is_string($key) ? $key : (string) $key),
					$ownerId
				);
				continue;
			}

			if (!is_scalar($value)) {
				continue;
			}

			$normalizedKey = $this->normalizeContactKey((string) $key);
			$stringValue   = (string) $value;

			if ($this->keyIsEmail($normalizedKey)) {
				if (preg_match(ContactKeyType::EMAIL_REGEX, $stringValue)) {
					$data[$key] = static::normalizeEmail(
						$stringValue,
						$context . '.' . $key,
						$ownerId
					);
				}
				continue;
			}

			if ($this->keyIsPhone($normalizedKey)) {
				if (preg_match(ContactKeyType::PHONE_REGEX, $stringValue)) {
					$data[$key] = static::normalizePhone(
						$stringValue,
						$context . '.' . $key,
						$ownerId
					);
				}
				continue;
			}

			// chave genérica de contato: tenta primeiro email, depois phone
			if ($this->keyIsGenericContact($normalizedKey)) {
				if (preg_match(ContactKeyType::EMAIL_REGEX, $stringValue)) {
					$data[$key] = static::normalizeEmail(
						$stringValue,
						$context . '.' . $key,
						$ownerId
					);
				} elseif (preg_match(ContactKeyType::PHONE_REGEX, $stringValue)) {
					$data[$key] = static::normalizePhone(
						$stringValue,
						$context . '.' . $key,
						$ownerId
					);
				}
			}
		}

		return $data;
	}
	protected static function normalizeBillingCountry(Model $model): void
	{
		$billingCountryEnum = CountryName::normalize($model->getAttribute(BC::COL_BL_CTR) ?? null)
			?? CountryName::Brazil;
		$model->setAttribute(BC::COL_BL_CTR, $billingCountryEnum->value);
		self::normalizeStateField($model, BC::COL_BL_ST, $billingCountryEnum);
	}
	protected static function normalizeShippingCountry(Model $model): void
	{
		$shippingCountryEnum = CountryName::normalize($model->getAttribute(BC::COL_SHIP_CTR) ?? null)
			?? CountryName::Brazil;
		$model->setAttribute(BC::COL_SHIP_CTR, $shippingCountryEnum->value);
		self::normalizeStateField($model, BC::COL_SHIP_ST, $shippingCountryEnum);
	}
	protected static function normalizeStateField(self $customer, string $column, CountryName $country): void
	{
		$raw = $customer->getAttribute($column) ?? null;
		$normalized = null;
		switch ($country) {
			case CountryName::Brazil:
				$normalized = BrazilState::normalize($raw) ?? BrazilState::RJ;
				break;
			case CountryName::Portugal:
				$normalized = PortugalState::normalize($raw) ?? PortugalState::LS;
				break;
			case CountryName::UnitedStates:
				$normalized = UnitedStatesState::normalize($raw) ?? UnitedStatesState::CA;
				break;
			case CountryName::China:
				$normalized = ChinaState::normalize($raw) ?? ChinaState::BJ;
				break;
			default:
				if (is_string($raw))
					$customer->setAttribute($column, strtoupper(trim($raw)));
				return;
		}
		$customer->setAttribute($column, $normalized->value);
	}
}
