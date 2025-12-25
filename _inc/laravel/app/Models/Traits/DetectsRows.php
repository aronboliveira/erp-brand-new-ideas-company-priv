<?php

namespace App\Traits;

use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

trait DetectsRows
{
	protected array $runtimeCache = [];

	protected function detectNameColumn(string $table): ?string
	{
		foreach (['name', 'title', 'label'] as $c)
			if (Schema::hasColumn($table, $c)) return $c;
		return null;
	}

	protected function rowByToken(array $indexed, string $token, ?string $nameCol): ?object
	{
		$s = trim((string) $token);
		if ($s === '') return null;

		if ($this->looksLikeUuid($s)) return $indexed['by_id'][$s] ?? null;
		if ($nameCol === null) return null;

		$key = strtoupper(Str::ascii($s));
		return $indexed['by_name'][$key] ?? null;
	}

	protected function resolveRowsByIdOrNameCached(string $table, array $tokens, array $select, ?string $nameCol, array $extraWhere): ?array
	{
		$key = 'rows:' . $table . ':' . md5(json_encode([$tokens, $select, $nameCol, $extraWhere], JSON_UNESCAPED_UNICODE));
		return $this->cacheOnce($key, function () use ($table, $tokens, $select, $nameCol, $extraWhere): ?array {
			$ids = [];
			$names = [];

			foreach ($tokens as $t) {
				$s = trim((string) $t);
				if ($s === '') continue;

				if ($this->looksLikeUuid($s)) $ids[] = $s;
				elseif ($nameCol !== null) $names[] = $s;
			}

			$ids = array_values(array_unique($ids));
			$names = array_values(array_unique($names));
			if (!$ids && !$names) return null;

			try {
				$q = DB::table($table)->select(array_values(array_unique($select)));

				foreach ($extraWhere as $col => $val)
					if (Schema::hasColumn($table, (string) $col)) $q->where((string) $col, $val);

				$q->where(function ($w) use ($ids, $names, $nameCol) {
					if ($ids) $w->whereIn('id', $ids);
					if ($names && $nameCol !== null) {
						if ($ids) $w->orWhereIn($nameCol, $names);
						else $w->whereIn($nameCol, $names);
					}
				});

				$rows = $q->get();
			} catch (\Throwable $e) {
				Log::warning("Holiday scope: failed fetching rows from {$table}", [
					'error' => $e->getMessage(),
				]);
				return null;
			}

			$indexed = [
				'by_id' => [],
				'by_name' => [],
			];

			foreach ($rows as $r) {
				$id = is_scalar($r->id ?? null) ? (string) $r->id : '';
				if ($id !== '') $indexed['by_id'][$id] = $r;

				if ($nameCol !== null) {
					$nm = is_scalar($r->{$nameCol} ?? null) ? (string) $r->{$nameCol} : '';
					$keyName = strtoupper(Str::ascii(trim($nm)));
					if ($keyName !== '') $indexed['by_name'][$keyName] = $r;
				}
			}

			return $indexed;
		});
	}

	protected function looksLikeUuid(string $value): bool
	{
		return (bool) preg_match('/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$/', $value);
	}

	protected function cacheOnce(string $key, callable $cb): mixed
	{
		if (array_key_exists($key, $this->runtimeCache)) return $this->runtimeCache[$key];
		$this->runtimeCache[$key] = $cb();
		return $this->runtimeCache[$key];
	}
}
