<?php

namespace App\Traits;

use App\Config\Constants\DatabaseConstants as DC;
use App\Helpers\ErrorHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Log, Schema};

/**
 * ExtendsBaseTable trait
 * 
 * Provides functionality to synchronize overlapping columns between an extender model
 * and a base table, with automatic type compatibility checking and bidirectional updates.
 */
trait ExtendsBaseTable
{
	protected string $baseTableName = '';
	protected array $extendsBaseTableErrors = [];
	protected static array $extendsBaseTableColumnCache = [];
	protected static array $extendsBaseTableOverlapCache = [];
	protected const BASE_TABLE_EXCLUDED_COLUMNS = [
		'id',
		'created_by',
		'updated_by',
		'created_at',
		'updated_at',
		'deleted_at',

		'description',
		'metadata',
		'notes',
	];

	protected static function getExtendsBaseTableExcludedColumns(): array
	{
		return [
			...self::BASE_TABLE_EXCLUDED_COLUMNS,
		];
	}

	/**
	 * Initialize base table extension
	 * 
	 * @param string $baseTable The base table name to extend
	 * @param string $foreignKeyColumn The foreign key column name in the extender table
	 * @return bool Success status
	 */
	protected function setupExtendsBaseTable(string $baseTable, string $foreignKeyColumn): bool
	{
		try {
			if (!Schema::hasTable($baseTable)) {
				ErrorHandler::evaluateExistenceToLogChannel(
					'extends_base_table_errors',
					candidate: [
						'message' => 'ExtendsBaseTable::setupExtendsBaseTable - Base table does not exist',
						'context' => [
							'trait' => 'ExtendsBaseTable',
							'class' => static::class,
							'method' => __METHOD__,
							'line' => __LINE__,
							'base_table' => $baseTable
						]
					],
					mainChannel: 'error'
				);
				return false;
			}
			$extenderTable = $this->getTable();
			if (!Schema::hasTable($extenderTable)) {
				ErrorHandler::evaluateExistenceToLogChannel(
					'extends_base_table_errors',
					candidate: [
						'message' => 'ExtendsBaseTable::setupExtendsBaseTable - Extender table does not exist',
						'context' => [
							'trait' => 'ExtendsBaseTable',
							'class' => static::class,
							'method' => __METHOD__,
							'line' => __LINE__,
							'extender_table' => $extenderTable
						]
					],
					mainChannel: 'error'
				);
				return false;
			}
			$this->cacheExtendsBaseTableColumns($baseTable, $extenderTable);
			$this->cacheExtendsBaseTableOverlappingColumns($baseTable, $extenderTable);
			Log::debug('ExtendsBaseTable::setupExtendsBaseTable - Successfully initialized', [
				'trait' => 'ExtendsBaseTable',
				'class' => static::class,
				'base_table' => $baseTable,
				'extender_table' => $extenderTable,
				'overlapping_columns' => count($this->getExtendsBaseTableOverlappingColumns($baseTable, $extenderTable)),
				'foreign_key_column' => $foreignKeyColumn
			]);
			return true;
		} catch (\Throwable $e) {
			ErrorHandler::evaluateExistenceToLogChannel(
				'extends_base_table_errors',
				candidate: [
					'message' => 'ExtendsBaseTable::setupExtendsBaseTable - Failed to initialize',
					'context' => [
						'trait' => 'ExtendsBaseTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__,
						'base_table' => $baseTable,
						'error' => $e->getMessage(),
						'foreign_key_column' => $foreignKeyColumn
					]
				],
				mainChannel: 'error'
			);
			return false;
		}
	}

