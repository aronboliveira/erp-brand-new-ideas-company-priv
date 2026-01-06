<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// * this table is mostly redundante and should just reference a row in DC::TABLE_PROD_SERV_CATS
class CreateProductCategoriesTable extends Migration
{
	private const TABLE = DC::TABLE_PRD_CAT;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('name');
			$table->text('description')->nullable();
			$table->uuid(DC::COL_TABLE_CREATOR)->index();
			$table->timestamps();
			$table->foreign(DC::COL_TABLE_CREATOR)
				->references('id')
				->on(DC::TABLE_USERS)
				->cascadeOnDelete();
			$table->uuid(PJC::COL_PRD_SERV_CAT_ID)->nullable()->index();
			$table->foreign(PJC::COL_PRD_SERV_CAT_ID)
				->references('id')
				->on(DC::TABLE_PROD_SERV_CATS)
				->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			try {
				if (Schema::hasColumn(self::TABLE, DC::COL_TABLE_CREATOR))
					$table->dropForeign([DC::COL_TABLE_CREATOR]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to drop foreign key for '
						. DC::COL_TABLE_CREATOR
						. ' on table '
						. self::TABLE
						. ': '
						. $e->getMessage()
				);
			}
		});
		Schema::dropIfExists(self::TABLE);
	}
}
