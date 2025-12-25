<?php

use App\Config\Constants\{CompaniesConstants as CPC, DatabaseConstants as DC};
use App\Traits\{BranchConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDepartmentsTable extends Migration
{
    use BranchConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_DEPARTMENTS;
    private const UNQ_BDEP = 'unique_branch_department';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company')->nullable()->index(); // ? nullable for tests
            $table->string('name')->index();
            $this->addBranchColumns($table, unique: false, nullable: false);
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->string('phone', 32)->nullable()->index();
            $table->string('email')->nullable();
            $table->uuid(CPC::COL_MNG)->nullable();
            $table->decimal('budget', 15, 2)->default(0.00);
            $table->unsignedDecimal('expenses', 15, 2)->default(0.00);
            $table->unsignedDecimal('profit', 15, 2)->default(0.00);
            $table->unique([CPC::COL_BRC_ID, CPC::COL_DEP_NM], self::UNQ_BDEP);
            foreach (
                [
                    CPC::COL_MNG       => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                $table->dropUnique(self::UNQ_BDEP);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop unique constraint unique_branch_department: '
                        . $e->getMessage()
                );
            }
            $this->dropBranchColumnForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    CPC::COL_MNG,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ' on table '
                            . self::TABLE
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
