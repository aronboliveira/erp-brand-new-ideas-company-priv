<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{MimeType, UserType};
use App\Traits\{DefinesDates, FiltersSecureAttachments, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractFile extends Model
{
	use UsesUuids, HasAuditFields, FiltersSecureAttachments, DefinesDates;

	/**
	 * Ordem e máscara de permissão (octal-like: 4=read, 2=write, 1=execute).
	 * O default do schema é '776444' (6 dígitos), então a ordem aqui deve ser 6 roles.
	 * Interpretação prática: COMPANY, ADMIN, ACCOUNTANT, CLIENT, VENDOR, CUSTOMER.
	 */
	protected const ROLES_ORDER = [
		UserType::Company->value,
		UserType::Admin->value,
		UserType::Accountant->value,
		UserType::Client->value,
		UserType::Vendor->value,
		UserType::Customer->value,
	];

	protected const DEFAULT_RULES = '776444';

	protected const ABSTRACT_FILE_FILLABLE = [
		DC::COL_FL_PT,      // file_path
		'url',
		'name',
		'extension',
		DC::COL_MM_TP,      // mime_type
		DC::COL_LA,         // last_accessed
		'size',
		'description',
		'notes',
		DC::COL_DL_CT,      // download_count
		DC::COL_FL_SZ,      // file_size (float)
		DC::COL_PERM_RLS,   // permission_rules
		'viewers',
		'editors',
		'executors',
		DC::COL_EXP_DT,     // expiration_date
		'type',
	];

	protected $fillable = [...self::ABSTRACT_FILE_FILLABLE];

	/**
	 * Return the base set of fillable fields for child classes to extend.
	 * @return array<int, string>
	 */
	protected static function fillableFields(): array
	{
		return self::ABSTRACT_FILE_FILLABLE;
	}

	protected const ABSTRACT_FILE_GUARDED = ['id', DC::COL_TABLE_CREATOR];

	protected $guarded = [...self::ABSTRACT_FILE_GUARDED];

	protected const ABSTRACT_FILE_CASTS = [
		'size'         => 'integer',
		DC::COL_MM_TP  => MimeType::class,
		DC::COL_LA     => 'datetime',
		DC::COL_EXP_DT => 'datetime',
		DC::COL_DL_CT  => 'integer',
		DC::COL_FL_SZ  => 'float',
		'viewers'      => 'array',
		'editors'      => 'array',
		'executors'    => 'array',
	];

	protected $casts = [...self::ABSTRACT_FILE_CASTS];

	protected const ABSTRACT_FILE_APPENDS = [
		'is_document',
		'is_expired',
	];

	protected $appends = [...self::ABSTRACT_FILE_APPENDS];

	protected static array $userRoleCache = [];

	protected static function booted(): void
	{
		parent::booted();
		// TODO use in product, too heavy for mocks
		// static::saving(function (self $m): void {
		// 	try {
		// 		$m->normalizeFileFields();
		// 		$m->ensureMimeFromExtension();
		// 		$m->enforceTypeNullWhenNotDocument();
		// 		$m->normalizePermissionRules();
		// 		$m->normalizeActorListsIfDirty();
		// 		$basePath = trim((string) ($m->getAttribute('url') ?? ''));
		// 		if ($basePath === '') {
		// 			$title = trim((string) ($m->getAttribute('title') ?? $m->getAttribute('name') ?? ''));
		// 			$basePath = $title !== '' ? Str::slug($title) : 'resource-' . now()->timestamp;
		// 		}
		// 		$basePath = trim(parse_url($basePath, PHP_URL_PATH) ?? $basePath, '/');
		// 		$basePath = Str::slug($basePath);
		// 		$candidatePath = '/' . $basePath;
		// 		if (DB::table($m->getTable())
		// 			->where('url', $candidatePath)
		// 			->where('id', '!=', $m->getAttribute('id') ?? '')
		// 			->exists()
		// 		) {
		// 			$acc = 0;
		// 			$maxAttempts = 1000;
		// 			do {
		// 				$suffix = now()->timestamp . '-' . Str::random(6);
		// 				$candidatePath = '/' . $basePath . '-' . $suffix;
		// 				$acc++;
		// 				if ($acc > $maxAttempts) {
		// 					throw new \RuntimeException(
		// 						'Failed to generate unique URL path for ' . get_class($m) . ' after ' . $maxAttempts . ' attempts'
		// 					);
		// 				}
		// 			} while (DB::table($m->getTable())
		// 				->where('url', $candidatePath)
		// 				->where('id', '!=', $m->getAttribute('id') ?? '')
		// 				->exists()
		// 			);
		// 		}
		// 		$m->setAttribute('url', $candidatePath);
		// 	} catch (Throwable $e) {
		// 		Log::warning(static::class . ' saving normalization failed', [
		// 			'id'    => $m->getAttribute('id'),
		// 			'error' => $e->getMessage(),
		// 			'line' => $e->getLine(),
		// 			'file' => $e->getFile(),
		// 		]);
		// 	}
		// });
	}

	protected function normalizeFileFields(): void
	{
		$path = $this->getAttribute(DC::COL_FL_PT);
		if (is_string($path)) {
			$path = trim(str_replace("\0", '', $path));
			$path = preg_replace('/[\x00-\x1F\x7F]/u', '', $path) ?? $path;
			$path = str_replace(['..\\', '../', '..'], '', $path);
			$this->setAttribute(DC::COL_FL_PT, $path === '' ? null : $path);
		}

		$name = $this->getAttribute('name');
		if (is_string($name)) {
			$name = trim($name);
			$name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? $name;
			$name = mb_substr($name, 0, 1024);
			$this->setAttribute('name', $name === '' ? null : $name);
		}

		if (!$this->getAttribute('name')) {
			$this->setAttribute('name', 'FILE_' . (string) Str::uuid() . '_' . now()->timestamp);
		}

		$ext = $this->getAttribute('extension');
		if (is_string($ext)) {
			$ext = strtolower(ltrim(trim($ext), '.'));
			$ext = preg_replace('/[^a-z0-9]+/', '', $ext) ?? $ext;
			$this->setAttribute('extension', $ext === '' ? null : $ext);
		}

		foreach ([DC::COL_DL_CT, DC::COL_FL_SZ, 'size'] as $col) {
			$v = $this->getAttribute($col);
			if ($v === null || !is_numeric($v)) continue;

			$n = (float) $v;
			if ($n < 0) $n = 0;

			$this->setAttribute($col, in_array($col, [DC::COL_DL_CT, 'size'], true) ? (int) $n : $n);
		}
	}

	protected function ensureMimeFromExtension(): void
	{
		$ext = (string) ($this->getAttribute('extension') ?? '');
		if ($ext === '') {
			$path = (string) ($this->getAttribute(DC::COL_FL_PT) ?? '');
			if ($path !== '') {
				$pi = pathinfo($path);
				$guess = strtolower((string) ($pi['extension'] ?? ''));
				$guess = preg_replace('/[^a-z0-9]+/', '', $guess) ?? $guess;
				if ($guess !== '') {
					$this->setAttribute('extension', $guess);
					$ext = $guess;
				}
			}
		}

		$raw = $this->getAttribute(DC::COL_MM_TP);
		$mime = $raw instanceof MimeType
			? $raw
			: (is_string($raw) ? MimeType::normalize($raw) : null);

		if (($mime === null || $mime === MimeType::OTHER) && $ext !== '') {
			$fromExt = MimeType::fromExtension($ext) ?? MimeType::OTHER;
			$this->setAttribute(DC::COL_MM_TP, $fromExt->value);
			return;
		}

		if ($mime === null)
			$this->setAttribute(DC::COL_MM_TP, MimeType::OTHER->value);
		elseif ($mime instanceof MimeType)
			$this->setAttribute(DC::COL_MM_TP, $mime->value);
	}

	protected function enforceTypeNullWhenNotDocument(): void
	{
		$raw = $this->getAttribute(DC::COL_MM_TP);

		$mime = $raw instanceof MimeType
			? $raw
			: (is_string($raw) ? MimeType::normalize($raw) : null);

		if (!$mime || !$mime->isDocument()) {
			$this->setAttribute('type', null);
			return;
		}

		// é documento: AbstractFile NÃO define o "type"; apenas preserva o que vier.
		$type = $this->getAttribute('type');
		if (is_string($type) && trim($type) === '')
			$this->setAttribute('type', null);
	}

	protected function normalizePermissionRules(): void
	{
		$needed = count(static::ROLES_ORDER);

		$raw = (string) ($this->getAttribute(DC::COL_PERM_RLS) ?? '');
		$raw = preg_replace('/\D+/', '', $raw) ?? '';
		$digits = str_split($raw);

		if (count($digits) !== $needed)
			$digits = str_split(static::DEFAULT_RULES);

		for ($i = 0; $i < $needed; $i++) {
			$d = (int) ($digits[$i] ?? 0);
			if ($d < 0 || $d > 7) $digits[$i] = '0';
		}

		$this->setAttribute(DC::COL_PERM_RLS, implode('', $digits));
	}

	protected function normalizeActorListsIfDirty(): void
	{
		if (
			!$this->isDirty('executors')
			&& !$this->isDirty('editors')
			&& !$this->isDirty('viewers')
		) return;

		$this->normalizeActorLists();
	}

	protected function normalizeActorLists(): void
	{
		$columns = ['executors', 'editors', 'viewers'];

		$allIds = [];
		$parsed = [];

		foreach ($columns as $column) {
			$raw = $this->getAttribute($column);

			if ($raw === null || $raw === '') {
				$parsed[$column] = [];
				continue;
			}

			if (is_array($raw)) {
				$ids = array_values(array_filter(array_map(function ($item) use ($column) {
					return $this->extractActorIdFromMixed($item, $column);
				}, $raw)));

				$parsed[$column] = $ids;
				foreach ($ids as $id) {
					$allIds[$id] = true;
				}
				continue;
			}

			// Fallback for string input (CSV or JSON)
			$items = $this->parseActorRawItems($raw);
			$ids = [];
			foreach ($items as $item) {
				$id = $this->extractActorIdFromMixed($item, $column);
				if ($id === null) continue;
				$ids[] = $id;
				$allIds[$id] = true;
			}
			$parsed[$column] = $ids;
		}

		if (empty($allIds)) {
			foreach ($columns as $column) {
				$this->setAttribute($column, []);
			}
			return;
		}

		$validIds = User::query()
			->whereIn('id', array_keys($allIds))
			->pluck('id')
			->all();

		$validSet = array_flip($validIds);

		foreach ($columns as $column) {
			$final = [];
			foreach ($parsed[$column] as $id) {
				if (isset($validSet[$id])) {
					$final[$id] = true;
				}
			}
			$this->setAttribute($column, empty($final) ? [] : array_values(array_keys($final)));
		}
	}
	protected function parseActorRawItems(mixed $raw): array
	{
		if ($raw === null) return [];
		if (is_array($raw)) return $raw;

		if (is_string($raw)) {
			$raw = trim($raw);
			if ($raw === '') return [];

			if (str_starts_with($raw, '[') || str_starts_with($raw, '{')) {
				$decoded = json_decode($raw, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					if (is_array($decoded)) {
						$isAssoc = array_keys($decoded) !== range(0, count($decoded) - 1);
						return $isAssoc ? [$decoded] : $decoded;
					}
					return [$decoded];
				}
			}

			return array_filter(array_map('trim', explode(',', $raw)));
		}

		return [];
	}

	protected function extractActorIdFromMixed(mixed $item, string $column): ?string
	{
		if (is_string($item)) {
			$id = trim($item);
			return $id === '' ? null : $id;
		}

		if (is_array($item))
			return $this->extractActorIdFromArray($item, $column);

		if (is_object($item))
			return $this->extractActorIdFromArray((array) $item, $column);

		return null;
	}

	protected function extractActorIdFromArray(array $data, string $column): ?string
	{
		$candidates = ['id'];

		$singular = rtrim($column, 's');
		if ($singular !== '')
			$candidates[] = $singular . '_id';

		$candidates[] = 'user_id';

		foreach ($candidates as $key) {
			if (empty($data[$key]) || !is_string($data[$key])) continue;
			$id = trim($data[$key]);
			if ($id !== '') return $id;
		}

		return null;
	}

	public function getIsDocumentAttribute(): bool
	{
		$raw = $this->getAttribute(DC::COL_MM_TP);

		$mime = $raw instanceof MimeType
			? $raw
			: (is_string($raw) ? MimeType::normalize($raw) : null);

		return (bool) ($mime && $mime->isDocument());
	}

	public function getIsExpiredAttribute(): bool
	{
		$exp = $this->getAttribute(DC::COL_EXP_DT);
		if (!$exp) return false;

		try {
			$dt = $exp instanceof \DateTimeInterface
				? \Illuminate\Support\Carbon::instance($exp)
				: \Illuminate\Support\Carbon::parse((string) $exp);

			return $dt->isPast();
		} catch (Throwable) {
			return false;
		}
	}

	public function userRoleHasPermission(?string $id, string|int $type): bool
	{
		if (!is_numeric($type)) return false;

		$mask = (int) $type;
		if ($mask < 0 || $mask > 7) return false;

		$uid = $id ?? auth()->id();
		if (!$uid) return false;

		$role = $this->getCachedUserRole($uid);
		if ($role === null) return false;

		$index = array_search($role, static::ROLES_ORDER, true);
		if ($index === false) return false;

		$rules = str_split((string) ($this->getAttribute(DC::COL_PERM_RLS) ?? static::DEFAULT_RULES));
		if (count($rules) !== count(static::ROLES_ORDER))
			$rules = str_split(static::DEFAULT_RULES);

		$digit = (int) ($rules[$index] ?? '0');
		return (($digit & $mask) === $mask);
	}

	protected function getCachedUserRole(string $userId): ?string
	{
		if (array_key_exists($userId, self::$userRoleCache))
			return self::$userRoleCache[$userId];

		$u = User::query()->select(['id', UC::COL_TP])->find($userId);
		if (!$u) return self::$userRoleCache[$userId] = null;

		return self::$userRoleCache[$userId] = (string) $u->getAttribute(UC::COL_TP);
	}
}
