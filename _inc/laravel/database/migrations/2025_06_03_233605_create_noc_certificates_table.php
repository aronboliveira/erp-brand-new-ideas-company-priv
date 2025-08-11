<?php

use App\Config\Constants\{DatabaseConstants, TemplatesConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateNocCertificatesTable extends Migration
{
	private const TABLE = DatabaseConstants::TABLE_NOC;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string(TemplatesConstants::COL_LG, 10)->default(DatabaseConstants::DEFAULT_LANG)->index();
			$table->text(TemplatesConstants::COL_CT);
			$table->uuid(DatabaseConstants::TABLE_CREATOR)->index();
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
