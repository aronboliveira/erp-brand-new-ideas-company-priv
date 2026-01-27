<?php

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateAnnouncementsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_ANC;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable()->index();
            $table->date(PJC::COL_S_DT)->useCurrent();
            $table->date(PJC::COL_E_DT)->nullable();
            $table->uuid(CC::COL_BRC_ID)->index();
            $table->uuid(CC::COL_DEP_ID)->nullable()->index();
            $table->uuid(UC::COL_EMP_ID)->nullable()->index(); // ? the employee who made the announcement
            $table->uuid('recruiter')->nullable()->index(); // ? the employee who is responsible for recruiting
            $table->text('description')->nullable();
            $table->boolean(UC::COL_IA)->default(true)->nullable()->index(); // ? nullable for tests
            $table->boolean(UC::COL_IS_RD)->default(true)->nullable()->index(); // ? nullable for tests
            $table->date(PJC::COL_PLN_ST)->useCurrent()->nullable(); // ? nullable for tests, when the job is planned to start
            $table->json('requirements')->nullable();
            $table->json('tags')->nullable();
            $table->json('steps')->nullable();
            $table->foreign(CC::COL_BRC_ID)
                ->references('id')
                ->on(DC::TABLE_BRANCHES)
                ->cascadeOnDelete();
            foreach (
                [
                    CC::COL_DEP_ID     => DC::TABLE_DEPARTMENTS,
                    UC::COL_EMP_ID     => DC::TABLE_EMPLOYEES,
                    'recruiter'        => DC::TABLE_EMPLOYEES,
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
            foreach (
                [
                    'recruiter',
                    CC::COL_BRC_ID,
                    CC::COL_DEP_ID,
                    UC::COL_EMP_ID,
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
