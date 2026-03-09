<?php

namespace App\Models;

use ReflectionClass;
use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{AppModuleType};
use App\Helpers\{AppModuleTypeCast};
use App\Models\{Utility};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Support\Facades\{DB, Log, Schema};

class BasicFavorite extends Model
{
	use UsesUuids, HasAuditFields;

	protected $table = DC::TABLE_BSC_FV;

	protected $guarded = ['id', DC::COL_TABLE_CREATOR];

	protected $fillable = [
		'module',
		AC::COL_FV_TB,
		AC::COL_FV_ID,
		UC::COL_USER_ID,
		'notes',
	];

	protected $casts = [
		'module' => AppModuleTypeCast::class,
	];

	private static ?array $allowedTablesCache = null;

	protected static function booted(): void
	{
		static::saving(function (Model $m): void {
			if (!$m instanceof self) return;

			try {
				$module = $m->getAttribute('module');
				$module = $module instanceof AppModuleType ? $module->value : (empty($module) ? AppModuleType::Other->value : (in_array($module, array_column(AppModuleType::cases(), 'value'), true) ? (string) $module : AppModuleType::Other->value));
				$moduleEnum = null;
				try {
					$moduleEnum = AppModuleType::tryFrom($module) ?? AppModuleType::Other;
				} catch (\Throwable) {
					$moduleEnum = AppModuleType::Other;
				}
				$m->setAttribute('module', $moduleEnum->value);

				$tb = $m->getAttribute(AC::COL_FV_TB);
				$tb = trim((string) $tb);
				if ($tb === '') $tb = DC::TABLE_NOTES;

				$allowedTables = self::allowedTables();
				if (!in_array($tb, $allowedTables, true) || !Schema::hasTable($tb))
					$tb = DC::TABLE_NOTES;

				$m->setAttribute(AC::COL_FV_TB, $tb);

				$favId = $m->getAttribute(AC::COL_FV_ID);
				$favId = is_scalar($favId) ? trim((string) $favId) : '';
				if (empty($favId))
					throw new \RuntimeException('BasicFavorite favorite_id cannot be empty');

				$userId = $m->getAttribute(UC::COL_USER_ID);
				$userId = is_scalar($userId) ? trim((string) $userId) : '';
				if ($userId === '' || !Utility::looksLikeUuid($userId)) {
					$m->setAttribute(UC::COL_USER_ID, null);
					return;
				}

				if (Schema::hasTable(DC::TABLE_USERS) && Schema::hasColumn(DC::TABLE_USERS, 'id')) {
					$okUser = (bool) DB::selectOne('SELECT 1 FROM ' . DC::TABLE_USERS . ' WHERE id = ? LIMIT 1', [$userId]);
					if (!$okUser) throw new \RuntimeException('BasicFavorite invalid user_id: ' . $userId);
				}

				if (Schema::hasTable($tb) && Schema::hasColumn($tb, 'id')) {
					$okTarget = (bool) DB::selectOne('SELECT 1 FROM ' . $tb . ' WHERE id = ? LIMIT 1', [$favId]);
					if (!$okTarget) throw new \RuntimeException('BasicFavorite invalid favorite_id: ' . $favId);
				}

				$selfId = (string) ($m->getKey() ?? '');
				$dup = DB::selectOne(
					'SELECT id FROM ' . DC::TABLE_BSC_FV . ' WHERE ' . AC::COL_FV_ID . ' = ? LIMIT 1',
					[$favId]
				);
				if ($dup && (string) ($dup->id ?? '') !== '' && (string) ($dup->id ?? '') !== $selfId)
					throw new \RuntimeException('BasicFavorite duplicate favorite_id: ' . $favId);
			} catch (\Throwable $e) {
				Log::error(static::class . ' failed normalizing BasicFavorite', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'model_id' => $m->getKey(),
					'table' => $m->getTable(),
				]);
			}
		});
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
	}

	private static function allowedTables(): array
	{
	    try {
    		if (self::$allowedTablesCache !== null)
    			return self::$allowedTablesCache;

    		$out = [];
    		try {
    			$ref = new ReflectionClass(DC::class);
    			foreach ($ref->getConstants() as $k => $v) {
    				if (!is_string($k) || !is_string($v)) continue;
    				if (!str_starts_with($k, 'TABLE_')) continue;
    				$vv = trim($v);
    				if ($vv === '') continue;
    				$out[] = $vv;
    			}
    		} catch (\Throwable) {
    			$out = [DC::TABLE_NOTES];
    		}

    		$out = array_values(array_unique($out));
    		self::$allowedTablesCache = $out;
    		return $out;
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::allowedTables — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}
}
