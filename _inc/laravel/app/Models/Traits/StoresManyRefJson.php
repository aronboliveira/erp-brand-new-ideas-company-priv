<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC};
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Throwable;

trait StoresManyRefJson
{
	protected static function bootStoresManyRefJson(): void
	{
		static::saving(function (Model $model) {
			$tableName = $model->getTable();
			$modelKey = $model->getKeyName();
			$modelKeyValue = $model->getKey();

			Log::notice("Starting many-ref JSON validation for model", [
				'table' => $tableName,
				'key' => $modelKey,
				'key_value' => $modelKeyValue
			]);

			// Skip if no key value (new record)
			if (!$modelKeyValue) {
				Log::notice("No key value present - skipping many-ref JSON validation for new record", [
					'table' => $tableName
				]);
				return;
			}

			// Verify existing record
			try {
				$existingRecord = DB::table($tableName)
					->where($modelKey, $modelKeyValue)
					->first();

				if (!$existingRecord) {
					Log::notice("No existing record found - skipping many-ref JSON validation", [
						'table' => $tableName,
						'key_value' => $modelKeyValue
					]);
					return;
				}
			} catch (Throwable $e) {
				Log::error("Failed to retrieve existing record for many-ref JSON validation", [
					'table' => $tableName,
					'key_value' => $modelKeyValue,
					'error' => $e->getMessage()
				]);
				return;
			}

			// Define attribute-to-table mappings
			$attributeMappings = [
				'notifications' => DC::TABLE_NTF,
				'fields' => DC::TABLE_FM_FD,
				'payments' => DC::TABLE_PAY,
				'employees' => DC::TABLE_EMPLOYEES,
				'sources' => 'sources',
				'questions' => DC::TABLE_CUSTOM_QUESTIONS,
				'branches' => DC::TABLE_BRANCHES,
				'departments' => DC::TABLE_DEPARTMENTS,
				'designations' => DC::TABLE_DESIGNS,
				'taxes' => DC::TABLE_TAXES,
				'jobs' => DC::TABLE_JOBS,
				'projects' => DC::TABLE_PROJECTS,
				'transactions' => DC::TABLE_TRS,
				'vendors' => DC::TABLE_VENDORS,
				'customers' => DC::TABLE_CUSTOMERS,
				'clients' => DC::TABLE_CLIENTS
			];

			foreach ($attributeMappings as $attribute => $referenceTable) {
				self::processJsonReferenceAttribute(
					$model,
					$attribute,
					$referenceTable,
					$tableName,
					$modelKeyValue
				);
			}

			Log::notice("Completed many-ref JSON validation for model", [
				'table' => $tableName,
				'key_value' => $modelKeyValue
			]);
		});
	}

	/**
	 * Process a single JSON reference attribute
	 */
	protected static function processJsonReferenceAttribute(
		Model $model,
		string $attribute,
		string $referenceTable,
		string $tableName,
		mixed $modelKeyValue
	): void {
		// Verify schema requirements
		try {
			if (!Schema::hasTable($referenceTable)) {
				Log::debug("Reference table does not exist - skipping attribute", [
					'attribute' => $attribute,
					'reference_table' => $referenceTable
				]);
				return;
			}

			if (!Schema::hasColumn($tableName, $attribute)) {
				Log::debug("Attribute column does not exist - skipping", [
					'table' => $tableName,
					'attribute' => $attribute
				]);
				return;
			}

			if (Schema::getColumnType($tableName, $attribute) !== 'json') {
				Log::debug("Attribute is not JSON type - skipping", [
					'table' => $tableName,
					'attribute' => $attribute,
					'actual_type' => Schema::getColumnType($tableName, $attribute)
				]);
				return;
			}
		} catch (Throwable $e) {
			Log::error("Schema validation failed for attribute", [
				'table' => $tableName,
				'attribute' => $attribute,
				'reference_table' => $referenceTable,
				'error' => $e->getMessage()
			]);
			return;
		}

		Log::notice("Processing JSON reference attribute", [
			'table' => $tableName,
			'attribute' => $attribute,
			'reference_table' => $referenceTable
		]);

		// Handle special bidirectional relationships
		if (in_array($attribute, ['fields'], true)) {
			self::handleBidirectionalReference($model, $attribute, $referenceTable, $modelKeyValue);
		} else {
			self::handleUnidirectionalReference($model, $attribute, $referenceTable);
		}
	}

