<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{DocumentKind, MimeType, UserType};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\HasOne};
use App\Models\User;

abstract class AbstractDocument extends Model
{
	use UsesUuids, HasAuditFields;

	protected $guarded = ['id', DC::COL_TABLE_CREATOR];

	protected $fillable = [
		DC::COL_FL_PT,
		'extension',
		DC::COL_MM_TP,
		'type',
		'size',
		'description',
		'notes',
		DC::COL_EXP_DT,
		DC::COL_LA,
		'viewers',
		'editors',
		'executors',
		DC::COL_PERM_RLS,
	];

	protected const ROLES_ORDER = [
		UserType::SuperAdmin->value,
		UserType::Admin->value,
		UserType::Company->value,
		UserType::Accountant->value,
		UserType::Vendor->value,
		UserType::Customer->value,
		UserType::Client->value,
	];

	protected const DEFAULT_RULES = '7776444';

	protected static function booted(): void
	{
		parent::booted();
		static::creating(function (self $m): void {
			$m->ensureMimeAndTypeFromExtension();
			$m->normalizeActorLists();
		});
		static::updating(function (self $m): void {
			if ($m->isDirty('extension') || $m->isDirty('mime_type') || $m->isDirty('type')) {
				$m->ensureMimeAndTypeFromExtension();
			}
			if (
				$m->isDirty('executors')
				|| $m->isDirty('editors')
				|| $m->isDirty('viewers')
			) {
				$m->normalizeActorLists();
			}
		});
	}

	public function user(): HasOne
	{
		return $this->hasOne(User::class, 'id', DC::COL_TABLE_CREATOR);
	}

	public function userRoleHasPermission(?string $id, string|int $type): bool
	{
		if (!is_numeric($type))
			return false;
		$mask = (int) $type; // 4=read, 2=write, 1=execute (padrão octal)
		if ($mask < 0 || $mask > 7)
			return false;
		$uid = $id ?? auth()->id();
		if (!$uid)
			return false;
		$u = User::query()->select(['id', UC::COL_TP])->find($uid);
		if (!$u)
			return false;
		$role = (string) $u->{UC::COL_TP};
		$index = array_search($role, self::ROLES_ORDER, true);
		if ($index === false)
			return false;
		$rules = str_split((string) ($this->permission_rules ?? self::DEFAULT_RULES));
		if (count($rules) < count(self::ROLES_ORDER))
			$rules = str_split(self::DEFAULT_RULES);
		$digit = (int) ($rules[$index] ?? '0');
		return (($digit & $mask) === $mask);
	}

	public function userCanExecute(?string $id): bool
	{
		return $this->userInActorsList($id, 'executors');
	}

	public function userCanEdit(?string $id): bool
	{
		return $this->userInActorsList($id, 'editors');
	}

	public function setExecutorsAttribute(array|string|null $ids): void
	{
		$this->attributes['executors'] = $this->normalizeActorsInputToString($ids, 'executors');
	}

	public function setEditorsAttribute(array|string|null $ids): void
	{
		$this->attributes['editors'] = $this->normalizeActorsInputToString($ids, 'editors');
	}

	public function setViewersAttribute(array|string|null $ids): void
	{
		$this->attributes['viewers'] = $this->normalizeActorsInputToString($ids, 'viewers');
	}

	public function userCanView(?string $id): bool
	{
		return $this->userInActorsList($id, 'viewers');
	}

	public function setRolePermission(string $role, int $permission): void
	{
		$idx = array_search($role, self::ROLES_ORDER, true);
		if ($idx === false || $permission < 0 || $permission > 7)
			return;
		$rules = str_split((string) ($this->permission_rules ?? self::DEFAULT_RULES));
		if (count($rules) < count(self::ROLES_ORDER))
			$rules = str_split(self::DEFAULT_RULES);
		$rules[$idx] = (string) $permission;
		$this->permission_rules = implode('', $rules);
	}

	public function ensureMimeAndTypeFromExtension(): void
	{
		$ext = strtolower((string) ($this->extension ?? ''));
		if ($ext === '')
			return;
		$mime = MimeType::fromExtension($ext);
		if ($mime && !$this->mime_type instanceof MimeType)
			$this->mime_type = $mime;
		if (!$this->type instanceof DocumentKind) {
			$kind = DocumentKind::fromExtension($ext);
			if ($kind)
				$this->type = $kind;
		}
	}

	protected function userInActorsList(?string $id, string $column): bool
	{
		$uid = $id ?? auth()->id();
		if (!$uid)
			return false;
		$list = $this->getNormalizedActorsAsArray($column);
		return in_array($uid, $list, true);
	}

