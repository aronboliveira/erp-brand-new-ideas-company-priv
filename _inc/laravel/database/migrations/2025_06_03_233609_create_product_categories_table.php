<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// * this table is mostly redundante and should just reference a row in DC::TABLE_PROD_SERV_CATS
class CreateProductCategoriesTable extends Migration
{
	use HasNullableAuditColumns;
	private const TABLE = DC::TABLE_PRD_CAT;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('name');
			$table->text('description')->nullable();
			$table->uuid(PJC::COL_PRD_SERV_CAT_ID)->nullable()->index();
			$table->foreign(PJC::COL_PRD_SERV_CAT_ID)
				->references('id')
				->on(DC::TABLE_PROD_SERV_CATS)
				->nullOnDelete();
			$table->json('tags');
			$this->addAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table, self::TABLE);
			try {
				Schema::hasColumn(self::TABLE, PJC::COL_PRD_SERV_CAT_ID)
					&& $table->dropForeign([PJC::COL_PRD_SERV_CAT_ID]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to execute down for '
						. PJC::COL_PRD_SERV_CAT_ID
						. ' foreign key column: '
						. $e->getMessage()
				);
			}
		});
		Schema::dropIfExists(self::TABLE);
	}
}
