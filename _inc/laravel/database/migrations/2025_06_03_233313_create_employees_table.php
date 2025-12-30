<?php

use App\Config\Constants\{CompaniesConstants as CPC, DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{BranchConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEmployeesTable extends Migration
{
    use BranchConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_EMPLOYEES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(UC::COL_EMP_ID)->unique(); // ? This is a secondary identifier for employees, used for querying
            $table->uuid(UC::COL_USER_ID)->unique()->nullable(); // ? A employee may not have a user account
            $table->string('name')->nullable()->index();
            $table->boolean('manager')->default(false)->nullable()->index();
            $table->string('phone', 32)->nullable()->unique(); // ? This is checked for regex pattern when creating and updated
            $table->string('email', 254)->nullable()->unique(); // ? This is checked for regex pattern when creating and updated
            $table->string('gender')->nullable(); // ? This is check by a enum when creating and updating
            $table->string('notes')->nullable();
            $table->string('password')->nullable();
            $table->string('address', 1024)->nullable();
            $table->date('dob')->nullable();
            $this->addBranchColumns($table, unique: false, nullable: false);
            $table->string(CPC::COL_BRC_LC)->nullable(); // ? This is queried on creating and updating to be not null when there is a branch, using branch->address
            $table->uuid(CPC::COL_DEP_ID)->nullable();
            $table->uuid(UC::COL_DSG_ID)->nullable();
            $table->string(CPC::COL_DOJ)->nullable();
            $table->text('documents')->nullable();
            $table->string(UC::COL_ACC_HD)->nullable();
            $table->string(UC::COL_ACC_NM)->nullable()->unique();
            $table->string(UC::COL_BANK_NM)->nullable();
            $table->string(UC::COL_BANK_IC)->nullable();
            $table->uuid(UC::COL_TAX_ID)->nullable();
            $table->decimal('salary', 10, 2)->nullable()->default(0.00);
            $table->uuid(UC::COL_SLR_TP)->nullable();
            $table->integer(UC::COL_IA)->default(1);
            foreach (
                [
                    UC::COL_USER_ID    => DC::TABLE_USERS,
                    UC::COL_DSG_ID     => DC::TABLE_DESIGNS,
                    CPC::COL_DEP_ID    => DC::TABLE_DEPARTMENTS,
                    UC::COL_TAX_ID     => DC::TABLE_TAXES,
                    UC::COL_SLR_TP     => DC::TABLE_PAY_SLP_TP,
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
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropBranchColumnForeign($table, self::TABLE);
            foreach (
                [
                    UC::COL_USER_ID,
                    UC::COL_DSG_ID,
                    CPC::COL_DEP_ID,
                    UC::COL_TAX_ID,
                    UC::COL_SLR_TP,
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
