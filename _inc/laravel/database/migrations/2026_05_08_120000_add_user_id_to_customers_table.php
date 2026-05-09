<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;

/**
 * Customers represent a *kind of person* the business engages with.
 * The authoritative authentication entity is `users` — a "customer who
 * logs in" is a `users` row with `type = customer` (see App\Enums\UserType).
 *
 * This migration adds an optional one-to-one bridge `customers.user_id →
 * users.id` so that a customer record may be reconciled with its login
 * account when one exists. `null` means the customer is record-only and
 * does not authenticate (most B2C "ledger" customers).
 *
 * Without this column, code that branches on `$customer->type` is
 * meaningless (the column doesn't exist on customers); auth-required
 * paths must be reached via the linked User.
 */
class AddUserIdToCustomersTable extends Migration
{
    private const TABLE = DC::TABLE_CUSTOMERS;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) return;
        if (Schema::hasColumn(self::TABLE, UC::COL_USER_ID)) return;

        Schema::table(self::TABLE, function (Blueprint $table): void {
            $table->uuid(UC::COL_USER_ID)->nullable()->after('id');
            $table->index(UC::COL_USER_ID);
            $table->foreign(UC::COL_USER_ID)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable(self::TABLE)) return;
        if (!Schema::hasColumn(self::TABLE, UC::COL_USER_ID)) return;

        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                $table->dropForeign([UC::COL_USER_ID]);
            } catch (\Throwable $e) {
                // index/FK already gone — ignore
            }
            $table->dropColumn(UC::COL_USER_ID);
        });
    }
}
