<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use Illuminate\Database\{Migrations\Migration, Schema\BluePrint};
use Illuminate\Support\Facades\{Schema, Log};

class CreateClientsTable extends Migration
{
	private const TABLE      = DC::TABLE_CLIENTS;
	private const COL_USER   = UC::COL_USER_ID;
	private const COL_CREATOR = DC::TABLE_CREATOR;

	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$table->string(UC::COL_NM)->nullable();
			$table->string(UC::COL_EM)->unique()->nullable();
			$table->timestamp(UC::COL_EM_V_AT)->nullable();
			$table->string(UC::COL_PW)->nullable();
			$table->string(UC::COL_LG, 100)->default(DC::DEFAULT_LANG);
			$table->integer(UC::COL_IA)->default(1);
			$table->uuid(self::COL_USER)->nullable();
			$table->string(UC::COL_TEL)->nullable();
			$table->string(UC::COL_ADR)->nullable();
			$table->boolean(UC::COL_IU)->default(false);
			$table->string(UC::COL_AV)->default(config('chatify.user_avatar.default'));
			$table->string(UC::COL_MSG_CL)->default('#2180f3');
			$table->integer(UC::COL_DEL_STT)->default(1);
			$table->uuid(DC::TABLE_CREATOR)->nullable();
			$table->uuid(DC::TABLE_UPDATER)->nullable();
			$table->timestamps();
			$table->foreign(self::COL_USER)
				->references('id')
				->on(DC::TABLE_USERS)
				->nullOnDelete();
			$table->foreign(DC::TABLE_CREATOR)
				->references('id')
				->on(DC::TABLE_USERS)
				->nullOnDelete();
			$table->foreign(DC::TABLE_UPDATER)
				->references('id')
				->on(DC::TABLE_USERS)
				->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			foreach ([self::COL_USER, DC::TABLE_CREATOR, DC::TABLE_UPDATER] as $column) {
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
