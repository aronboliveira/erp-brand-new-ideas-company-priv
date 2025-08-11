<?php

use App\Config\Constants\{
    CompaniesConstants,
    DatabaseConstants,
    UsersConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDesignationsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_DESIGNS;
    private const COL_DEPARTMENT = CompaniesConstants::COL_DEP_ID;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                               // ! CHANGED
            $table->uuid(self::COL_DEPARTMENT)->index();                       // ! CHANGED
            $table->string(UsersConstants::COL_DSG_NM);
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->index();                          // ! CHANGED
            foreach ([
                self::COL_DEPARTMENT                     => DatabaseConstants::TABLE_DEPARTMENTS,
                DatabaseConstants::TABLE_CREATOR    => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_DEPARTMENT,
                DatabaseConstants::TABLE_CREATOR,
            ] as $column) {
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
