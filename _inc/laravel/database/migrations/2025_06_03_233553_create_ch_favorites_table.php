<?php

use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;

class CreateChFavoritesTable extends Migration
{
	private const TABLE = 'chatify_favorites';
	private const COL_USER = 'user_id';
	private const COL_FAV = 'favorite_id';
	public function up(): void
	{
		if (!Schema::hasTable(self::TABLE))
			Schema::create(self::TABLE, function (Blueprint $table): void {
				$table->uuid('id')->primary();
				$table->uuid(self::COL_USER);
				$table->uuid(self::COL_FAV);
				$table->timestamps();
				$table->index([self::COL_USER, self::COL_FAV]);
			});
	}
	public function down(): void
	{
		Schema::dropIfExists(self::TABLE);
	}
}
