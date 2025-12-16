<?php

use App\Config\Constants\{DatabaseConstants as DC, NotificationsConstants as NC, MessagesConstants as MC};
use App\Traits\{HasNullableAuditColumns, IsTemplateLang};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateNotificationTemplateLangsTable extends Migration
{
	use HasNullableAuditColumns, IsTemplateLang;
	private const TABLE = DC::TABLE_NOTIFICATION_TEMPLATE_LANGS;
	public function up(): void
	{
		if (!Schema::hasTable(self::TABLE))
			Schema::create(self::TABLE, function (Blueprint $table) {
				$table->uuid('id')->primary();
				$table->uuid(NC::COL_TEMPL_PR)->index();
				$this->addTemplateLangColumns($table);
				$table->unique([NC::COL_TEMPL_PR, 'lang']);
				$table->foreign(NC::COL_TEMPL_PR)
					->references('id')
					->on(DC::TABLE_NOTIFICATION_TEMPLATES)
					->cascadeOnDelete();
				$this->addAuditColumns($table);
			});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table, self::TABLE);
			foreach (
				[
					NC::COL_TEMPL_PR,
					MC::COL_TRL_ID,
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