	/**
	 * Cache columns for base and extender tables
	 * 
	 * @param string $baseTable
	 * @param string $extenderTable
	 */
	protected function cacheExtendsBaseTableColumns(string $baseTable, string $extenderTable): void
	{
		try {
			if (!isset(static::$extendsBaseTableColumnCache[$baseTable])) {
				$baseColumns = [];
				foreach (Schema::getColumnListing($baseTable) as $column) {
					try {
						$baseColumns[$column] = Schema::getColumnType($baseTable, $column);
					} catch (\Throwable $e) {
						if (str_contains($e->getMessage(), 'Unknown database type enum requested')) {
							$baseColumns[$column] = 'string';
							Log::debug('cacheExtendsBaseTableColumns: failed type inspection; defaulted to string', [
								'table' => $baseTable,
								'column' => $column,
								'error' => $e->getMessage(),
							]);
						}
					}
				}
				static::$extendsBaseTableColumnCache[$baseTable] = $baseColumns;
			}

			if (!isset(static::$extendsBaseTableColumnCache[$extenderTable])) {
				$extenderColumns = [];
				foreach (Schema::getColumnListing($extenderTable) as $column) {
					try {
						$extenderColumns[$column] = Schema::getColumnType($extenderTable, $column);
					} catch (\Throwable $e) {
						if (str_contains($e->getMessage(), 'Unknown database type enum requested')) {
							$extenderColumns[$column] = 'string';
							Log::debug('cacheExtendsBaseTableColumns: failed type inspection; defaulted to string', [
								'table' => $extenderTable,
								'column' => $column,
								'error' => $e->getMessage(),
							]);
						}
					}
				}
				static::$extendsBaseTableColumnCache[$extenderTable] = $extenderColumns;
			}
			Log::debug('ExtendsBaseTable::cacheExtendsBaseTableColumns - Columns cached', [
				'trait' => 'ExtendsBaseTable',
				'class' => static::class,
				'base_table' => $baseTable,
				'base_columns_count' => count(static::$extendsBaseTableColumnCache[$baseTable]),
				'extender_table' => $extenderTable,
				'extender_columns_count' => count(static::$extendsBaseTableColumnCache[$extenderTable])
			]);
		} catch (\Throwable $e) {
			ErrorHandler::evaluateExistenceToLogChannel(
				'extends_base_table_errors',
				candidate: [
					'message' => 'ExtendsBaseTable::cacheExtendsBaseTableColumns - Failed to cache columns',
					'context' => [
						'trait' => 'ExtendsBaseTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__,
						'base_table' => $baseTable,
						'extender_table' => $extenderTable,
						'error' => $e->getMessage()
					]
				],
				mainChannel: 'error'
			);
		}
	}

	/**
	 * Cache overlapping compatible columns between base and extender tables
	 * 
	 * @param string $baseTable
	 * @param string $extenderTable
	 */
	protected function cacheExtendsBaseTableOverlappingColumns(string $baseTable, string $extenderTable): void
	{
		try {
			$cacheKey = "{$baseTable}:{$extenderTable}";
			if (isset(static::$extendsBaseTableOverlapCache[$cacheKey])) return;
			$baseColumns = static::$extendsBaseTableColumnCache[$baseTable] ?? [];
			$extenderColumns = static::$extendsBaseTableColumnCache[$extenderTable] ?? [];
			$overlapping = [];
			foreach ($extenderColumns as $columnName => $extenderType) {
				if (!isset($baseColumns[$columnName])) continue;
				if (in_array($columnName, static::getExtendsBaseTableExcludedColumns())) continue;
				$baseType = $baseColumns[$columnName];
				if ($this->areExtendsBaseTableTypesCompatible($baseType, $extenderType))
					$overlapping[$columnName] = ['base_type' => $baseType, 'extender_type' => $extenderType];
			}
			static::$extendsBaseTableOverlapCache[$cacheKey] = $overlapping;
			Log::debug('ExtendsBaseTable::cacheExtendsBaseTableOverlappingColumns - Overlapping columns cached', [
				'trait' => 'ExtendsBaseTable',
				'class' => static::class,
				'base_table' => $baseTable,
				'extender_table' => $extenderTable,
				'overlapping_count' => count($overlapping),
				'columns' => array_keys($overlapping)
			]);
		} catch (\Throwable $e) {
			ErrorHandler::evaluateExistenceToLogChannel(
				'extends_base_table_errors',
				candidate: [
					'message' => 'ExtendsBaseTable::cacheExtendsBaseTableOverlappingColumns - Failed to cache overlapping columns',
					'context' => [
						'trait' => 'ExtendsBaseTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__,
						'base_table' => $baseTable,
						'extender_table' => $extenderTable,
						'error' => $e->getMessage()
					]
				],
				mainChannel: 'error'
			);
		}
	}

