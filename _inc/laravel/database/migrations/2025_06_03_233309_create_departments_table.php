<?php

use App\Config\Constants\{CompaniesConstants as CPC, DatabaseConstants as DC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDepartmentsTable extends Migration
{
    private const TABLE = DC::TABLE_DEPARTMENTS;
    private const COL_BRANCH = 'branch_id';
    private const UNQ_BDEP = 'unique_branch_department';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(self::COL_BRANCH)->index();
            $table->string(CPC::COL_DEP_NM)->index();
            $table->text('description')->nullable();
            $table->string('phone', 32)->nullable()->index();
            $table->string('email')->nullable();
            $table->uuid(CPC::COL_MNG)->nullable();
            $table->uuid(DC::TABLE_CREATOR)->default(DC::DEFAULT_UUID)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->default(DC::DEFAULT_UUID)->nullable();
            $table->timestamps();
            $table->decimal('budget', 10, 2)->default(0.00);
            $table->decimal('expenses', 10, 2)->default(0.00);
            $table->decimal('profit', 10, 2)->default(0.00);
            $table->unique([self::COL_BRANCH, CPC::COL_DEP_NM], self::UNQ_BDEP);
            $table->foreign(self::COL_BRANCH)
                ->references('id')
                ->on(DC::TABLE_BRANCHES)
                ->cascadeOnDelete();
            foreach (
                [
                    DC::TABLE_CREATOR  => DC::TABLE_USERS,
                    DC::TABLE_UPDATER  => DC::TABLE_USERS,
                    CPC::COL_MNG       => DC::TABLE_USERS,
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
                $table->dropUnique(self::UNQ_BDEP);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop unique constraint unique_branch_department: '
                        . $e->getMessage()
                );
            }
            foreach (
                [
                    self::COL_BRANCH,
                    DC::TABLE_CREATOR,
                    DC::TABLE_UPDATER,
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
