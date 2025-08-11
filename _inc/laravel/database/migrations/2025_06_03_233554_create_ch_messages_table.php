<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

class CreateChMessagesTable extends Migration
{
	private const TABLE = 'ch_messages';
	private const FROM_COL = 'from_id';
	private const TO_COL = 'to_id';
	public function up(): void
	{
		if (!Schema::hasTable(self::TABLE))
			Schema::create(self::TABLE, function (Blueprint $table) {
				$table->uuid('id')->primary();                 // ! CHANGED
				$table->uuid(self::FROM_COL);                       // ! CHANGED
				$table->uuid(self::TO_COL);                         // ! CHANGED
				$table->text('message');                       // * text body of message
				$table->boolean('seen')->default(false);       // * 0 = unseen, 1 = seen
				$table->timestamps();
				foreach ([
					self::FROM_COL                    => DatabaseConstants::TABLE_USERS,
					self::TO_COL                      => DatabaseConstants::TABLE_USERS,
				] as $column => $referencedTable)
					$table->foreign($column)
						->references('id')
						->on($referencedTable)
						->onDelete('cascade');
			});
	}
	public function down(): void
	{
		if (Schema::hasTable(self::TABLE)) {
			Schema::table(self::TABLE, function (Blueprint $table): void {
				foreach ([
					['column' => self::FROM_COL, 'constraint' => self::TABLE . '_from_id_foreign'],
					['column' => self::TO_COL,   'constraint' => self::TABLE . '_to_id_foreign'],
				] as $fk) {
					try {
						if ($this->foreignKeyExists(self::TABLE, $fk['constraint'])) {
							$table->dropForeign([$fk['column']]);
						}
					} catch (\Exception $e) {
						Log::warning(
							'Failed to drop foreign key '
								. $fk['constraint']
								. ' on table '
								. self::TABLE
								. ': '
								. $e->getMessage()
						);
					}
				}
			});
		}
		Schema::dropIfExists(self::TABLE);
	}
	private function foreignKeyExists(string $table, string $constraintName): bool
	{
		$database = DB::connection()->getDatabaseName();
		$result = DB::select("
					SELECT CONSTRAINT_NAME 
					FROM information_schema.TABLE_CONSTRAINTS 
					WHERE CONSTRAINT_SCHEMA = ? 
					AND TABLE_NAME = ? 
					AND CONSTRAINT_NAME = ? 
					AND CONSTRAINT_TYPE = 'FOREIGN KEY'
			", [$database, $table, $constraintName]);

		return !empty($result);
	}
}
