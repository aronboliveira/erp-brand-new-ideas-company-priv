<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePurchasesTable extends Migration
{
    private const ENTITY = 'purchase';
    private const TABLE = DatabaseConstants::TABLE_PURCHASES;
    private const D = 'date';
    private const COL_PARENT   = 'purchase_id';
    private const COL_VENDOR   = 'vendor_id';
    private const COL_WAREHOUSE = 'warehouse_id';
    private const COL_CATEGORY = 'category_id';
    private const COL_TAX      = 'tax_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();              // ! CHANGED
            $table->uuid(self::COL_PARENT)
                ->default(DatabaseConstants::DEFAULT_UUID)
                ->index();                                   // FK → purchases (self-reference)
            $table->uuid(self::COL_VENDOR)->index();           // FK → vendors
            $table->uuid(self::COL_WAREHOUSE)->index();        // FK → warehouses
            $table->uuid(self::COL_CATEGORY)->index();         // FK → categories
            $table->uuid(self::COL_TAX)->nullable()->index();  // FK → taxes
            $table->date(self::ENTITY . '_' . self::D);
            $table->integer(self::ENTITY . '_number')->default(0);
            $table->integer('discount_apply')->default(0);
            $table->integer('status')->default(0);
            $table->integer('shipping_display')->default(1);
            $table->date('send_' . self::D)->nullable();      // ! CHANGED
            $table->timestamps();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR);                 // ! CHANGED
            foreach (
                [
                    self::COL_PARENT                 => self::TABLE,                         // self-reference
                    self::COL_VENDOR                 => DatabaseConstants::TABLE_VENDORS,
                    self::COL_WAREHOUSE              => DatabaseConstants::TABLE_WHS,
                    self::COL_CATEGORY               => DatabaseConstants::TABLE_PROD_SERV_CATS,
                    self::COL_TAX                    => DatabaseConstants::TABLE_TAXES,
                    DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_PARENT,
                    self::COL_VENDOR,
                    self::COL_WAREHOUSE,
                    self::COL_CATEGORY,
                    self::COL_TAX,
                    DatabaseConstants::COL_TABLE_CREATOR,
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
