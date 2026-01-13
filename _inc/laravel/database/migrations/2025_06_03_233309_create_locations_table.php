<?php

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// * it's not clear why this table exists. Maybe if was attempt on legacy to link information for a User::whereIn('type', ['company', 'vendor']).
class CreateLocationsTable extends Migration
{
	use HasNullableAuditColumns;
	private const TABLE = DC::TABLE_LOC;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$table->uuid(CC::COL_CP_ID)->index();
			$table->json('location')->nullable();
			$table->boolean(CC::COL_IA)->default(true);
			$table->foreign(CC::COL_CP_ID)
				->references('id')
				->on(DC::TABLE_USERS)
				->cascadeOnDelete();
			$this->addNullableAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table);
			foreach (
				[
					CC::COL_CP_ID,
				] as $column
			) {
				try {
					Schema::hasColumn(self::TABLE, $column)
						&& $table->dropForeign([$column]);
				} catch (\Exception $e) {
					Log::warning(
						'Failed to drop foreign key for '
							. $column
							. ' on table '
							. self::TABLE
							. ': '
							. $e->getMessage()
					);
				}
			}
		});
		Schema::dropIfExists(self::TABLE);
	}
}
