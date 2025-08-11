<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBillsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_BILLS;
    private const COL_VENDOR = 'vendor_id';
    private const COL_CAT = 'category_id';
    private const DATE = 'date';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();               // ! CHANGED
            $table->uuid('bill_id')
                ->default(DatabaseConstants::DEFAULT_UUID); // ! CHANGED
            $table->uuid(self::COL_VENDOR);                   // ! CHANGED
            $table->string('currency', 10)->nullable();  // ! CHANGED
            $table->date('bill_' . self::DATE);
            $table->date('due_' . self::DATE);
            $table->date('send_' . self::DATE)->nullable();
            $table->integer('order_number')->default(0);
            $table->integer('status')->default(0);
            $table->string('type')->nullable();
            $table->string('user_type')->nullable();
            $table->integer('shipping_display')->default(1);
            $table->integer('discount_apply')->default(0);
            $table->uuid(self::COL_CAT);                 // ! CHANGED
            $table->uuid(DatabaseConstants::TABLE_CREATOR);                  // ! CHANGED
            $table->timestamps();
            $table->foreign(self::COL_VENDOR)
                ->references('id')->on(DatabaseConstants::TABLE_VENDORS)
                ->cascadeOnDelete(); // * ADDED
            $table->foreign(self::COL_CAT)
                ->references('id')->on(DatabaseConstants::TABLE_PROD_SERV_CATS)
                ->cascadeOnDelete(); // * ADDED
            $table->foreign(DatabaseConstants::TABLE_CREATOR)
                ->references('id')->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_VENDOR,
                self::COL_CAT,
                DatabaseConstants::TABLE_CREATOR,
            ] as $col) {
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
