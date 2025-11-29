<?php

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\{BranchConnected, EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateMeetingsTable extends Migration
{
    use BranchConnected, EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = 'meetings';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('code')->unique()->index()->nullable(); // ? nullable for testing purposes
            $this->addEmployeeColumns($table, false, true);
            $this->addBranchColumns($table, false, true);
            $table->uuid(CC::COL_DEP_ID)->index()->nullable();
            $table->string('title')->index();
            $table->date('date');
            $table->time('time')->index(); // * maybe datetime later
            $table->unsignedTinyInteger(PJC::COL_MIN_DR)->default(15)->nullable(); // * duration in minutes
            $table->unsignedTinyInteger(PJC::COL_EXP_DR)->default(30)->nullable(); // * duration in minutes
            $table->unsignedTinyInteger(PJC::COL_MAX_DR)->default(60)->nullable(); // * duration in minutes
            $table->string('url', 256)->nullable();
            $table->text('note')->nullable();
            $table->boolean(CC::COL_IS_INT)->default(true)->nullable();
            $table->json('attachments')->nullable();
            $table->json('invited')->nullable();
            $table->json('conditions')->nullable();
            $table->json('reminders')->nullable();
            $table->json('tags')->nullable();
            $table->foreign(CC::COL_DEP_ID)
                ->references('id')
                ->on(DC::TABLE_DEPARTMENTS)
                ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }
    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropBranchForeign($table, self::TABLE);
            $this->dropEmployeeForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    CC::COL_DEP_ID,
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