	/**
	 * Check if two column types are compatible
	 * 
	 * @param string $baseType
	 * @param string $extenderType
	 * @return bool
	 */
	protected function areExtendsBaseTableTypesCompatible(string $baseType, string $extenderType): bool
	{
		if ($baseType === $extenderType) return true;

		$compatibilityMap = [
			'datetime' => ['date', 'timestamp'],
			'date' => ['datetime', 'timestamp'],
			'timestamp' => ['datetime', 'date'],
			'integer' => ['bigint', 'smallint', 'tinyint'],
			'bigint' => ['integer', 'smallint', 'tinyint'],
			'smallint' => ['integer', 'bigint', 'tinyint'],
			'tinyint' => ['integer', 'bigint', 'smallint'],
			'decimal' => ['float', 'double'],
			'float' => ['decimal', 'double'],
			'double' => ['decimal', 'float'],
			'string' => ['text'],
			'text' => ['string']
		];

		return isset($compatibilityMap[$baseType]) && in_array($extenderType, $compatibilityMap[$baseType]);
	}

	/**
	 * Get cached overlapping columns
	 * 
	 * @param string $baseTable
	 * @param string $extenderTable
	 * @return array
	 */
	protected function getExtendsBaseTableOverlappingColumns(string $baseTable, string $extenderTable): array
	{
		$cacheKey = "{$baseTable}:{$extenderTable}";
		return static::$extendsBaseTableOverlapCache[$cacheKey] ?? [];
	}

