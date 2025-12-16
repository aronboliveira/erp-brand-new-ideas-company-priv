<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC, MessagesConstants as MC};
use App\Enums\AvailableLang;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait IsTemplateLang
{
	public function addTemplateLangColumns(Blueprint $table): void
	{
		$table->enum('lang', array_column(AvailableLang::cases(), 'value'))->default(DC::DEFAULT_LANG)->index();
		$table->string('translator', 254)->nullable()->default(DC::DEFAULT_UUID); // ? the name of someone who translated this template
		$table->uuid(MC::COL_TRL_ID)->nullable()->default(DC::DEFAULT_UUID)->index(); // ? not every translation will have an account
		$table->text('content');
		$table->json('variables')->nullable();
		$table->json('metadata')->nullable();
		$table->foreign(MC::COL_TRL_ID)
			->references('id')
			->on(DC::TABLE_USERS)
			->nullOnDelete();
	}
	public function dropTemplateLangColumns(Blueprint $table, ?string $tableName): void
	{
		$columns = [
			'lang',
			'translator',
			MC::COL_TRL_ID,
			'content',
			'variables',
			'metadata',
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
	public function dropTemplateLangColumnForeigns(Blueprint $table, ?string $tableName): void
	{
		$columns = [
			MC::COL_TRL_ID,
		];
		$tableName ??= $table->getTable();
		foreach ($columns as $column) {
			try {
				Schema::hasColumn($tableName, $column)
					&& $table->dropForeign([$column]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to drop foreign key for '
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
