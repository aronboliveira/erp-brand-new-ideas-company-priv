<?php
// database/migrations/xxxx_xx_xx_create_joining_letter.s_table.php

use App\Config\Constants\{
	DatabaseConstants,
	TemplatesConstants
};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

class CreateJoiningLettersTable extends Migration
{
	private const TABLE = DatabaseConstants::TABLE_JL;

	public function up(): void
	{
		if (!Schema::hasTable(self::TABLE)) {
			Schema::create(self::TABLE, function (Blueprint $table) {
				$table->uuid('id')->primary();
				$table->string(TemplatesConstants::COL_LG, 10)
					->default(DatabaseConstants::DEFAULT_LANG)
					->index();
				$table->longText(TemplatesConstants::COL_CT);
				$table->uuid(DatabaseConstants::COL_TABLE_CREATOR)
					->index();
				$table->timestamps();

				$table->foreign(DatabaseConstants::COL_TABLE_CREATOR)
					->references('id')
					->on(DatabaseConstants::TABLE_USERS)
					->cascadeOnDelete();
			});
		}
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			try {
				if (Schema::hasColumn(self::TABLE, DatabaseConstants::COL_TABLE_CREATOR)) {
					$table->dropForeign([DatabaseConstants::COL_TABLE_CREATOR]);
				}
			} catch (\Exception $e) {
				Log::warning(
					'Failed to drop FK '
						. DatabaseConstants::COL_TABLE_CREATOR
						. ' on ' . self::TABLE . ': '
						. $e->getMessage()
				);
			}
		});
		Schema::dropIfExists(self::TABLE);
	}
}
