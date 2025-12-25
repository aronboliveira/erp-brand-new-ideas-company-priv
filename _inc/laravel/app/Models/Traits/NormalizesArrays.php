<?php

namespace App\Traits;

trait NormalizesArrays
{
	public static function normalizeArrayField(mixed $value): array
	{
		if ($value === null)
			return [];

		if (is_string($value)) {
			$decoded = json_decode($value, true);
			return is_array($decoded) ? $decoded : [];
		}

		return is_array($value) ? $value : (array) $value;
	}

	/**
	 * Garante json_encode consistente, aceitando array/objeto/string pré-JSON.
	 */
	public static function encodeJsonValue(mixed $value, string $key): ?string
	{
		if ($value === null || $value === '')
			return null;
		if (is_string($value)) {
			$trimmed = trim($value);
			if ($trimmed === '')
				return null;
			if (self::looksLikeJson($trimmed))
				return $trimmed;
			$value = [$trimmed];
		}

		if (!is_array($value) && !is_object($value))
			$value = [$value];
		try {
			return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
		} catch (\Throwable) {
			// fallback defensivo: tenta ao menos serializar cast para array
			return json_encode((array) $value);
		}
	}

	/**
	 * Verifica se uma string parece JSON válido.
	 */
	public static function looksLikeJson(string $value): bool
	{
		$first = $value[0] ?? '';
		if ($first !== '{' && $first !== '[')
			return false;
		try {
			json_decode($value, true, 512, JSON_THROW_ON_ERROR);
			return true;
		} catch (\Throwable) {
			return false;
		}
	}

	/**
	 * Helper central para codificar atributos JSON.
	 * Armazena SEMPRE uma string JSON ou null em self::attributes[$key].
	 */
	protected function encodeJsonAttribute(string $key, mixed $value): void
	{
		$this->attributes[$key] = self::encodeJsonValue($value, $key);
	}

	/**
	 * Garante que, mesmo que alguém tenha injetado array diretamente em attributes,
	 * os campos JSON serão convertidos para string JSON antes de persistir.
	 */
	protected function ensureJsonAttributesAreEncoded($jsonFields): void
	{
		foreach ($jsonFields as $field) {
			if (!array_key_exists($field, $this->attributes)) {
				continue;
			}

			$current = $this->attributes[$field];

			if (is_array($current) || is_object($current)) {
				self::encodeJsonAttribute($field, $current);
			} elseif (is_string($current) && $current !== '' && !self::looksLikeJson($current)) {
				self::encodeJsonAttribute($field, $current);
			}
		}
	}

	protected function normalizeStringList(mixed $value): ?array
	{
		$arr = self::normalizeArrayField($value);
		$out = [];
		foreach ($arr as $v) {
			if (!is_scalar($v)) continue;
			$s = trim((string) $v);
			if ($s === '') continue;
			$out[] = $s;
		}
		$out = array_values(array_unique($out));
		return $out ?: null;
	}
}
