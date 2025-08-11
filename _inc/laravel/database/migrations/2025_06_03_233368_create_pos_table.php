<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePosTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_POS;
    private const COL_CUSTOMER = 'customer_id';
    private const COL_WH = 'warehouse_id';
    private const COL_CAT = 'category_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();         // ! CHANGED
            $table->uuid(self::TABLE . '_id');                // ! CHANGED
            $table->uuid(self::COL_CUSTOMER);           // ! CHANGED
            $table->uuid(self::COL_WH);          // ! CHANGED
            $table->date(self::TABLE . '_date')->nullable();
            $table->uuid(self::COL_CAT);           // ! CHANGED
            $table->integer('status')->default(0);
            $table->integer('shipping_display')->default(1);
            $table->uuid(DatabaseConstants::TABLE_CREATOR);            // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_CUSTOMER                 => DatabaseConstants::TABLE_CUSTOMERS,
                self::COL_WH                => DatabaseConstants::TABLE_WHS,
                self::COL_CAT                 => DatabaseConstants::TABLE_PROD_SERV_CATS,
                DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
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
                self::COL_CUSTOMER,
                self::COL_WH,
                self::COL_CAT,
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
