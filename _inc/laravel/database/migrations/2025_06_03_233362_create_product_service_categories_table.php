<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProductServiceCategoriesTable extends Migration
{
    private const TABLE_NAME = DatabaseConstants::TABLE_PROD_SERV_CATS;
    private const COL_COA = 'chart_account_id';
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED: use UUID primary key
            $table->string('name');
            $table->string('type')->default('0');
            $table->uuid(self::COL_COA)->nullable(); // ! CHANGED: use UUID for chart_account_id, allow null
            $table->string('color')->default('#fc544b');
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable(); // ! CHANGED: use UUID for created_by, allow null
            foreach ([
                self::COL_COA                => DatabaseConstants::TABLE_COAS,
                DatabaseConstants::TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            foreach ([
                self::COL_COA,
                DatabaseConstants::TABLE_CREATOR,
            ] as $column) {
                try {
                    Schema::hasColumn(self::TABLE_NAME, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
