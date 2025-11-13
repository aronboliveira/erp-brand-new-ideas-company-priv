<?php

use App\Config\Constants\{
    CompaniesConstants as CPC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

class CreateDesignationsTable extends Migration
{
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
            $table->date(CPC::COL_VFROM)->nullable()->default(DB::raw('CURDATE()'));
            $table->date(CPC::COL_VTO)->nullable()->default(DB::raw('DATE_ADD(CURDATE(), INTERVAL 10 YEAR)'));
            $table->timestamps();
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            $table->foreign(CPC::COL_DEP_ID)
                ->references('id')
                ->on(DC::TABLE_DEPARTMENTS)
                ->cascadeOnDelete();
            foreach (
                [
                    DC::TABLE_UPDATER    => DC::TABLE_USERS,
                    DC::TABLE_CREATOR    => DC::TABLE_USERS,
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
            foreach (
                [
                    CPC::COL_DEP_ID,
                    DC::TABLE_UPDATER,
                    DC::TABLE_CREATOR,
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
