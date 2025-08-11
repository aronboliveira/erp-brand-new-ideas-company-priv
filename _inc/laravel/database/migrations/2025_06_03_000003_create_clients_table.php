<?php

use App\Config\Constants\{DatabaseConstants, UsersConstants};
use Illuminate\Database\{Migrations\Migration, Schema\BluePrint};
use Illuminate\Support\Facades\{Schema, Log};

class CreateClientsTable extends Migration
{
	private const TABLE      = DatabaseConstants::TABLE_CLIENTS;
	private const COL_USER   = UsersConstants::COL_USER_ID;
	private const COL_CREATOR = DatabaseConstants::TABLE_CREATOR;

	public function up(): void
	{
		Schema::create(self::TABLE, function (Blueprint $table): void {
			$table->uuid('id')->primary();
			$table->string(UsersConstants::COL_NM)->nullable();
			$table->string(UsersConstants::COL_EM)->unique()->nullable();
			$table->timestamp(UsersConstants::COL_EM_V_AT)->nullable();
			$table->string(UsersConstants::COL_PW)->nullable();
			$table->string(UsersConstants::COL_LG, 100)->default(DatabaseConstants::DEFAULT_LANG);
			$table->integer(UsersConstants::COL_IA)->default(1);
			$table->uuid(self::COL_USER)->nullable();
			$table->string(UsersConstants::COL_TEL)->nullable();
			$table->string(UsersConstants::COL_ADR)->nullable();
			$table->boolean(UsersConstants::COL_IU)->default(false);
			$table->string(UsersConstants::COL_AV)->default(config('chatify.user_avatar.default'));
			$table->string(UsersConstants::COL_MSG_CL)->default('#2180f3');
			$table->integer(UsersConstants::COL_DEL_STT)->default(1);
			$table->uuid(self::COL_CREATOR);
			$table->timestamps();
			$table->foreign(self::COL_USER)
				->references('id')
				->on(DatabaseConstants::TABLE_USERS)
				->nullOnDelete();
			$table->foreign(self::COL_CREATOR)
				->references('id')
				->on(DatabaseConstants::TABLE_USERS)
				->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			foreach ([self::COL_USER, self::COL_CREATOR] as $column) {
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
