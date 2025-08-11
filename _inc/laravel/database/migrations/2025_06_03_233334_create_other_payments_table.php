<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateOtherPaymentsTable extends Migration
{
    private const TABLE            = 'other_payments';
    private const COL_CREATED_BY   = 'created_by';
    private const COL_EMPLOYEE_ID  = 'employee_id';
    private const COL_TYPE         = 'type';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE_ID);          // ! CHANGED
            $table->string('title');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string(self::COL_TYPE)->nullable();   // * ADDED
            $table->uuid(self::COL_CREATED_BY);           // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_EMPLOYEE_ID  => DatabaseConstants::TABLE_EMPLOYEES,
                self::COL_CREATED_BY  => DatabaseConstants::TABLE_USERS,
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
