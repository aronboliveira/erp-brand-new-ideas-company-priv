<?php

use App\Config\Constants\{DatabaseConstants as DC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// todo this will change after meeting with DevOps and Sec team
class CreateIpRestrictsTable extends Migration
{
	private const TABLE = DC::TABLE_IP_RST;

	public function up(): void
	{
		if (!Schema::hasTable(self::TABLE))
			Schema::create(self::TABLE, function (Blueprint $table) {
				$table->uuid('id')->primary();
				$table->ipAddress('ip')->index();
				$table->uuid(DC::COL_TABLE_CREATOR);
				$table->timestamps();
				$table->foreign(DC::COL_TABLE_CREATOR)
					->references('id')
					->on(DC::TABLE_USERS)
					->onDelete('cascade');
			});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			try {
				Schema::hasColumn(self::TABLE, DC::COL_TABLE_CREATOR)
					&& $table->dropForeign([DC::COL_TABLE_CREATOR]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to execute down for '
						. DC::COL_TABLE_CREATOR
						. ' foreign key column: '
						. $e->getMessage()
				);
			}
		});
		Schema::dropIfExists(self::TABLE);
	}
}
