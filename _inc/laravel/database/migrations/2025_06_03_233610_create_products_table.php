<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// * this table is redundant and should mostly reference a row in DC::TABLE_PROD_SERVS
class CreateProductsTable extends Migration
{
	private const TABLE = DC::TABLE_PRD;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$table->string('name');
			$table->decimal('price', 16, 2)->default(0.00);
			$table->text('description')->nullable();
			$table->string('image')->nullable();
			$table->string('type')->nullable();
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
