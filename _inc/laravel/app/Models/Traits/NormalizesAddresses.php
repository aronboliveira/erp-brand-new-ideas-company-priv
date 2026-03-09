<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC};
use App\Enums\{
	BrazilState,
	ChinaState,
	ContactKeyType,
	CountryName,
	PortugalState,
	UnitedStatesState
};
use App\Models\{Utility};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Support\Facades\{Log, Schema};

trait NormalizesAddresses
{
	use NormalizesArrays;

	public static function bootNormalizesAddresses(): void
	{
	    try {
    		static::saving(function (self $model) {
    			$tableName = $model->getTable();
    			Schema::hasColumn($tableName, 'phone') && $model->setAttribute(
    				'phone',
    				self::normalizePhone(
    					$model->getAttribute('phone'),
    					'phone',
    					$model->getKey(),
    					false
    				)
    			);
    			Schema::hasColumn($tableName, 'email') && $model->setAttribute(
    				'email',
    				self::normalizeEmail(
    					$model->getAttribute('email'),
    					'email',
    					$model->getKey()
    				)
    			);
    			Schema::hasColumn($tableName, BC::COL_BL_EMAIL) && $model->setAttribute(
    				BC::COL_BL_EMAIL,
    				self::normalizeEmail(
    					$model->getAttribute(BC::COL_BL_EMAIL),
    					'billing_email',
    					$model->getKey()
    				)
    			);
    			Schema::hasColumn($tableName, BC::COL_SHIP_EMAIL) && $model->setAttribute(
    				BC::COL_SHIP_EMAIL,
    				self::normalizeEmail(
    					$model->getAttribute(BC::COL_SHIP_EMAIL),
    					'shipping_email',
    					$model->getKey()
    				)
    			);
    			Schema::hasColumn($tableName, BC::COL_BL_TEL) && $model->setAttribute(
    				BC::COL_BL_TEL,
    				self::normalizePhone(
    					$model->getAttribute(BC::COL_BL_TEL),
    					'billing_phone',
    					$model->getKey(),
    					false
    				)
    			);
    			Schema::hasColumn($tableName, BC::COL_SHIP_TEL) && $model->setAttribute(
    				BC::COL_SHIP_TEL,
    				self::normalizePhone(
    					$model->getAttribute(BC::COL_SHIP_TEL),
    					'shipping_phone',
    					$model->getKey(),
    					false
    				)
    			);
    			Schema::hasColumn($tableName, BC::COL_BL_ZIP) && $model->setAttribute(
    				BC::COL_BL_ZIP,
    				self::normalizeZip(
    					$model->getAttribute(BC::COL_BL_ZIP),
    					$model->getAttribute(BC::COL_BL_CTR),
    					'billing_zip',
    					$model->getKey()
    				)
    			);
    			Schema::hasColumn($tableName, BC::COL_SHIP_ZIP) && $model->setAttribute(
    				BC::COL_SHIP_ZIP,
    				self::normalizeZip(
    					$model->getAttribute(BC::COL_SHIP_ZIP),
    					$model->getAttribute(BC::COL_SHIP_CTR),
    					'shipping_zip',
    					$model->getKey()
    				)
    			);
    			Schema::hasColumn($tableName, BC::COL_BL_CTR) && self::normalizeBillingCountry($model);
    			Schema::hasColumn($tableName, BC::COL_SHIP_CTR) && self::normalizeShippingCountry($model);
    			Schema::hasColumn($tableName, 'zip') && $model->setAttribute(
    				'zip',
    				self::normalizeZip(
    					$model->getAttribute('zip'),
    					$model->getAttribute('country'),
    					'zip',
    					$model->getKey()
    				)
    			);
    		});
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::bootNormalizesAddresses — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	public static function normalizeEmail(?string $email, string|null $context = '', string|int|null $ownerId = ''): ?string
	{
	    try {
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeEmail — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return '';
	    }
	}

	public static function normalizePhone(?string $phone, ?string $context, string|int|null $ownerId, ?bool $isMock = false): ?string
	{
	    try {
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

    		return $phone;
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizePhone — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return '';
	    }
	}

	public static function normalizeZip(?string $zip, ?string $country, ?string $context, string|int|null $ownerId): ?string
	{
	    try {
    		$zip = trim((string) $zip);
    		if ($zip === '')
    			return null;
    		if ($country === null || trim((string) $country) === '') $country = 'brazil';
    		if (empty($context)) $context = '#NO_CONTEXT';
    		if (empty($ownerId)) $ownerId = '#NO_OWNER_ID';
    		$countryNorm = strtolower(trim((string) $country));

    				if (in_array($countryNorm, ['br', 'bra', 'brazil', 'brasil'], true)) {
    			$digits = preg_replace('/\D+/', '', $zip);

    			if (strlen($digits) === 8)
    				return substr($digits, 0, 5) . '-' . substr($digits, 5);

    			if (!preg_match('/^\d{5}\-?\d{3}$/', $zip)) {
    				Log::notice("[" . self::class . "]: " . static::class . " invalid Brazilian {$context} zip", [
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeZip — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return '';
	    }
	}

	public static function normalizeContactKey(string $key): string
	{
	    try {
    		$key = mb_strtolower(trim($key));
    		$key = str_replace([' ', '-', '.', '–', '—'], '_', $key);
    		return preg_replace('/_+/', '_', $key);
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeContactKey — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return '';
	    }
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
	    try {
    		if (is_array($attr)) {
    			$attr = $this->normalizeContactArrayRecursive(
    				$attr,
    				'event.participants',
    				$ownerId
    			);
    		}

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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeContactFields — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	protected function normalizeContactArrayRecursive(
		array $data,
		string $context,
		string|int|null $ownerId
	): array {
	    try {
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeContactArrayRecursive — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}
	protected static function normalizeBillingCountry(Model $model): void
	{
	    try {
    		$billingCountryEnum = CountryName::normalize($model->getAttribute(BC::COL_BL_CTR) ?? null)
    			?? CountryName::Brazil;
    		$model->setAttribute(BC::COL_BL_CTR, $billingCountryEnum->value);
    		self::normalizeStateField($model, BC::COL_BL_ST, $billingCountryEnum);
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeBillingCountry — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	protected static function normalizeShippingCountry(Model $model): void
	{
	    try {
    		$shippingCountryEnum = CountryName::normalize($model->getAttribute(BC::COL_SHIP_CTR) ?? null)
    			?? CountryName::Brazil;
    		$model->setAttribute(BC::COL_SHIP_CTR, $shippingCountryEnum->value);
    		self::normalizeStateField($model, BC::COL_SHIP_ST, $shippingCountryEnum);
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeShippingCountry — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	protected static function normalizeStateField(self $customer, string $column, CountryName $country): void
	{
	    try {
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
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeStateField — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