	protected function getNormalizedActorsAsArray(string $column): array
	{
		$raw = $this->getAttribute($column);
		if ($raw === null)
			return [];
		if (is_array($raw)) {
			$out = [];
			foreach ($raw as $item) {
				if (is_string($item)) {
					$id = trim($item);
					if ($id !== '')
						$out[$id] = true;
				}
			}
			return array_keys($out);
		}

		if (is_string($raw)) {
			$raw = trim($raw);
			if ($raw === '')
				return [];
			if (str_starts_with($raw, '[') || str_starts_with($raw, '{')) {
				$decoded = json_decode($raw, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					$items = [];
					if (is_array($decoded)) {
						$isAssoc = array_keys($decoded) !== range(0, count($decoded) - 1);
						$items = $isAssoc ? [$decoded] : $decoded;
					} else
						$items = [$decoded];
					$ids = [];
					foreach ($items as $item) {
						if (is_string($item)) {
							$id = trim($item);
							if ($id !== '')
								$ids[$id] = true;
						} elseif (is_array($item)) {
							$id = $this->extractActorIdFromArray($item, $column);
							if ($id !== null)
								$ids[$id] = true;
						}
					}

					return array_keys($ids);
				}
			}

			$parts = array_filter(array_map('trim', explode(',', $raw)));
			$ids = [];
			foreach ($parts as $id) {
				if ($id !== '')
					$ids[$id] = true;
			}
			return array_keys($ids);
		}

		return [];
	}

	protected function normalizeActorsInputToString(array|string|null $ids, string $column): ?string
	{
		if ($ids === null)
			return null;
		if (is_string($ids)) {
			$ids = trim($ids);
			return $ids === '' ? null : $ids;
		}

		$candidates = [];
		foreach ($ids as $item) {
			if (is_string($item)) {
				$id = trim($item);
				if ($id !== '')
					$candidates[$id] = true;
			} elseif (is_array($item)) {
				$id = $this->extractActorIdFromArray($item, $column);
				if ($id !== null)
					$candidates[$id] = true;
			}
		}
		if ($candidates === [])
			return null;
		return implode(',', array_keys($candidates));
	}

	/**
	 * Filtra executors/editors/viewers garantindo que:
	 * - IDs sejam extraídos de objetos JSON ('id', '<singular>_id', 'user_id');
	 * - apenas usuários existentes sejam mantidos;
	 * - resultado final seja persistido como CSV "id1,id2,...".
	 */
	protected function normalizeActorLists(): void
	{
		$columns = ['executors', 'editors', 'viewers'];
		$allIds = [];
		$parsed = [];
		foreach ($columns as $column) {
			$raw = $this->getAttribute($column);
			$items = $this->parseActorRawItems($raw, $column);
			$ids = [];
			foreach ($items as $item) {
				$id = $this->extractActorIdFromMixed($item, $column);
				if ($id !== null) {
					$ids[] = $id;
					$allIds[$id] = true;
				}
			}
			$parsed[$column] = $ids;
		}

		if ($allIds === []) {
			foreach ($columns as $column)
				$this->setAttribute($column, null);
			return;
		}
		$validIds = User::query()
			->whereIn('id', array_keys($allIds))
			->pluck('id')
			->all();
		$validSet = array_flip($validIds);
		foreach ($columns as $column) {
			$final = [];
			foreach ($parsed[$column] as $id)
				if (isset($validSet[$id]))
					$final[$id] = true;
			$this->setAttribute(
				$column,
				$final ? implode(',', array_keys($final)) : null
			);
		}
	}

	protected function parseActorRawItems(mixed $raw, string $column): array
	{
		if ($raw === null)
			return [];
		if (is_array($raw))
			return $raw;
		if (is_string($raw)) {
			$raw = trim($raw);
			if ($raw === '')
				return [];
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
			$parts = array_filter(array_map('trim', explode(',', $raw)));
			return $parts;
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

	/**
	 * Extrai ID de array/objeto usando:
	 * - 'id'
	 * - '<singular>_id' (executor_id, editor_id, viewer_id)
	 * - 'user_id'
	 */
	protected function extractActorIdFromArray(array $data, string $column): ?string
	{
		$candidates = ['id'];
		$singular = rtrim($column, 's');
		if ($singular !== '')
			$candidates[] = $singular . '_id';
		$candidates[] = 'user_id';
		foreach ($candidates as $key) {
			if (!empty($data[$key]) && is_string($data[$key])) {
				$id = trim($data[$key]);
				if ($id !== '')
					return $id;
			}
		}
		return null;
	}
}
