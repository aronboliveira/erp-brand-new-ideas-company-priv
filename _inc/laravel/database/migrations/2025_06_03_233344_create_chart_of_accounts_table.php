<?php

use App\Config\Constants\{
    ChartsConstants,
    DatabaseConstants,
    UsersConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateChartOfAccountsTable extends Migration
{
    private const TABLE         = DatabaseConstants::TABLE_COAS;
    private const COL_CREATED_BY = DatabaseConstants::TABLE_CREATOR;
    private const COL_SUB_TYPE  = ChartsConstants::COL_SUBTP;
    private const COL_TYPE      = ChartsConstants::COL_TP;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                  // ! CHANGED
            $table->string(ChartsConstants::COL_NM);
            $table->integer(ChartsConstants::COL_CD)->default(0);
            $table->uuid(self::COL_TYPE);                   // ! CHANGED
            $table->uuid(self::COL_SUB_TYPE);               // ! CHANGED
            $table->integer(ChartsConstants::COL_ENB)->default(1);
            $table->text(Chartsconstants::COL_DESC)->nullable();
            $table->uuid(self::COL_CREATED_BY);             // ! CHANGED
            $table->uuid(UsersConstants::COL_USER_ID)->nullable();
            $table->timestamps();
            foreach ([
                self::COL_CREATED_BY => DatabaseConstants::TABLE_USERS,
                self::COL_SUB_TYPE => DatabaseConstants::TABLE_COA_SUBTYPES,
                self::COL_TYPE => DatabaseConstants::TABLE_COA_TYPES
            ] as $col => $tbl)
                $table->foreign($col)
                    ->references('id')->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_CREATED_BY,
                self::COL_SUB_TYPE,
                self::COL_TYPE,
            ] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
