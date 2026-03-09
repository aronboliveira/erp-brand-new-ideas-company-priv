<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Support\Facades\{Log, Schema};
use Illuminate\Database\Eloquent\Model;

/**
 * ExtendsInvoiceTable trait
 * 
 * Extends a model to synchronize invoice-related columns with an invoices table.
 * Automatically detects foreign key column and synchronizes overlapping compatible columns.
 */
trait ExtendsInvoiceTable
{
	use ExtendsBaseTable;

	protected static ?string $extendsInvoiceTableName = null;
	protected static ?string $extendsInvoiceForeignKeyColumn = null;
	protected static bool $extendsInvoiceInitialized = false;

	/**
	 * Boot the ExtendsInvoiceTable trait
	 */
	protected static function bootExtendsInvoice(): void
	{
		try {
			/** @phpstan-ignore new.static */
			$instance = new static;

			if (!static::$extendsInvoiceInitialized) {
				if (!$instance->detectExtendsInvoiceConfiguration()) {
					Log::warning('ExtendsInvoiceTable::bootExtendsInvoice - Failed to detect invoice configuration', [
						'trait' => 'ExtendsInvoiceTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__
					]);
					return;
				}

				if (!$instance->setupExtendsBaseTable(static::$extendsInvoiceTableName, static::$extendsInvoiceForeignKeyColumn)) {
					Log::warning('ExtendsInvoiceTable::bootExtendsInvoice - Failed to initialize base table extension', [
						'trait' => 'ExtendsInvoiceTable',
						'class' => static::class,
						'method' => __METHOD__,
						'line' => __LINE__,
						'invoice_table' => static::$extendsInvoiceTableName
					]);
					return;
				}

				static::$extendsInvoiceInitialized = true;
			}

			static::saved(function (Model $model) {
				if (method_exists($model, 'handleExtendsInvoiceSaved'))
					$model->handleExtendsInvoiceSaved();
			});

			Log::debug('ExtendsInvoiceTable::bootExtendsInvoice - Trait booted successfully', [
				'trait' => 'ExtendsInvoiceTable',
				'class' => static::class,
				'invoice_table' => static::$extendsInvoiceTableName,
				'foreign_key' => static::$extendsInvoiceForeignKeyColumn
			]);
		} catch (\Throwable $e) {
			Log::error('ExtendsInvoiceTable::bootExtendsInvoice - Failed to boot trait', [
				'trait' => 'ExtendsInvoiceTable',
				'class' => static::class,
				'method' => __METHOD__,
				'line' => __LINE__,
				'error' => $e->getMessage()
			]);
		}
	}

	/**
	 * Detect invoice configuration (table name and foreign key column)
	 * 
	 * @return bool
	 */
	protected function detectExtendsInvoiceConfiguration(): bool
	{
		try {
			static::$extendsInvoiceTableName = $this->resolveExtendsInvoiceTableName();

			if (!Schema::hasTable(static::$extendsInvoiceTableName)) {
				Log::error('ExtendsInvoiceTable::detectExtendsInvoiceConfiguration - Invoice table does not exist', [
					'trait' => 'ExtendsInvoiceTable',
					'class' => static::class,
					'method' => __METHOD__,
					'line' => __LINE__,
					'invoice_table' => static::$extendsInvoiceTableName
				]);
				return false;
			}

			static::$extendsInvoiceForeignKeyColumn = $this->detectExtendsInvoiceForeignKeyColumn();

			if (!static::$extendsInvoiceForeignKeyColumn) {
				Log::error('ExtendsInvoiceTable::detectExtendsInvoiceConfiguration - No valid foreign key column found', [
					'trait' => 'ExtendsInvoiceTable',
					'class' => static::class,
					'method' => __METHOD__,
					'line' => __LINE__
				]);
				return false;
			}

			return true;
		} catch (\Throwable $e) {
			Log::error('ExtendsInvoiceTable::detectExtendsInvoiceConfiguration - Failed to detect configuration', [
				'trait' => 'ExtendsInvoiceTable',
				'class' => static::class,
				'method' => __METHOD__,
				'line' => __LINE__,
				'error' => $e->getMessage()
			]);
			return false;
		}
	}

