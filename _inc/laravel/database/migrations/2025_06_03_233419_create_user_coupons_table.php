<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateUserCouponsTable extends Migration
{
    private const TABLE     = 'user_' . DatabaseConstants::TABLE_COUPONS;
    private const COL_COUPON = 'coupon';
    private const COL_ORDER = 'order';
    private const COL_USER  = 'user';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                       // ! CHANGED
            $table->uuid(self::COL_USER);                        // ! CHANGED
            $table->uuid(self::COL_COUPON);                      // ! CHANGED
            $table->string(self::COL_ORDER)->nullable();         // * added to match model
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            foreach ([
                self::COL_USER   => DatabaseConstants::TABLE_USERS,
                self::COL_COUPON => DatabaseConstants::TABLE_COUPONS,
                DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
            ] as $col => $tbl)
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_USER,
                self::COL_COUPON,
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
