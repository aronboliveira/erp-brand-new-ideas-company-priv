<?php

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC, UsersConstants as UC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTerminationsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_TERMINATIONS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $this->addEmployeeColumns($table, unique: true);
                $table->date(UC::COL_TERMINATION_NDT)->default(now()->format('Y-m-d'));
                $table->date(UC::COL_TERMINATION_DT)->default(now()->addDays(30)->format('Y-m-d'));
                $table->uuid(UC::COL_TERMINATION_TP)->nullable();
                $table->uuid(FC::COL_FM_ID)->nullable()->index(); // ? related exit interview form
                $table->string('description')->nullable();
                foreach (
                    [
                        UC::COL_TERMINATION_TP => DC::TABLE_TERMINATION_TYPES,
                        FC::COL_FM_ID => DC::TABLE_FORM_BUILD,
                    ] as $col => $tableName
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tableName)
                        ->nullOnDelete();
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    UC::COL_TERMINATION_TP,
                ] as $col
            ) {
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
