<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\BluePrint};
use Illuminate\Support\Facades\{Schema, Log};

class CreateClientsTable extends Migration
{
	use HasNullableAuditColumns;
	private const TABLE      = DC::TABLE_CLIENTS;
	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$table->string(UC::COL_NM)->nullable();
			$table->string(UC::COL_EM)->nullable()->unique();
			$table->timestamp(UC::COL_EM_V_AT)->nullable();
			$table->string(UC::COL_PW)->nullable();
			$table->string(UC::COL_LG, 100)->default(DC::DEFAULT_LANG);
			$table->integer(UC::COL_IA)->default(1);
			$table->uuid(UC::COL_USER_ID)->nullable();
			$table->string(UC::COL_TEL)->nullable();
			$table->string(UC::COL_ADR)->nullable();
			$table->boolean(UC::COL_IU)->default(false);
			$table->string(UC::COL_AV)->default(config('chatify.user_avatar.default'));
			$table->string(UC::COL_MSG_CL)->default('#2180f3');
			$table->integer(UC::COL_DEL_STT)->default(1);
			$table->foreign(UC::COL_USER_ID)
				->references('id')
				->on(DC::TABLE_USERS)
				->nullOnDelete();
			$this->addAuditColumns($table);
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$this->dropAuditColumnForeigns($table, self::TABLE);
			foreach ([UC::COL_USER_ID] as $column) {
				try {
					Schema::hasColumn(self::TABLE, $column) &&
						$table->dropForeign([$column]);
				} catch (\Exception $e) {
					Log::warning(
						"Failed to drop foreign key for {$column} on table "
							. self::TABLE . ": " . $e->getMessage()
					);
				}
			}
		});
		Schema::dropIfExists(self::TABLE);
	}
}
