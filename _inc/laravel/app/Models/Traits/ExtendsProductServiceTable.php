<?php

namespace App\Traits;

use App\Helpers\ErrorHandler;
use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Support\Facades\{Log, Schema};
use Illuminate\Database\Eloquent\Model;

/**
 * ExtendsProductServiceTable trait
 * 
 * Extends a model to synchronize product/service-related columns with products or product_services tables.
 * Handles polymorphic relationships and automatically detects the appropriate base table.
 * Synchronizes overlapping compatible columns between extender and base tables.
 */
trait ExtendsProductServiceTable
{
	use ExtendsBaseTable;

	protected array $extendsProductServiceTableErrors = [];
	protected static ?string $extendsProductServiceTableName = null;
	protected static ?string $extendsProductServiceForeignKeyColumn = null;
	protected static bool $extendsProductServiceInitialized = false;

	/**
	 * Boot the ExtendsProductServiceTable trait
	 */
	protected static function bootExtendsProductServiceTable(): void
	{
		try {
			/** @phpstan-ignore new.static */
			$instance = new static;

			if (!static::$extendsProductServiceInitialized) {
				if (!$instance->detectExtendsProductServiceConfiguration()) {
					ErrorHandler::evaluateExistenceToLogChannel(
						'extends_product_service_table_errors',
						[
							'message' => 'ExtendsProductServiceTable::bootExtendsProductServiceTable - Failed to detect product/service configuration',
							'context' => [
								'trait' => 'ExtendsProductServiceTable',
								'class' => static::class,
								'method' => __METHOD__,
								'line' => __LINE__
							]
						],
						mainChannel: 'error'
					);
					return;
				}

				static::$extendsProductServiceInitialized = true;
			}

			static::saved(function (Model $model) {
				if (method_exists($model, 'handleExtendsProductServiceTableSaved'))
					$model->handleExtendsProductServiceTableSaved();
			});

			Log::debug('ExtendsProductServiceTable::bootExtendsProductServiceTable - Trait booted successfully', [
				'trait' => 'ExtendsProductServiceTable',
				'class' => static::class,
				'product_service_table' => static::$extendsProductServiceTableName,
				'foreign_key' => static::$extendsProductServiceForeignKeyColumn
			]);
		} catch (\Throwable $e) {
			ErrorHandler::evaluateExistenceToLogChannel(
				'extends_product_service_table_errors',
				[
					'message' => 'ExtendsProductServiceTable::bootExtendsProductServiceTable - Failed to boot trait',
					'context' => [
						'trait' => 'ExtendsProductServiceTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__,
						'error' => $e->getMessage()
					]
				],
				mainChannel: 'error'
			);
		}
	}

	/**
	 * Detect product/service configuration (table name and foreign key column)
	 * 
	 * @return bool
	 */
	protected function detectExtendsProductServiceConfiguration(): bool
	{
		try {
			static::$extendsProductServiceForeignKeyColumn = $this->detectExtendsProductServiceForeignKeyColumn();

			if (!static::$extendsProductServiceForeignKeyColumn) {
				ErrorHandler::evaluateExistenceToLogChannel(
					'extends_product_service_table_errors',
					[
						'message' => 'ExtendsProductServiceTable::detectExtendsProductServiceConfiguration - No valid foreign key column found',
						'context' => [
							'trait' => 'ExtendsProductServiceTable',
							'class' => static::class,
							'method' => __METHOD__,
							'line' => __LINE__
						]
					],
					mainChannel: 'error'
				);
				return false;
			}

			static::$extendsProductServiceTableName = $this->resolveExtendsProductServiceTableName();

			if (!static::$extendsProductServiceTableName) {
				ErrorHandler::evaluateExistenceToLogChannel(
					'extends_product_service_table_errors',
					[
						'message' => 'ExtendsProductServiceTable::detectExtendsProductServiceConfiguration - Could not resolve base table',
						'context' => [
							'trait' => 'ExtendsProductServiceTable',
							'class' => static::class,
							'method' => __METHOD__,
							'line' => __LINE__
						]
					],
					mainChannel: 'error'
				);
				return false;
			}

			if (!Schema::hasTable(static::$extendsProductServiceTableName)) {
				ErrorHandler::evaluateExistenceToLogChannel(
					'extends_product_service_table_errors',
					[
						'message' => 'ExtendsProductServiceTable::detectExtendsProductServiceConfiguration - Base table does not exist',
						'context' => [
							'trait' => 'ExtendsProductServiceTable',
							'class' => static::class,
							'method' => __METHOD__,
							'line' => __LINE__,
							'base_table' => static::$extendsProductServiceTableName
						]
					],
					mainChannel: 'error'
				);
				return false;
			}

			if (!$this->setupExtendsBaseTable(static::$extendsProductServiceTableName, static::$extendsProductServiceForeignKeyColumn)) {
				ErrorHandler::evaluateExistenceToLogChannel(
					'extends_product_service_table_errors',
					[
						'message' => 'ExtendsProductServiceTable::detectExtendsProductServiceConfiguration - Failed to initialize base table extension',
						'context' => [
							'trait' => 'ExtendsProductServiceTable',
							'class' => static::class,
							'method' => __METHOD__,
							'line' => __LINE__,
							'base_table' => static::$extendsProductServiceTableName
						]
					],
					mainChannel: 'warning'
				);
				return false;
			}

			return true;
		} catch (\Throwable $e) {
			ErrorHandler::evaluateExistenceToLogChannel(
				'extends_product_service_table_errors',
				[
					'message' => 'ExtendsProductServiceTable::detectExtendsProductServiceConfiguration - Failed to detect configuration',
					'context' => [
						'trait' => 'ExtendsProductServiceTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__,
						'error' => $e->getMessage()
					]
				],
				mainChannel: 'error'
			);
			return false;
		}
	}

