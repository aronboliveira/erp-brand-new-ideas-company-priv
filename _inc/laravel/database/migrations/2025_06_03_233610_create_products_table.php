<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProductsTable extends Migration
{
	private const TABLE = 'products';
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();             // ! CHANGED: use UUIDs
			$table->string('name');
			$table->decimal('price', 16, 2)->default(0.00);
			$table->text('description')->nullable();
			$table->string('image')->nullable();
			$table->string('type')->nullable();
			$table->uuid(DatabaseConstants::TABLE_CREATOR);                // ! CHANGED: references users.id
			$table->timestamps();
			$table->foreign(DatabaseConstants::TABLE_CREATOR)
				->references('id')
				->on(DatabaseConstants::TABLE_USERS)
				->onDelete('cascade');
		});
	}
	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			try {
				Schema::hasColumn(self::TABLE, DatabaseConstants::TABLE_CREATOR)
					&& $table->dropForeign([DatabaseConstants::TABLE_CREATOR]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to execute down for '
						. DatabaseConstants::TABLE_CREATOR
						. ' foreign key column: '
						. $e->getMessage()
				);
			}
		});
		Schema::dropIfExists(self::TABLE);
	}
}
