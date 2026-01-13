<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;
//todo the api for chatify needs to be read for this
class CreateChFavoritesTable extends Migration
{
	private const TABLE = DC::TABLE_CHTF_FV;
	private const COL_FAV = 'favorite_id';
	public function up(): void
	{
		if (!Schema::hasTable(self::TABLE))
			Schema::create(self::TABLE, function (Blueprint $table): void {
				$table->uuid('id')->primary();
				$table->uuid(UC::COL_USER_ID)->index();
				$table->uuid(self::COL_FAV);
				$table->index([UC::COL_USER_ID, self::COL_FAV]);
				$table->timestamps();
				$table->foreign(UC::COL_USER_ID)
					->references('id')
					->on(DC::TABLE_USERS)
					->cascadeOnDelete();
			});
	}
	public function down(): void
	{
		Schema::dropIfExists(self::TABLE);
	}
}