	/**
	 * Resolve product/service table name based on polymorphic column value
	 * 
	 * @return string|null
	 */
	protected function resolveExtendsProductServiceTableName(): ?string
	{
		try {
			$columnName = static::$extendsProductServiceForeignKeyColumn;

			if (!$columnName) {
				ErrorHandler::evaluateExistenceToLogChannel(
					'extends_product_service_table_errors',
					[
						'message' => 'ExtendsProductServiceTable::resolveExtendsProductServiceTableName - Foreign key column not set',
						'context' => [
							'trait' => 'ExtendsProductServiceTable',
							'class' => static::class,
							'method' => __METHOD__,
							'line' => __LINE__
						]
					],
					mainChannel: 'error'
				);
				return null;
			}

			$productsTable = $this->resolveExtendsProductServiceProductsTableName();
			$productServicesTable = $this->resolveExtendsProductServiceProductServicesTableName();

			$columnNameLower = \strtolower($columnName);

			if (\in_array($columnNameLower, ['product', 'product_id'], true)) {
				Log::debug('ExtendsProductServiceTable::resolveExtendsProductServiceTableName - Using products table based on column name', [
					'trait' => 'ExtendsProductServiceTable',
					'class' => static::class,
					'table' => $productsTable,
					'column' => $columnName
				]);
				return $productsTable;
			}

			if (\in_array($columnNameLower, ['service', 'product_service', 'product_service_id'], true)) {
				Log::debug('ExtendsProductServiceTable::resolveExtendsProductServiceTableName - Using product_services table based on column name', [
					'trait' => 'ExtendsProductServiceTable',
					'class' => static::class,
					'table' => $productServicesTable,
					'column' => $columnName
				]);
				return $productServicesTable;
			}

			Log::debug('ExtendsProductServiceTable::resolveExtendsProductServiceTableName - Defaulting to product_services table', [
				'trait' => 'ExtendsProductServiceTable',
				'class' => static::class,
				'table' => $productServicesTable,
				'column' => $columnName
			]);

			return $productServicesTable;
		} catch (\Throwable $e) {
			ErrorHandler::evaluateExistenceToLogChannel(
				'extends_product_service_table_errors',
				[
					'message' => 'ExtendsProductServiceTable::resolveExtendsProductServiceTableName - Failed to resolve table name',
					'context' => [
						'trait' => 'ExtendsProductServiceTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__,
						'error' => $e->getMessage()
					]
				],
				mainChannel: 'error'
			);
			return null;
		}
	}

	/**
	 * Resolve products table name from constant or default
	 * 
	 * @return string
	 */
	protected function resolveExtendsProductServiceProductsTableName(): string
	{
		try {
			if (\defined(DC::class . '::TABLE_PRODUCTS')) {
				Log::debug('ExtendsProductServiceTable::resolveExtendsProductServiceProductsTableName - Using DC::TABLE_PRODUCTS constant', [
					'trait' => 'ExtendsProductServiceTable',
					'class' => static::class,
					'table' => DC::TABLE_PRODUCTS
				]);
				return DC::TABLE_PRODUCTS;
			}
		} catch (\Throwable $e) {
			Log::debug('ExtendsProductServiceTable::resolveExtendsProductServiceProductsTableName - DC::TABLE_PRODUCTS not available', [
				'trait' => 'ExtendsProductServiceTable',
				'class' => static::class,
				'error' => $e->getMessage()
			]);
		}

		Log::debug('ExtendsProductServiceTable::resolveExtendsProductServiceProductsTableName - Using default products table', [
			'trait' => 'ExtendsProductServiceTable',
			'class' => static::class
		]);

		return 'products';
	}