	/**
	 * Synchronize overlapping columns to base table
	 * 
	 * @param string $baseTable
	 * @param string $foreignKeyColumn
	 * @param mixed $foreignKeyValue
	 * @return bool
	 */
	protected function synchronizeExtendsBaseTableColumns(string $baseTable, string $foreignKeyColumn, $foreignKeyValue): bool
	{
		if (!$foreignKeyValue) {
			Log::notice('ExtendsBaseTable::synchronizeExtendsBaseTableColumns - No foreign key value provided', [
				'trait' => 'ExtendsBaseTable',
				'class' => static::class,
				'method' => __METHOD__,
				'line' => __LINE__,
				'base_table' => $baseTable
			]);
			return false;
		}
		$extenderTable = $this->getTable();
		$overlapping = $this->getExtendsBaseTableOverlappingColumns($baseTable, $extenderTable);
		if (empty($overlapping)) {
			Log::debug('ExtendsBaseTable::synchronizeExtendsBaseTableColumns - No overlapping columns to synchronize', [
				'trait' => 'ExtendsBaseTable',
				'class' => static::class,
				'base_table' => $baseTable,
				'extender_table' => $extenderTable
			]);
			return true;
		}
		try {
			DB::beginTransaction();
			$blocked = $this->extendsBaseTableDerivedBlockedColumns($baseTable, $extenderTable, $foreignKeyColumn);
			$updateData = [];
			foreach ($overlapping as $columnName => $meta) {
				$baseExcludedColumns = static::getExtendsBaseTableExcludedColumns();
				if (in_array($columnName, (!empty($baseExcludedColumns) && is_array($baseExcludedColumns)) ? $baseExcludedColumns : self::BASE_TABLE_EXCLUDED_COLUMNS, true)) continue;
				if (isset($blocked[$columnName])) continue;
				if (!$this->isDirty($columnName)) continue;
				$value = $this->getAttribute($columnName);
				if ($this->extendsBaseTableIsCandidateLinkedIdColumn($columnName, $value, $meta) && !$this->extendsBaseTableShouldSyncLinkedIdColumn($baseTable, $columnName, $value)) continue;
				if (!Schema::hasColumn($baseTable, $columnName)) continue;
				$updateData[$columnName] = $value;
			}
			if (!empty($updateData)) {
				$query = DB::table($baseTable)
					->where('id', $foreignKeyValue);
				if ($query->doesntExist()) {
					DB::rollBack();
					return false;
				}
				if (!empty($this->baseTableName)) {
					try {
						$modelClass = '\\App\\Models\\' . Str::studly(Str::singular($this->baseTableName));
						if (!class_exists($modelClass)) {
							$modelClass = '\\App\\' . Str::studly(Str::singular($this->baseTableName));
							if (!class_exists($modelClass))
								throw new \Exception("Model class not found for table: " . $this->baseTableName);
						}
						$model = app()->make($modelClass);
						if ($model instanceof Model) {
							/** @var Model|null $instance */
							$instance = $model->newQuery()->find($foreignKeyValue);
							if (!$instance || !($instance instanceof Model))
								throw new \Exception("Record not found with ID: $foreignKeyValue");
							$instance->fill($updateData);
							$instance->setAttribute('updated_at', now());
							$instance->setAttribute('updated_by', DC::DEFAULT_UUID);
							foreach (['saving', 'updating'] as $event) {
								try {
									$instance->fireModelEvent($event, false);
								} catch (\Throwable $e) {
									ErrorHandler::evaluateExistenceToLogChannel(
										'extends_base_table_errors',
										candidate: [
											'message' => "ExtendsBaseTable::synchronizeExtendsBaseTableColumns
											- Failed to fire {$event} model event",
											'context' => [
												'trait' => 'ExtendsBaseTable',
												'class' => static::class,
												'method' => __METHOD__,
												'line' => __LINE__,
												'base_table' => $baseTable,
												'foreign_key_value' => $foreignKeyValue,
												'error' => $e->getMessage(),
											]
										]
									);
								}
							}
							$affected = DB::table($baseTable)
								->where('id', $foreignKeyValue)
								->update(array_merge($updateData, [
									'updated_at' => now(),
									'updated_by' => DC::DEFAULT_UUID,
								]));
							if ($affected > 0) {
								foreach (['saved', 'updated', 'synced'] as $event) {
									try {
										$instance->fireModelEvent($event, false);
									} catch (\Throwable $e) {
										ErrorHandler::evaluateExistenceToLogChannel(
											'extends_base_table_errors',
											candidate: [
												'message' => "ExtendsBaseTable::synchronizeExtendsBaseTableColumns
												- Failed to fire {$event} model event",
												'context' => [
													'trait' => 'ExtendsBaseTable',
													'class' => static::class,
													'method' => __METHOD__,
													'line' => __LINE__,
													'base_table' => $baseTable,
													'foreign_key_value' => $foreignKeyValue,
													'error' => $e->getMessage(),
												]
											]
										);
									}
								}
							}
						} else {
							throw new \Exception("Class is not a Model instance");
						}
					} catch (\Throwable $e) {
						ErrorHandler::evaluateExistenceToLogChannel(
							'extends_base_table_errors',
							candidate: [
								'message' => 'ExtendsBaseTable::synchronizeExtendsBaseTableColumns - Failed to handle model events',
								'context' => [
									'trait' => 'ExtendsBaseTable',
									'class' => static::class,
									'method' => __METHOD__,
									'line' => __LINE__,
									'base_table' => $baseTable,
									'foreign_key_value' => $foreignKeyValue,
									'error' => $e->getMessage(),
									'trace' => $e->getTraceAsString()
								]
							],
							mainChannel: 'error'
						);

						// Continue without events - don't rollback just because events failed
						// Fire a warning but proceed with the update
					}
				}
				$affected = $query->update(array_merge($updateData, [
					'updated_at' => now(),
					'updated_by' => DC::DEFAULT_UUID,
				]));
				Log::debug('ExtendsBaseTable::synchronizeExtendsBaseTableColumns - Synchronized columns', [
					'trait' => 'ExtendsBaseTable',
					'class' => static::class,
					'base_table' => $baseTable,
					'foreign_key_value' => $foreignKeyValue,
					'columns' => array_keys($updateData),
					'affected_rows' => $affected
				]);
			}
			DB::commit();
			return true;
		} catch (\Throwable $e) {
			DB::rollBack();
			ErrorHandler::evaluateExistenceToLogChannel(
				'extends_base_table_errors',
				candidate: [
					'message' => 'ExtendsBaseTable::synchronizeExtendsBaseTableColumns - Failed to synchronize columns',
					'context' => [
						'trait' => 'ExtendsBaseTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__,
						'base_table' => $baseTable,
						'foreign_key_value' => $foreignKeyValue,
						'error' => $e->getMessage()
					]
				],
				mainChannel: 'error'
			);
			return false;
		}
	}

