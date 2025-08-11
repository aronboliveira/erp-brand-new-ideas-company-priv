<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJoinUsTable extends Migration
{

    private const TABLE = 'join_us';

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('email')->unique();
                $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
                $table->timestamps();
                $table->foreign(DatabaseConstants::TABLE_CREATOR)
                    ->references('id')
                    ->on(DatabaseConstants::TABLE_USERS)
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, DatabaseConstants::TABLE_CREATOR) &&
                    $table->dropForeign([DatabaseConstants::TABLE_CREATOR]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DatabaseConstants::TABLE_CREATOR
                        . ' on table '
                        . self::TABLE
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
};
