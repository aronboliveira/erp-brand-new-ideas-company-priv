<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateWarehousesTable extends Migration
{
    private const TABLE         = 'warehouses';
    private const COL_NAME      = 'name';
    private const COL_ADDRESS   = 'address';
    private const COL_CITY      = 'city';
    private const COL_CITY_ZIP  = self::COL_CITY . '_zip';
    private const COL_CREATED_BY = DatabaseConstants::TABLE_CREATOR;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                 // ! CHANGED
            $table->string(self::COL_NAME);
            $table->text(self::COL_ADDRESS);
            $table->string(self::COL_CITY);
            $table->string(self::COL_CITY_ZIP);
            $table->uuid(self::COL_CREATED_BY);            // ! CHANGED
            $table->timestamps();
            $table->foreign(self::COL_CREATED_BY)
                ->references('id')->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, self::COL_CREATED_BY)
                    && $table->dropForeign([self::COL_CREATED_BY]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to execute down for '
                        . self::COL_CREATED_BY
                        . ' foreign key column: '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
