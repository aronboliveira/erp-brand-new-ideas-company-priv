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
                $table->uuid('id')->primary();
                $table->uuid('query_key')->unique();
                $table->string(PLC::COL_NM, 100)->unique();
                $table->string(PLC::COL_DUR, 100)->default('month');
                $table->integer(PLC::COL_MAX_U)->default(0);
                $table->integer(PLC::COL_MAX_CR)->default(0);
                $table->integer(PLC::COL_MAX_V)->default(0);
                $table->integer(PLC::COL_MAX_CL)->default(0);
                $table->decimal(PLC::COL_PC, 15, 2)->nullable()->default(0.00);
                $table->decimal(PLC::COL_SL, 10, 2)->default(0.00);
                $table->integer(PLC::COL_GPT)->default(0);
                $table->integer(PLC::COL_CRM)->default(0);
                $table->integer(PLC::COL_HRM)->default(0);
                $table->integer(PLC::COL_ACC)->default(0);
                $table->integer(PLC::COL_PJ)->default(0);
                $table->integer(PLC::COL_POS)->default(0);
                $table->text(PLC::COL_DESC)->nullable();
                $table->string(PLC::COL_IMG)->nullable();
                $table->timestamps();
                $table->uuid(DC::TABLE_CREATOR)->default(DC::DEFAULT_UUID)->nullable();
                $table->uuid(DC::TABLE_UPDATER)->nullable();
                foreach (
                    [
                        DC::TABLE_CREATOR  => DC::TABLE_USERS,
                        DC::TABLE_UPDATER  => DC::TABLE_USERS,
                    ] as $column => $referencedTable
                )
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->nullOnDelete();
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                foreach (
                    [DC::TABLE_UPDATER, DC::TABLE_CREATOR] as $col
                )
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
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
