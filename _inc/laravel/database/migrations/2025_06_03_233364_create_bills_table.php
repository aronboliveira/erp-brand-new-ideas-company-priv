<?php

use App\Config\Constants\{DatabaseConstants as DC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBillsTable extends Migration
{
    private const TABLE = DC::TABLE_BILLS;
    private const COL_VENDOR = 'vendor_id';
    private const COL_CAT = 'category_id';
    private const DATE = 'date';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bill_id')->default(DC::DEFAULT_UUID);
            $table->uuid('vendor_id');
            $table->uuid('category_id');
            $table->string('currency', 10)->nullable();
            $table->date('bill_' . self::DATE);
            $table->date('due_' . self::DATE);
            $table->date('send_' . self::DATE)->nullable();
            $table->integer('order_number')->default(0);
            $table->integer('status')->default(0);
            $table->string('type')->nullable();
            $table->string('user_type')->nullable();
            $table->integer('shipping_display')->default(1);
            $table->integer('discount_apply')->default(0);
            $table->uuid(DC::TABLE_CREATOR);
            $table->timestamps();
            $table->foreign('vendor_id')
                ->references('id')->on(DC::TABLE_VENDORS)
                ->cascadeOnDelete(); // * ADDED
            $table->foreign(self::COL_CAT)
                ->references('id')->on(DC::TABLE_PROD_SERV_CATS)
                ->cascadeOnDelete(); // * ADDED
            $table->foreign(DC::TABLE_CREATOR)
                ->references('id')->on(DC::TABLE_USERS)
                ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    'vendor_id',
                    self::COL_CAT,
                    DC::TABLE_CREATOR,
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
