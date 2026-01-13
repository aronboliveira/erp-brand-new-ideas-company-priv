<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, FormsConstants as FC};
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Throwable;

trait StoresManyRefJson
{
	protected static function bootStoresManyRefJson(): void
	{
		// todo this should be activated in production, but it's too heavy for mocks
		// static::saving(function (Model $model) {
		// 	$tableName = $model->getTable();
		// 	$modelKey = $model->getKeyName();
		// 	$modelKeyValue = $model->getKey();

		// 	Log::notice("Starting many-ref JSON validation for model", [
		// 		'table' => $tableName,
		// 		'key' => $modelKey,
		// 		'key_value' => $modelKeyValue
		// 	]);

		// 	if (!$modelKeyValue) {
		// 		Log::notice("No key value present - skipping many-ref JSON validation for new record", [
		// 			'table' => $tableName
		// 		]);
		// 		return;
		// 	}

		// 	try {
		// 		$existingRecord = DB::table($tableName)
		// 			->where($modelKey, $modelKeyValue)
		// 			->first();

		// 		if (!$existingRecord) {
		// 			Log::notice("No existing record found - skipping many-ref JSON validation", [
		// 				'table' => $tableName,
		// 				'key_value' => $modelKeyValue
		// 			]);
		// 			return;
		// 		}
		// 	} catch (Throwable $e) {
		// 		Log::error("Failed to retrieve existing record for many-ref JSON validation", [
		// 			'table' => $tableName,
		// 			'key_value' => $modelKeyValue,
		// 			'error' => $e->getMessage()
		// 		]);
		// 		return;
		// 	}

		// 	// Define attribute-to-table mappings
		// 	$attributeMappings = [
		// 		'notifications' => DC::TABLE_NTF,
		// 		'fields' => DC::TABLE_FM_FD,
		// 		'payments' => DC::TABLE_PAY,
		// 		'employees' => DC::TABLE_EMPLOYEES,
		// 		'sources' => 'sources',
		// 		'questions' => DC::TABLE_CUSTOM_QUESTIONS,
		// 		'branches' => DC::TABLE_BRANCHES,
		// 		'departments' => DC::TABLE_DEPARTMENTS,
		// 		'designations' => DC::TABLE_DESIGNS,
		// 		'taxes' => DC::TABLE_TAXES,
		// 		'jobs' => DC::TABLE_JOBS,
		// 		'projects' => DC::TABLE_PROJECTS,
		// 		'transactions' => DC::TABLE_TRS,
		// 		'vendors' => DC::TABLE_VENDORS,
		// 		'customers' => DC::TABLE_CUSTOMERS,
		// 		'clients' => DC::TABLE_CLIENTS,
		// 		BC::COL_BNK_TRFS => DC::TABLE_BNK_TRF,
		// 		'budgets' => DC::TABLE_BDG,
		// 		BC::COL_CARD_NTS => DC::TABLE_DB_NOTES,
		// 	];

		// 	foreach ($attributeMappings as $attribute => $referenceTable) {
		// 		self::processJsonReferenceAttribute(
		// 			$model,
		// 			$attribute,
		// 			$referenceTable,
		// 			$tableName,
		// 			$modelKeyValue
		// 		);
		// 	}

		// 	self::adjustBudget($model, $tableName);

		// 	Log::notice("Completed many-ref JSON validation for model", [
		// 		'table' => $tableName,
		// 		'key_value' => $modelKeyValue
		// 	]);
		// });
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

			$validIds = empty($existingIds) ? [] : ($attribute === BC::COL_CARD_NTS ? array_values(array_merge(DB::table(DC::TABLE_CR_NOTES)->whereIn('id', $existingIds)->pluck('id')->toArray(), DB::table(DC::TABLE_DB_NOTES)->whereIn('id', $existingIds)->pluck('id')->toArray())) : DB::table($referenceTable)
				->whereIn('id', $existingIds)
				->pluck('id')
				->toArray());

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

	protected static function adjustBudget(Model $model, string $tableName): void
	{
		try {
			if (!Schema::hasTable($tableName))
				return;
			$hasBudgetColumn = Schema::hasColumn($tableName, 'budget');
			$hasBudgetsColumn = Schema::hasColumn($tableName, 'budgets');
			$hasBudgetTable = Schema::hasTable(DC::TABLE_BDG);
			$hasBudgetAmountColumn = $hasBudgetTable && Schema::hasColumn(DC::TABLE_BDG, 'amount');
			if (!$hasBudgetColumn || !$hasBudgetsColumn || !$hasBudgetTable || !$hasBudgetAmountColumn)
				return;
			$budgetsList = $model->getAttribute('budgets');
			$budgetTotal = $model->getAttribute('budget');
			if ($budgetsList === null && $budgetTotal === null)
				return;
			$budgetTotal = is_numeric($budgetTotal) ? (float) $budgetTotal : 0.00;
			$budgetsListedTotal = 0.00;

			if (!empty($budgetsList) && is_array($budgetsList)) {
				try {
					$budgetsTable = DB::table(DC::TABLE_BDG);

					foreach ($budgetsList as $budgetId) {
						if (empty($budgetId) || (!is_int($budgetId) && !is_string($budgetId)))
							continue;
						try {
							$budgetRecord = $budgetsTable->where('id', $budgetId)->first(['amount']);

							if ($budgetRecord !== null && isset($budgetRecord->amount)) {
								$budgetAmount = is_numeric($budgetRecord->amount) ? (float) $budgetRecord->amount : 0.00;
								$budgetsListedTotal += $budgetAmount;
							}
						} catch (\Exception $e) {
							Log::debug("Failed to fetch budget amount for ID: {$budgetId}", [
								'budget_id' => $budgetId,
								'table' => $tableName,
								'error' => $e->getMessage(),
								'trait' => __TRAIT__,
								'class' => static::class,
							]);
							continue;
						}
					}
				} catch (\Exception $e) {
					Log::warning("Failed to process budgets list", [
						'table' => $tableName,
						'budgets_count' => count($budgetsList),
						'error' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
						'trait' => __TRAIT__,
						'class' => static::class,
					]);
					return;
				}
			}

			if ($budgetsListedTotal > 0.00 && $budgetTotal < $budgetsListedTotal) {
				$model->setAttribute('budget', $budgetsListedTotal);

				Log::info("Budget total adjusted to match listed budgets", [
					'table' => $tableName,
					'old_budget' => $budgetTotal,
					'new_budget' => $budgetsListedTotal,
					'budgets_count' => count($budgetsList ?? []),
					'trait' => __TRAIT__,
					'class' => static::class,
				]);
			}
		} catch (\Exception $e) {
			Log::error("Failed to validate budget totals", [
				'table' => $tableName,
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'trace' => $e->getTraceAsString(),
				'trait' => __TRAIT__,
				'class' => static::class,
			]);
		}
	}
}
