<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePayslipsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;

    private const TABLE = DC::TABLE_PAY_SLP;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addEmployeeColumns($table, unique: false, nullable: false);
            $table->integer(BC::COL_NET_PAYABLE)->default(0);
            $table->string(BC::COL_SLR_M); // TODO modificar posteriormente para date
            $table->date(BC::COL_P_DAY)->nullable();
            $table->integer('status');
            $table->decimal(BC::COL_G_SLR, 10, 2)->default(DC::MININUM_WAGE_BR - 1.00);
            $table->decimal(BC::COL_N_SLR, 10, 2)->default(DC::MININUM_WAGE_BR)->nullable();
            $table->uuid('allowance')->nullable();
            $table->uuid('commission')->nullable();
            $table->uuid('loan')->nullable();
            $table->uuid(BC::COL_ST_DD)->nullable();
            $table->uuid('payment')->nullable()->unique();
            $table->uuid(BC::COL_OT_PAY)->nullable();
            $table->uuid('overtime')->nullable();
            $this->addAuditColumns($table);
            foreach (
                [
                    'allowance'    => DC::TABLE_ALW,
                    'commission'   => DC::TABLE_CMS,
                    'loan'         => DC::TABLE_LN,
                    BC::COL_ST_DD  => DC::TABLE_ST_DD,
                    BC::COL_OT_PAY => DC::TABLE_OT_PYMTS,
                    'overtime'     => DC::TABLE_OVT,
                    'payment'      => DC::TABLE_PAY,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropEmployeeColumnForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);

            foreach (
                [
                    'allowance',
                    'commission',
                    'loan',
                    BC::COL_ST_DD,
                    BC::COL_OT_PAY,
                    'overtime',
                    'payment'
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for ' .
                            $col .
                            ' on table ' .
                            self::TABLE .
                            ': ' .
                            $e->getMessage()
                    );
                }
            }
        });

        Schema::dropIfExists(self::TABLE);
    }
}
