<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePayslipsTable extends Migration
{
    private const TABLE          = 'pay_slips';
    private const COL_CREATED_BY = 'created_by';
    private const COL_EMPLOYEE_ID = 'employee_id';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                      // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE_ID);                // ! CHANGED
            $table->integer('net_payble');
            $table->string('salary_month');
            $table->integer('status');
            $table->integer('basic_salary');
            $table->text('allowance');
            $table->text('commission');
            $table->text('loan');
            $table->text('saturation_deduction');
            $table->text('other_payment');
            $table->text('overtime');
            $table->uuid(self::COL_CREATED_BY);                 // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_EMPLOYEE_ID  => DatabaseConstants::TABLE_USERS,
                self::COL_CREATED_BY  => DatabaseConstants::TABLE_EMPLOYEES,
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
                self::COL_EMPLOYEE_ID,
                self::COL_CREATED_BY
            ] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
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
