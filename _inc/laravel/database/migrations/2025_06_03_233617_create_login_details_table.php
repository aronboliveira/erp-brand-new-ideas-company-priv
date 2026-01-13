<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// todo this will change after meeting with DevOps and Sec team
class CreateLoginDetailsTable extends Migration
{
	use HasNullableAuditColumns;
	private const TABLE = DC::TABLE_LG_DTLS;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->uuid(UC::COL_USER_ID)->index();
			$table->ipAddress('ip')->index();
			$table->timestamp('date')->index();
			$table->string('user_agent')->nullable();
			$table->text('details');
			$table->foreign(UC::COL_USER_ID)
				->references('id')
				->on(DC::TABLE_USERS)
				->cascadeOnDelete();
			$this->addNullableAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table, self::TABLE);
			foreach (
				[
					UC::COL_USER_ID,
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
