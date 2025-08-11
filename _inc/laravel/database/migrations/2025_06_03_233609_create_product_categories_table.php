<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProductCategoriesTable extends Migration
{
	private const TABLE = 'product_categories';
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();                // ! CHANGED
			$table->string('name');
			$table->text('description')->nullable();       // * nullable description
			$table->uuid(DatabaseConstants::TABLE_CREATOR)->index();           // ! CHANGED
			$table->timestamps();
			$table->foreign(DatabaseConstants::TABLE_CREATOR)
				->references('id')
				->on(DatabaseConstants::TABLE_USERS)
				->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			try {
				if (Schema::hasColumn(self::TABLE, DatabaseConstants::TABLE_CREATOR))
					$table->dropForeign([DatabaseConstants::TABLE_CREATOR]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to drop foreign key for '
						. DatabaseConstants::TABLE_CREATOR
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
