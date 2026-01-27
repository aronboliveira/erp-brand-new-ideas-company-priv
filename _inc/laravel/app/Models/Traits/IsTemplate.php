<?php

namespace App\Traits;

use App\Config\Constants\{ActivitiesConstants as AC, MessagesConstants as MC};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait IsTemplate
{
	public function addTemplateColumns(Blueprint $table): void
	{
		$table->string('slug')->nullable();
		$table->text('description')->nullable();
		$table->datetime(AC::COL_AV_FROM)->nullable()->useCurrent(); // * this should be refresh on booted, specially if null // ? nullable for testing
		$table->boolean(AC::COL_DSB)->nullable()->default(false); // * this should be refresh on booted, specially if null // ? nullable for testing
		$table->json('categories')->nullable(); // * since there is no 'NotificationCategory' model for now, this is just a array<string>
		$table->json(MC::COL_EX_PLN)->nullable(); // * an array of string pointing to the id, query_key or name of a Plan, to be queried against the Plan model, and filter out those who do not match any of these, and later used by the Controller to rule out usage // ? nullable to avoid issues with existing data
		$table->json('rules')->nullable(); //* this should contain an array with fields that represent columns found in the Notification migration, and impose valid constraints (such as max, min for a string column, acceptable attachment mimes, etc) that do not conflict with the own Notification migration, while unfitting rule keys are filtered out // ? nullable to avoid issues with existing data
		$table->json(MC::COL_AV_LG)->nullable(); // * an array of string representing the available languages for this template // ? at boot should fill with DC::DEFAULT_LANG and AvailableLang::PtBr->value (from an enum). These should be used to query into the Language model ('languages' table, with the 'code' or 'full_name' column) for initial filtering and later updates filtering. // ? nullable to avoid issues with existing data
		$table->json('tags')->nullable();
	}
	public function dropTemplateColumns(Blueprint $table, ?string $tableName): void
	{
		$columns = [
			'slug',
			'description',
			AC::COL_AV_FROM,
			AC::COL_DSB,
			'categories',
			MC::COL_EX_PLN,
			'rules',
			MC::COL_AV_LG,
			'tags',
		];
		$tableName ??= $table->getTable();
		foreach ($columns as $column) {
			try {
				Schema::hasColumn($tableName, $column)
					&& $table->dropColumn($column);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to drop column '
						. $column
						. ' on table '
						. $tableName
						. ': '
						. $e->getMessage()
				);
			}
		}
	}
}
