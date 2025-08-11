<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateSaturationDeductionsTable extends Migration
{
    private const TABLE = 'saturation_deductions';
    private const COL_EMPLOYEE = 'employee_id';
    private const COL_DEDUCT = 'deduction_option';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();            // ! CHANGED
                $table->uuid(self::COL_EMPLOYEE);              // ! CHANGED
                $table->uuid(self::COL_DEDUCT);         // ! CHANGED
                $table->string('title');
                $table->decimal('amount', 15, 2)->default('0.0');
                $table->string('type')->nullable();
                $table->timestamps();
                $table->uuid(DatabaseConstants::TABLE_CREATOR);               // ! CHANGED
                foreach ([
                    self::COL_EMPLOYEE                    => DatabaseConstants::TABLE_EMPLOYEES,
                    self::COL_DEDUCT                      => DatabaseConstants::TABLE_DEDUCTION_OPTS,
                    DatabaseConstants::TABLE_CREATOR       => DatabaseConstants::TABLE_USERS,
                ] as $column => $referencedTable)
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->onDelete('cascade');
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_EMPLOYEE,
                self::COL_DEDUCT,
                DatabaseConstants::TABLE_CREATOR,
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