	/**
	 * Handle bidirectional references (where both sides know about each other)
	 */
	protected static function handleBidirectionalReference(
		Model $model,
		string $attribute,
		string $referenceTable,
		mixed $modelKeyValue
	): void {
		try {
			switch ($attribute) {
				case 'fields':
					Log::notice("Fetching bidirectional field references", [
						'reference_table' => $referenceTable,
						'form_id' => $modelKeyValue
					]);

					$existingIds = DB::table($referenceTable)
						->where(FC::COL_FM_ID, $modelKeyValue)
						->pluck('id')
						->toArray();

					$model->setAttribute($attribute, json_encode($existingIds));

					Log::notice("Successfully stored bidirectional field references", [
						'attribute' => $attribute,
						'count' => count($existingIds)
					]);
					break;

				default:
					Log::warning("Unknown bidirectional attribute type", [
						'attribute' => $attribute
					]);
					break;
			}
		} catch (Throwable $e) {
			Log::error("Failed to handle bidirectional reference", [
				'attribute' => $attribute,
				'reference_table' => $referenceTable,
				'error' => $e->getMessage()
			]);
		}
	}

	/**
	 * Handle unidirectional references (standard FK validation)
	 */
	protected static function handleUnidirectionalReference(
		Model $model,
		string $attribute,
		string $referenceTable
	): void {
		try {
			// Decode existing IDs
			$rawValue = $model->getAttribute($attribute);
			$existingIds = $rawValue ? json_decode($rawValue, true) : [];

			if (!is_array($existingIds)) {
				Log::warning("Invalid JSON array for attribute - resetting to empty", [
					'attribute' => $attribute,
					'raw_value' => $rawValue
				]);
				$existingIds = [];
			}

			Log::notice("Validating unidirectional references", [
				'attribute' => $attribute,
				'reference_table' => $referenceTable,
				'existing_ids_count' => count($existingIds)
			]);

			// Validate base reference table
			$validIds = empty($existingIds) ? [] : DB::table($referenceTable)
				->whereIn('id', $existingIds)
				->pluck('id')
				->toArray();

			// Handle special user-type validations
			$validIds = self::mergeUserTypeReferences($attribute, $existingIds, $validIds);

			$model->setAttribute($attribute, json_encode($validIds));

			Log::notice("Successfully validated unidirectional references", [
				'attribute' => $attribute,
				'original_count' => count($existingIds),
				'valid_count' => count($validIds)
			]);
		} catch (Throwable $e) {
			Log::error("Failed to handle unidirectional reference", [
				'attribute' => $attribute,
				'reference_table' => $referenceTable,
				'error' => $e->getMessage()
			]);
		}
	}

	/**
	 * Merge user-type specific references for polymorphic relationships
	 */
	protected static function mergeUserTypeReferences(
		string $attribute,
		array $existingIds,
		array $validIds
	): array {
		if (empty($existingIds)) {
			return $validIds;
		}

		try {
			switch ($attribute) {
				case 'vendors':
					Log::notice("Merging vendor user references");
					$userIds = DB::table(DC::TABLE_USERS)
						->where('type', UserType::Vendor->value)
						->whereIn('id', $existingIds)
						->pluck('id')
						->toArray();
					return array_values(array_merge($validIds, $userIds));

				case 'customers':
					Log::notice("Merging customer user references");
					$userIds = DB::table(DC::TABLE_USERS)
						->where('type', UserType::Customer->value)
						->whereIn('id', $existingIds)
						->pluck('id')
						->toArray();
					return array_values(array_merge($validIds, $userIds));

				case 'clients':
					Log::notice("Merging client user references");
					$userIds = DB::table(DC::TABLE_USERS)
						->where('type', UserType::Client->value)
						->whereIn('id', $existingIds)
						->pluck('id')
						->toArray();
					return array_values(array_merge($validIds, $userIds));

				default:
					return $validIds;
			}
		} catch (Throwable $e) {
			Log::error("Failed to merge user-type references", [
				'attribute' => $attribute,
				'error' => $e->getMessage()
			]);
			return $validIds;
		}
	}
}
