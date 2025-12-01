<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePromotionsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PRMT;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $this->addEmployeeColumns($table);
                $table->uuid(UC::COL_DSG_ID)->index();
                $table->date(UC::COL_PRMT_DT)->default(now()->addDays(7)->format('Y-m-d'));
                $table->string(UC::COL_PRMT_TL)->nullable(); // ? this does not correspond to the real role, but rather a facade/alias
                $table->string('description')->nullable();
                $table->foreign(UC::COL_DSG_ID)
                    ->references('id')
                    ->on(DC::TABLE_DESIGNS)
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
                    UC::COL_DSG_ID,
                ] as $col
            ) {
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