	/**
	 * Derive blocked column names for sync based on excluded/static rules and redundant link columns.
	 * 
	 * @param string $baseTable
	 * @param string $extenderTable
	 * @param string $foreignKeyColumn
	 * @return array
	 */
	protected function extendsBaseTableDerivedBlockedColumns(string $baseTable, string $extenderTable, string $foreignKeyColumn): array
	{
		$baseCols = static::getExtendsBaseTableExcludedColumns();
		$blocked = [];;
		foreach (!empty($baseCols) && is_array($baseCols) ? static::getExtendsBaseTableExcludedColumns() : (self::BASE_TABLE_EXCLUDED_COLUMNS ?? []) as $c)
			if (isset($baseCols[$c])) $blocked[$c] = true;
		if (isset($baseCols[$foreignKeyColumn])) $blocked[$foreignKeyColumn] = true;
		$extSnake = $this->extendsBaseTableSnakeFromModelClass(static::class);
		$baseSnake = Str::singular($baseTable);
		$baseSnake = preg_replace('/[^a-z0-9_]+/i', '_', $baseSnake) ?? $baseSnake;
		$baseSnake = Str::snake($baseSnake);
		foreach ([$extSnake, "{$extSnake}_id", $baseSnake, "{$baseSnake}_id"] as $c)
			if (isset($baseCols[$c])) $blocked[$c] = true;
		return $blocked;
	}

	/**
	 * Build a snake-cased token for a model class name using regex + Str.
	 * 
	 * @param string|null $fqcn
	 * @return string
	 */
	protected function extendsBaseTableSnakeFromModelClass(?string $fqcn = null): string
	{
		$cls = $fqcn ?: static::class;
		$base = class_basename($cls);
		$base = preg_replace('/(?<!^)[A-Z]/', '_$0', $base) ?? $base;
		return Str::snake($base);
	}


	/**
	 * Detect whether a column behaves like a linked identifier (uuid/int id, with or without _id).
	 * 
	 * @param string $columnName
	 * @param mixed $value
	 * @param array $meta
	 * @return bool
	 */
	protected function extendsBaseTableIsCandidateLinkedIdColumn(string $columnName, mixed $value, array $meta = []): bool
	{
		$baseType = (string) ($meta['base_type'] ?? '');
		$extType = (string) ($meta['extender_type'] ?? '');
		$isNumericId = str_ends_with($columnName, '_id') && (
			in_array($baseType, ['integer', 'bigint', 'smallint', 'tinyint'], true)
			|| in_array($extType, ['integer', 'bigint', 'smallint', 'tinyint'], true)
		);
		if ($isNumericId) return true;
		if (is_string($value) && \App\Models\Utility::looksLikeUuid($value)) return true;
		$isTextual = in_array($baseType, ['string', 'text'], true) || in_array($extType, ['string', 'text'], true);
		if ($isTextual && $value === null) return true;
		return false;
	}

