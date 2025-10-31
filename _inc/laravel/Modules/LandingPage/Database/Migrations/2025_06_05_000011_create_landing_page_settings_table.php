<?php

use App\Config\Constants\{DatabaseConstants, LandingPageConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLandingPageSettingsTable extends Migration
{

    private const TABLE = DatabaseConstants::TABLE_LPS;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary(); // ! CHANGED
                $table->uuid("query_key")->unique()->index();
                $table->string(LandingPageConstants::COL_LPS_NM);
                $table->longtext(LandingPageConstants::COL_LPS_V)->nullable();
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
