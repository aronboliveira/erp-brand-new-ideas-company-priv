<?php

use App\Config\Constants\{DatabaseConstants as DC, PlansConstants as PLC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePlansTable extends Migration
{
    private const TABLE = DC::TABLE_PLANS;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                      // ! CHANGED
                $table->uuid('query_key')->unique();              // ! CHANGED
                $table->string(PLC::COL_NM, 100)->unique();
                $table->string(PLC::COL_DUR, 100)->default('month');
                $table->decimal(PLC::COL_PC, 15, 2)->nullable()->default(0.0);
                $table->integer(PLC::COL_MAX_U)->default(0);
                $table->integer(PLC::COL_MAX_CR)->default(0);
                $table->integer(PLC::COL_MAX_V)->default(0);
                $table->integer(PLC::COL_MAX_CL)->default(0);
                $table->float(PLC::COL_SL)->default(0.00);
                $table->integer(PLC::COL_GPT)->default(0);
                $table->integer(PLC::COL_CRM)->default(0);
                $table->integer(PLC::COL_HRM)->default(0);
                $table->integer(PLC::COL_ACC)->default(0);
                $table->integer(PLC::COL_PJ)->default(0);
                $table->integer(PLC::COL_POS)->default(0);
                $table->text(PLC::COL_DESC)->nullable();
                $table->string(PLC::COL_IMG)->nullable();
                $table->timestamps();
                $table->uuid(DC::TABLE_CREATOR)->default(DC::DEFAULT_UUID);
                $table->foreign(DC::TABLE_CREATOR)
                    ->references('id')
                    ->on(DC::TABLE_USERS)
                    ->cascadeOnDelete();
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, DC::TABLE_CREATOR) &&
                    $table->dropForeign([DC::TABLE_CREATOR]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DC::TABLE_CREATOR
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
