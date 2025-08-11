<?php

use App\Config\Constants\{DatabaseConstants, UsersConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateSettingsTable extends Migration
{
    private const TABLE_NAME = DatabaseConstants::TABLE_SETTINGS;
    private const COL_NAME = 'name';
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->string(self::COL_NAME)->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique([self::COL_NAME, DatabaseConstants::TABLE_CREATOR]);
            $table->uuid(DatabaseConstants::TABLE_CREATOR); // ! CHANGED
            $table->foreign(DatabaseConstants::TABLE_CREATOR)
                ->references('id')
                ->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete();
            $table->uuid(UsersConstants::COL_USER_ID)->nullable();
            $table->foreign(UsersConstants::COL_USER_ID)
                ->references('id')
                ->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete();
            $table->unique(
                [self::COL_NAME, DatabaseConstants::TABLE_CREATOR, UsersConstants::COL_USER_ID],
                self::TABLE_NAME
                    . '_' . self::COL_NAME
                    . '_' . DatabaseConstants::TABLE_CREATOR
                    . '_' . UsersConstants::COL_USER_ID . '_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE_NAME, DatabaseConstants::TABLE_CREATOR) &&
                    $table->dropForeign([DatabaseConstants::TABLE_CREATOR]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DatabaseConstants::TABLE_CREATOR
                        . ' on table '
                        . self::TABLE_NAME
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
