<?php

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    EmailsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEmailsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_EMAILS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();                // ! CHANGED
            $table->string(ActivitiesConstants::COL_TT);
            $table->text(ActivitiesConstants::COL_DESC);
            // $table->text(DatabaseConstants::TABLE_NOTES);
            $table->string(EmailsConstants::COL_D_URL, 200)->nullable();
            $table->text(EmailsConstants::COL_ATC)->nullable(); // TODO IMPLEMENTAR COMO CLASSE PRÓPRIA
            $table->string(EmailsConstants::COL_EM, 254)->unique();
            $table->string(ActivitiesConstants::COL_MT, 100);
            $table->uuid(ActivitiesConstants::COL_MI)->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR);             // ! CHANGED
            $table->foreign(DatabaseConstants::COL_TABLE_CREATOR)
                ->references('id')
                ->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, DatabaseConstants::COL_TABLE_CREATOR) &&
                    $table->dropForeign([DatabaseConstants::COL_TABLE_CREATOR]);
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