	/**
	 * Resolve product_services table name from constant or default
	 * 
	 * @return string
	 */
	protected function resolveExtendsProductServiceProductServicesTableName(): string
	{
		try {
			if (\defined(DC::class . '::TABLE_PROD_SERVS')) {
				Log::debug('ExtendsProductServiceTable::resolveExtendsProductServiceProductServicesTableName - Using DC::TABLE_PROD_SERVS constant', [
					'trait' => 'ExtendsProductServiceTable',
					'class' => static::class,
					'table' => DC::TABLE_PROD_SERVS
				]);
				return DC::TABLE_PROD_SERVS;
			}
		} catch (\Throwable $e) {
			Log::debug('ExtendsProductServiceTable::resolveExtendsProductServiceProductServicesTableName - DC::TABLE_PROD_SERVS not available', [
				'trait' => 'ExtendsProductServiceTable',
				'class' => static::class,
				'error' => $e->getMessage()
			]);
		}

		Log::debug('ExtendsProductServiceTable::resolveExtendsProductServiceProductServicesTableName - Using default product_services table', [
			'trait' => 'ExtendsProductServiceTable',
			'class' => static::class
		]);

		return 'product_services';
	}

	/**
	 * Detect foreign key column in the model table
	 * 
	 * @return string|null
	 */
	protected function detectExtendsProductServiceForeignKeyColumn(): ?string
	{
		try {
			$tableName = $this->getTable();
			$candidateColumns = [];

			try {
				if (\defined(BC::class . '::COL_PRD_ID'))
					$candidateColumns[] = BC::COL_PRD_ID;
			} catch (\Throwable $e) {
				Log::debug('ExtendsProductServiceTable::detectExtendsProductServiceForeignKeyColumn - BC::COL_PRD_ID not available, using fallback', [
					'trait' => 'ExtendsProductServiceTable',
					'class' => static::class,
					'error' => $e->getMessage()
				]);
				$candidateColumns[] = 'product_id';
			}

			try {
				if (\defined(BC::class . '::COL_PRD_SV_ID'))
					$candidateColumns[] = BC::COL_PRD_SV_ID;
			} catch (\Throwable $e) {
				Log::debug('ExtendsProductServiceTable::detectExtendsProductServiceForeignKeyColumn - BC::COL_PRD_SV_ID not available, using fallback', [
					'trait' => 'ExtendsProductServiceTable',
					'class' => static::class,
					'error' => $e->getMessage()
				]);
				$candidateColumns[] = 'product_service_id';
			}

			$candidateColumns[] = 'product';
			$candidateColumns[] = 'service';
			$candidateColumns[] = 'product_service';

			foreach ($candidateColumns as $column) {
				if (!Schema::hasColumn($tableName, $column)) continue;

				$columnType = null;

				try {
					$columnType = Schema::getColumnType($tableName, $column);
				} catch (\Throwable $e) {
					Log::debug('Schema::getColumnType failed; skipping column', [
						'table' => $tableName,
						'column' => $column,
						'error' => $e->getMessage(),
					]);
					continue;
				}
				if ($this->isExtendsProductServiceValidForeignKeyType($columnType)) {
					Log::debug('ExtendsProductServiceTable::detectExtendsProductServiceForeignKeyColumn - Foreign key column detected', [
						'trait' => 'ExtendsProductServiceTable',
						'class' => static::class,
						'column' => $column,
						'type' => $columnType
					]);
					return $column;
				}
			}
			ErrorHandler::evaluateExistenceToLogChannel(
				'extends_product_service_table_errors',
				[
					'message' => 'ExtendsProductServiceTable::detectExtendsProductServiceForeignKeyColumn - No valid foreign key column found',
					'context' => [
						'trait' => 'ExtendsProductServiceTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__
					]
				],
			);
			return null;
		} catch (\Throwable $e) {
			ErrorHandler::evaluateExistenceToLogChannel(
				'extends_product_service_table_errors',
				[
					'message' => 'ExtendsProductServiceTable::detectExtendsProductServiceForeignKeyColumn - Failed to detect foreign key column',
					'context' => [
						'trait' => 'ExtendsProductServiceTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__,
						'error' => $e->getMessage()
					]
				],
				mainChannel: 'error'
			);
			return null;
		}
	}

	/**
	 * Check if column type is valid for foreign key
	 * 
	 * @param string $type
	 * @return bool
	 */
	protected function isExtendsProductServiceValidForeignKeyType(string $type): bool
	{
		$validTypes = ['string', 'integer', 'bigint', 'smallint', 'uuid'];
		return \in_array(\strtolower($type), $validTypes, true);
	}

	/**
	 * Handle model saved event
	 */
	protected function handleExtendsProductServiceTableSaved(): void
	{
		if (!static::$extendsProductServiceInitialized) return;

		$foreignKeyValue = $this->getAttribute(static::$extendsProductServiceForeignKeyColumn);

		$this->synchronizeExtendsBaseTableColumns(
			static::$extendsProductServiceTableName,
			static::$extendsProductServiceForeignKeyColumn,
			$foreignKeyValue
		);
	}
}
