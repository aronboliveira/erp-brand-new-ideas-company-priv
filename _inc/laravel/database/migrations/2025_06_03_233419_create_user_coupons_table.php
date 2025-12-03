<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateUserCouponsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE     = DC::TABLE_USR_CPNS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('coupon')->index();
            $table->uuid('user');
            $table->string('order')->nullable(); // * it's not clear why this was stored as a string, but keeping it for backward compatibility, and should be checked with NormalizesAddresses trait for self::looksLikeUuid and as a valid Order instance id; if failed, nullify it
            $table->unique(['user', 'coupon', 'order'], 'uq_user_coupon_order');
            foreach (
                [
                    'user'   => DC::TABLE_USERS,
                    'coupon' => DC::TABLE_COUPONS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->cascadeOnDelete();
            $table->foreign('order')
                ->references('id')
                ->on(DC::TABLE_ORDERS)
                ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    'user',
                    'coupon',
                    'order',
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
