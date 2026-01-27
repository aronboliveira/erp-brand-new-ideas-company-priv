<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Support\Facades\{Log, Schema};
use Illuminate\Database\Eloquent\Model;

/**
 * ExtendsPaymentTable trait
 * 
 * Extends a model to synchronize payment-related columns with a payments table.
 * Automatically detects foreign key column and synchronizes overlapping compatible columns.
 */
trait ExtendsPaymentTable
{
	use ExtendsBaseTable;

	protected static ?string $extendsPaymentTableName = null;
	protected static ?string $extendsPaymentForeignKeyColumn = null;
	protected static bool $extendsPaymentInitialized = false;

	/**
	 * Boot the ExtendsPaymentTable trait
	 */
	protected static function bootExtendsPaymentTable(): void
	{
		try {
			$instance = new static;

			if (!static::$extendsPaymentInitialized) {
				if (!$instance->detectExtendsPaymentConfiguration()) {
					Log::warning('ExtendsPaymentTable::bootExtendsPaymentTable - Failed to detect payment configuration', [
						'trait' => 'ExtendsPaymentTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__
					]);
					return;
				}

				if (!$instance->setupExtendsBaseTable(static::$extendsPaymentTableName, static::$extendsPaymentForeignKeyColumn)) {
					Log::warning('ExtendsPaymentTable::bootExtendsPaymentTable - Failed to initialize base table extension', [
						'trait' => 'ExtendsPaymentTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__,
						'payment_table' => static::$extendsPaymentTableName
					]);
					return;
				}

				static::$extendsPaymentInitialized = true;
			}

			static::saved(function (Model $model) {
				$model->handleExtendsPaymentSaved();
			});

			Log::debug('ExtendsPaymentTable::bootExtendsPayment - Trait booted successfully', [
				'trait' => 'ExtendsPaymentTable',
				'class' => static::class,
				'payment_table' => static::$extendsPaymentTableName,
				'foreign_key' => static::$extendsPaymentForeignKeyColumn
			]);
		} catch (\Throwable $e) {
			Log::error('ExtendsPaymentTable::bootExtendsPayment - Failed to boot trait', [
				'trait' => 'ExtendsPaymentTable',
				'class' => static::class,
				'method' => __METHOD__,
				'line' => __LINE__,
				'error' => $e->getMessage()
			]);
		}
	}

	/**
	 * Detect payment configuration (table name and foreign key column)
	 * 
	 * @return bool
	 */
	protected function detectExtendsPaymentConfiguration(): bool
	{
		try {
			static::$extendsPaymentTableName = $this->resolveExtendsPaymentTableName();

			if (!Schema::hasTable(static::$extendsPaymentTableName)) {
				Log::error('ExtendsPaymentTable::detectExtendsPaymentConfiguration - Payment table does not exist', [
					'trait' => 'ExtendsPaymentTable',
					'class' => static::class,
					'method' => __METHOD__,
					'line' => __LINE__,
					'payment_table' => static::$extendsPaymentTableName
				]);
				return false;
			}

			static::$extendsPaymentForeignKeyColumn = $this->detectExtendsPaymentForeignKeyColumn();

			if (!static::$extendsPaymentForeignKeyColumn) {
				Log::error('ExtendsPaymentTable::detectExtendsPaymentConfiguration - No valid foreign key column found', [
					'trait' => 'ExtendsPaymentTable',
					'class' => static::class,
					'method' => __METHOD__,
					'line' => __LINE__
				]);
				return false;
			}

			return true;
		} catch (\Throwable $e) {
			Log::error('ExtendsPaymentTable::detectExtendsPaymentConfiguration - Failed to detect configuration', [
				'trait' => 'ExtendsPaymentTable',
				'class' => static::class,
				'method' => __METHOD__,
				'line' => __LINE__,
				'error' => $e->getMessage()
			]);
			return false;
		}
	}

	/**
	 * Resolve payment table name from constant or default
	 * 
	 * @return string
	 */
	protected function resolveExtendsPaymentTableName(): string
	{
		try {
			if (\defined(DC::class . '::TABLE_PAY'))
				return DC::TABLE_PAY;
		} catch (\Throwable $e) {
			Log::debug('ExtendsPaymentTable::resolveExtendsPaymentTableName - DC::TABLE_PAY not available', [
				'trait' => 'ExtendsPaymentTable',
				'class' => static::class,
				'error' => $e->getMessage()
			]);
		}

		Log::debug('ExtendsPaymentTable::resolveExtendsPaymentTableName - Using default payments table', [
			'trait' => 'ExtendsPaymentTable',
			'class' => static::class
		]);

		return 'payments';
	}

	/**
	 * Detect foreign key column in the model table
	 * 
	 * @return string|null
	 */
	protected function detectExtendsPaymentForeignKeyColumn(): ?string
	{
		try {
			$tableName = $this->getTable();
			$candidateColumns = [];

			try {
				if (\defined(BC::class . '::COL_PAY_ID'))
					$candidateColumns[] = BC::COL_PAY_ID;
			} catch (\Throwable $e) {
				Log::debug('ExtendsPaymentTable::detectExtendsPaymentForeignKeyColumn - BC::COL_PAY_ID not available', [
					'trait' => 'ExtendsPaymentTable',
					'class' => static::class,
					'error' => $e->getMessage()
				]);
				$candidateColumns[] = 'payment_id';
			}

			$candidateColumns[] = 'payment';

			foreach ($candidateColumns as $column) {
				if (!Schema::hasColumn($tableName, $column)) continue;

				$columnType = Schema::getColumnType($tableName, $column);

				if ($this->isExtendsPaymentValidForeignKeyType($columnType)) {
					Log::debug('ExtendsPaymentTable::detectExtendsPaymentForeignKeyColumn - Foreign key column detected', [
						'trait' => 'ExtendsPaymentTable',
						'class' => static::class,
						'column' => $column,
						'type' => $columnType
					]);
					return $column;
				}
			}

			Log::warning('ExtendsPaymentTable::detectExtendsPaymentForeignKeyColumn - No valid foreign key column found', [
				'trait' => 'ExtendsPaymentTable',
				'class' => static::class,
				'method' => __METHOD__,
				'line' => __LINE__,
				'candidates' => $candidateColumns
			]);

			return null;
		} catch (\Throwable $e) {
			Log::error('ExtendsPaymentTable::detectExtendsPaymentForeignKeyColumn - Failed to detect foreign key', [
				'trait' => 'ExtendsPaymentTable',
				'class' => static::class,
				'method' => __METHOD__,
				'line' => __LINE__,
				'error' => $e->getMessage()
			]);
			return null;
		}
	}

	/**
	 * Check if column type is valid for foreign key
	 * 
	 * @param string $type
	 * @return bool
	 */
	protected function isExtendsPaymentValidForeignKeyType(string $type): bool
	{
		$validTypes = ['string', 'integer', 'bigint', 'smallint', 'uuid'];
		return \in_array(\strtolower($type), $validTypes, true);
	}

	/**
	 * Handle model saved event
	 */
	protected function handleExtendsPaymentSaved(): void
	{
		if (!static::$extendsPaymentInitialized) return;

		$foreignKeyValue = $this->getAttribute(static::$extendsPaymentForeignKeyColumn);

		$this->synchronizeExtendsBaseTableColumns(
			static::$extendsPaymentTableName,
			static::$extendsPaymentForeignKeyColumn,
			$foreignKeyValue
		);
	}
}
