<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// * this table is redundant and should mostly reference a row in DC::TABLE_PROD_SERVS
class CreateProductsTable extends Migration
{
	use HasNullableAuditColumns;
	private const TABLE = DC::TABLE_PRD;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$table->uuid(BC::COL_PRD_SV_ID)->nullable()->index();
			$table->string('name')->index();
			$table->decimal('price', 16, 2)->default(0.00);
			$table->unsignedInteger('quantity')->default(0)->nullable();
			$table->text('description')->nullable();
			$table->string('image')->nullable();
			$table->string('type')->nullable();
			$table->foreign(BC::COL_PRD_SV_ID)
				->references('id')
				->on(DC::TABLE_PROD_SERVS)
				->nullOnDelete();
			$this->addAuditColumns($table);
		});
	}
	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table, self::TABLE);
			try {
				Schema::hasColumn(self::TABLE, BC::COL_PRD_SV_ID)
					&& $table->dropForeign([BC::COL_PRD_SV_ID]);
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
