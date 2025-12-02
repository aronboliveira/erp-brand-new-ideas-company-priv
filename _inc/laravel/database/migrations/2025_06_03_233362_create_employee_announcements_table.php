<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\RecruitmentRole;
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEmployeeAnnouncementsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_EANC;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(AC::COL_ANC_ID)->index();
            $this->addEmployeeColumns($table, nullable: false, cascade: false);
            $table->enum('role', RecruitmentRole::values())->default(RecruitmentRole::Other)->nullable(); // ? nullable for testing purposes
            $table->json(AC::COL_PRT)->nullable(); // ? nullable for testing purposes
            $table->json('participation')->nullable(); // ? nullable for testing purposes
            $table->text('notes')->nullable();
            $table->foreign(AC::COL_ANC_ID)
                ->references('id')
                ->on(DC::TABLE_ANC)
                ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropEmployeeColumnForeign($table, self::TABLE);
            foreach (
                [
                    AC::COL_ANC_ID,
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
