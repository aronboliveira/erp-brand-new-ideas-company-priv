<?php

use App\Config\Constants\{BanksConstants, DatabaseConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBankAccountsTable extends Migration
{
    private const TABLE                = DatabaseConstants::TABLE_BANK_ACC;
    private const COL_ACCOUNT_NUMBER   = BanksConstants::COL_ACC_N;
    private const COL_BANK_ADDRESS     = BanksConstants::COL_ADR;
    private const COL_BANK_NAME        = BanksConstants::COL_NM;
    private const COL_CHART_ACCOUNT_ID = BanksConstants::COL_COA;
    private const COL_CONTACT_NUMBER   = BanksConstants::COL_CT;
    private const COL_HOLDER_NAME      = BanksConstants::COL_HNM;
    private const COL_OPENING_BALANCE  = BanksConstants::COL_OB;
    private const COL_CREATED_BY       = DatabaseConstants::TABLE_CREATOR;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                        // ! CHANGED
            $table->string(self::COL_HOLDER_NAME);
            $table->string(self::COL_BANK_NAME);
            $table->string(self::COL_ACCOUNT_NUMBER);
            $table->uuid(self::COL_CHART_ACCOUNT_ID);             // ! CHANGED
            $table->decimal(self::COL_OPENING_BALANCE, 15, 2)       // ! CHANGED
                ->default(0);
            $table->string(self::COL_CONTACT_NUMBER);
            $table->text(self::COL_BANK_ADDRESS);
            $table->uuid(self::COL_CREATED_BY);                   // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_CHART_ACCOUNT_ID => DatabaseConstants::TABLE_COAS,
                self::COL_CREATED_BY => DatabaseConstants::TABLE_USERS
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
                self::COL_CHART_ACCOUNT_ID,
                self::COL_CREATED_BY,
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
