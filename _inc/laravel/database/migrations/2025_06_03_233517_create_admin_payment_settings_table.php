<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateAdminPaymentSettingsTable extends Migration
{
    private const TABLE = 'admin_payment_settings';
    public function up(): void
    {
        Schema::create(
            self::TABLE,
            function (Blueprint $table) {
                $table->uuid('id')->primary(); // ! CHANGED
                $table->string('name');
                $table->string('value');
                $table->uuid(DatabaseConstants::COL_TABLE_CREATOR); // ! CHANGED
                $table->timestamps();
                $table->unique(['name', DatabaseConstants::COL_TABLE_CREATOR]);
                $table->foreign(DatabaseConstants::COL_TABLE_CREATOR)
                    ->references('id')
                    ->on(DatabaseConstants::TABLE_USERS)
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, DatabaseConstants::COL_TABLE_CREATOR)
                    && $table->dropForeign([DatabaseConstants::COL_TABLE_CREATOR]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DatabaseConstants::COL_TABLE_CREATOR
                        . ' on table '
                        . self::TABLE
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
