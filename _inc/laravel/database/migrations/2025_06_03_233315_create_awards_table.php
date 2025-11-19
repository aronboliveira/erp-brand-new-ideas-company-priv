<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateAwardsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_AWD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(UC::COL_EMP_ID)->index();
            $table->uuid(UC::COL_AWD_TP)->nullable()->index();
            $table->date('date');
            $table->string('gift')->nullable();
            $table->string('description')->nullable();
            $this->addAuditColumns($table);
            $table->foreign(UC::COL_EMP_ID)
                ->references('id')
                ->on(DC::TABLE_EMPLOYEES)
                ->cascadeOnDelete();
            $table->foreign(UC::COL_AWD_TP)
                ->references('id')
                ->on(DC::TABLE_AWD_TPS)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    UC::COL_EMP_ID,
                    UC::COL_AWD_TP,
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