	/**
	 * Infer a candidate table name from a column like product_service_id => product_services, product_service => product_services.
	 * 
	 * @param string $columnName
	 * @return string|null
	 */
	protected function extendsBaseTableProposedTableFromLinkedColumn(string $columnName): ?string
	{
		$stem = $columnName;
		if (str_ends_with($stem, '_id'))
			$stem = substr($stem, 0, -3);
		$stem = trim((string) $stem);
		if ($stem === '') return null;
		$stem = preg_replace('/[^a-z0-9_]+/i', '', $stem) ?? $stem;
		$stem = Str::snake($stem);
		$table = Str::plural($stem);
		return $table !== '' ? $table : null;
	}

	/**
	 * Resolve FK reference metadata for a given table+column via information_schema for mysql/mariadb/pgsql.
	 * 
	 * @param string $tableName
	 * @param string $columnName
	 * @return array|null
	 */
	protected function extendsBaseTableGetForeignKeyReference(string $tableName, string $columnName): ?array
	{
		$driver = DB::connection()->getDriverName();
		if (in_array($driver, ['mysql', 'mariadb'], true)) {
			$rows = DB::select(
				"SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
			 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
			 WHERE TABLE_SCHEMA = DATABASE()
			   AND TABLE_NAME = ?
			   AND COLUMN_NAME = ?
			   AND REFERENCED_TABLE_NAME IS NOT NULL",
				[$tableName, $columnName]
			);
			if (!$rows) return null;
			$r = (array) $rows[0];
			$rt = $r['REFERENCED_TABLE_NAME'] ?? null;
			$rc = $r['REFERENCED_COLUMN_NAME'] ?? null;
			if (!is_string($rt) || trim($rt) === '') return null;
			if (!is_string($rc) || trim($rc) === '') return null;
			return ['table' => $rt, 'column' => $rc];
		}

		if ($driver === 'pgsql') {
			$rows = DB::select(
				"SELECT tc.constraint_name, ccu.table_name AS foreign_table_name, ccu.column_name AS foreign_column_name
			 FROM information_schema.table_constraints AS tc
			 JOIN information_schema.key_column_usage AS kcu
			   ON tc.constraint_name = kcu.constraint_name
			 JOIN information_schema.constraint_column_usage AS ccu
			   ON ccu.constraint_name = tc.constraint_name
			 WHERE tc.constraint_type = 'FOREIGN KEY'
			   AND tc.table_name = ?
			   AND kcu.column_name = ?",
				[$tableName, $columnName]
			);
			if (!$rows) return null;
			$r = (array) $rows[0];
			$rt = $r['foreign_table_name'] ?? null;
			$rc = $r['foreign_column_name'] ?? null;
			if (!is_string($rt) || trim($rt) === '') return null;
			if (!is_string($rc) || trim($rc) === '') return null;
			return ['table' => $rt, 'column' => $rc];
		}

		return null;
	}

	/**
	 * Decide whether a linked-id column should be synced; enforces inferred-table + FK detection + existence check.
	 * 
	 * @param string $baseTable
	 * @param string $columnName
	 * @param mixed $value
	 * @return bool
	 */
	protected function extendsBaseTableShouldSyncLinkedIdColumn(string $baseTable, string $columnName, mixed $value): bool
	{
		$always = [
			'client',
			'client_id',
			'customer',
			'customer_id',
			'vendor',
			'vendor_id',
			'company',
			'company_id',
			'payment',
			'payment_id',
			'product',
			'product_id',
			'product_service',
			'product_service_id',
			'product_category',
			'product_category_id',
			'product_service_category_id',
			'product_service_category',
		];

		$proposedTable = $this->extendsBaseTableProposedTableFromLinkedColumn($columnName);
		if (!$proposedTable || !Schema::hasTable($proposedTable))
			return in_array($columnName, $always, true);

		$fk = $this->extendsBaseTableGetForeignKeyReference($baseTable, $columnName);
		if (!$fk) return in_array($columnName, $always, true);

		if (($fk['table'] ?? null) !== $proposedTable)
			return in_array($columnName, $always, true);

		if ($value === null) return true;

		if (!is_scalar($value)) return false;

		$exists = DB::table($fk['table'])
			->where($fk['column'], $value)
			->exists();

		return $exists;
	}
}
