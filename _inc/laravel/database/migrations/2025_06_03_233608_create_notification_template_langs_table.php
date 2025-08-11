<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateNotificationTemplateLangsTable extends Migration
{
	private const TABLE = DatabaseConstants::TABLE_NOTIFICATION_TEMPLATE_LANGS;
	private const COL_PARENT = 'parent_id';
	public function up(): void
	{
		if (!Schema::hasTable(self::TABLE))
			Schema::create(self::TABLE, function (Blueprint $table) {
				$table->uuid('id')->primary();                           // ! CHANGED
				$table->uuid(self::COL_PARENT);
				$table->string('lang', 10)->default(DatabaseConstants::DEFAULT_LANG);
				$table->text('content');
				$table->json('variables')->nullable();                    // * stores template variables
				$table->timestamps();
				$table->uuid(DatabaseConstants::TABLE_CREATOR);
				foreach ([
					self::COL_PARENT                => DatabaseConstants::TABLE_NOTIFICATION_TEMPLATES,
					DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
				] as $column => $referencedTable) {
					$table->foreign($column)
						->references('id')
						->on($referencedTable)
						->cascadeOnDelete();
				}
			});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			foreach ([
				self::COL_PARENT,
				DatabaseConstants::TABLE_CREATOR,
			] as $column) {
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
