<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateIpRestrictsTable extends Migration
{
	private const TABLE = 'ip_restricts';

	public function up(): void
	{
		if (!Schema::hasTable(self::TABLE))
			Schema::create(self::TABLE, function (Blueprint $table) {
				$table->uuid('id')->primary();           // ! CHANGED
				$table->string('ip', 45);
				$table->uuid(DatabaseConstants::COL_TABLE_CREATOR);              // ! CHANGED
				$table->timestamps();
				$table->foreign(DatabaseConstants::COL_TABLE_CREATOR)
					->references('id')
					->on(DatabaseConstants::TABLE_USERS)
					->onDelete('cascade');
			});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			try {
				Schema::hasColumn(self::TABLE, DatabaseConstants::COL_TABLE_CREATOR)
					&& $table->dropForeign([DatabaseConstants::COL_TABLE_CREATOR]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to execute down for '
						. DatabaseConstants::COL_TABLE_CREATOR
						. ' foreign key column: '
						. $e->getMessage()
				);
			}
		});
		Schema::dropIfExists(self::TABLE);
	}
}