	/**
	 * Resolve invoice table name from constant or default
	 * 
	 * @return string
	 */
	protected function resolveExtendsInvoiceTableName(): string
	{
		try {
			if (\defined(DC::class . '::TABLE_INVS')) {
				Log::debug('ExtendsInvoiceTable::resolveExtendsInvoiceTableName - Using DC::TABLE_INVS constant', [
					'trait' => 'ExtendsInvoiceTable',
					'class' => static::class,
					'table' => DC::TABLE_INVS
				]);
				return DC::TABLE_INVS;
			}
		} catch (\Throwable $e) {
			Log::debug('ExtendsInvoiceTable::resolveExtendsInvoiceTableName - DC::TABLE_INVS not available', [
				'trait' => 'ExtendsInvoiceTable',
				'class' => static::class,
				'error' => $e->getMessage()
			]);
		}

		Log::debug('ExtendsInvoiceTable::resolveExtendsInvoiceTableName - Using default invoices table', [
			'trait' => 'ExtendsInvoiceTable',
			'class' => static::class
		]);

		return 'invoices';
	}

	/**
	 * Detect foreign key column in the model table
	 * 
	 * @return string|null
	 */
	protected function detectExtendsInvoiceForeignKeyColumn(): ?string
	{
		try {
			$tableName = $this->getTable();
			$candidateColumns = [];

			try {
				if (\defined(BC::class . '::COL_INV_ID'))
					$candidateColumns[] = BC::COL_INV_ID;
			} catch (\Throwable $e) {
				Log::debug('ExtendsInvoiceTable::detectExtendsInvoiceForeignKeyColumn - BC::COL_INV_ID not available, using fallback', [
					'trait' => 'ExtendsInvoiceTable',
					'class' => static::class,
					'error' => $e->getMessage()
				]);
				$candidateColumns[] = 'invoice_id';
			}

			$candidateColumns[] = 'invoice';

			foreach ($candidateColumns as $column) {
				if (!Schema::hasColumn($tableName, $column)) continue;

				$columnType = Schema::getColumnType($tableName, $column);

				if ($this->isExtendsInvoiceValidForeignKeyType($columnType)) {
					Log::debug('ExtendsInvoiceTable::detectExtendsInvoiceForeignKeyColumn - Foreign key column detected', [
						'trait' => 'ExtendsInvoiceTable',
						'class' => static::class,
						'column' => $column,
						'type' => $columnType
					]);
					return $column;
				}
			}

			Log::warning('ExtendsInvoiceTable::detectExtendsInvoiceForeignKeyColumn - No valid foreign key column found', [
				'trait' => 'ExtendsInvoiceTable',
				'class' => static::class,
				'method' => __METHOD__,
				'line' => __LINE__,
				'candidates' => $candidateColumns
			]);

			return null;
		} catch (\Throwable $e) {
			Log::error('ExtendsInvoiceTable::detectExtendsInvoiceForeignKeyColumn - Failed to detect foreign key', [
				'trait' => 'ExtendsInvoiceTable',
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
	protected function isExtendsInvoiceValidForeignKeyType(string $type): bool
	{
		$validTypes = ['string', 'integer', 'bigint', 'smallint', 'uuid'];
		return \in_array(\strtolower($type), $validTypes, true);
	}

	/**
	 * Handle model saved event
	 */
	protected function handleExtendsInvoiceSaved(): void
	{
		if (!static::$extendsInvoiceInitialized) return;

		$foreignKeyValue = $this->getAttribute(static::$extendsInvoiceForeignKeyColumn);

		$this->synchronizeExtendsBaseTableColumns(
			static::$extendsInvoiceTableName,
			static::$extendsInvoiceForeignKeyColumn,
			$foreignKeyValue
		);
	}
}
