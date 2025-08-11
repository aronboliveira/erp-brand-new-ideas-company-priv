<?php

use App\Config\Constants\{DatabaseConstants, EmailsConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEmailTemplatesTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_EMAIL_TEMPLATES;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();       // ! CHANGED
                $table->string(EmailsConstants::COL_TT);
                $table->string(EmailsConstants::COL_FROM)->nullable();
                $table->string(EmailsConstants::COL_SLG)->nullable();
                $table->timestamps();
                $table->uuid(DatabaseConstants::TABLE_CREATOR);          // ! CHANGED
                $table->foreign(DatabaseConstants::TABLE_CREATOR)
                    ->references('id')
                    ->on(DatabaseConstants::TABLE_USERS)
                    ->cascadeOnDelete(); // * ADDED
            });
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
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
