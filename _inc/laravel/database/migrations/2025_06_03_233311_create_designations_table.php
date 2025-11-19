<?php

use App\Config\Constants\{
    CompaniesConstants as CPC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

class CreateDesignationsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_DESIGNS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(UC::COL_DSG_NM)->index();
            $table->uuid(CPC::COL_DEP_ID);
            $table->decimal(CPC::COL_EBDG, 15, 2)->default(0.00)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->date(CPC::COL_VFROM)->nullable()->default(DB::raw('(CURDATE())'));
            $table->date(CPC::COL_VTO)->nullable()->default(DB::raw('(DATE_ADD(CURDATE(), INTERVAL 10 YEAR))'));
            $this->addAuditColumns($table);
            $table->foreign(CPC::COL_DEP_ID)
                ->references('id')
                ->on(DC::TABLE_DEPARTMENTS)
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    CPC::COL_DEP_ID,
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
