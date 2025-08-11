<?php

use App\Config\Constants\{DatabaseConstants, PlansConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePlansTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_PLANS;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                      // ! CHANGED
                $table->string(PlansConstants::COL_NM, 100)->unique();
                $table->string(PlansConstants::COL_DUR, 100)->default('month');
                $table->decimal(PlansConstants::COL_PC, 15, 2)->nullable()->default(0.0);
                $table->integer(PlansConstants::COL_MAX_U)->default(0);
                $table->integer(PlansConstants::COL_MAX_CR)->default(0);
                $table->integer(PlansConstants::COL_MAX_V)->default(0);
                $table->integer(PlansConstants::COL_MAX_CL)->default(0);
                $table->float(PlansConstants::COL_SL)->default(0.00);
                $table->integer(PlansConstants::COL_GPT)->default(0);
                $table->integer(PlansConstants::COL_CRM)->default(0);
                $table->integer(PlansConstants::COL_HRM)->default(0);
                $table->integer(PlansConstants::COL_ACC)->default(0);
                $table->integer(PlansConstants::COL_PJ)->default(0);
                $table->integer(PlansConstants::COL_POS)->default(0);
                $table->text(PlansConstants::COL_DESC)->nullable();
                $table->string(PlansConstants::COL_IMG)->nullable();
                $table->timestamps();
                $table->uuid(DatabaseConstants::TABLE_CREATOR)->default(DatabaseConstants::DEFAULT_UUID);
                $table->foreign(DatabaseConstants::TABLE_CREATOR)
                    ->references('id')
                    ->on(DatabaseConstants::TABLE_USERS)
                    ->cascadeOnDelete();
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
