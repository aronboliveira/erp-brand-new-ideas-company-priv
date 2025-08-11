<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateOrdersTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_ORDERS;
    private const C = 'card';
    private const E = '_exp';
    private const PL = 'plan';
    private const PR = 'price';
    private const PY = 'payment';
    private const COL_PLAN = self::PL . '_id';
    private const COL_USER = 'user_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();               // ! CHANGED
            $table->uuid('order_id')->unique(); // ! CHANGED
            $table->string('name', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string(self::C . '_number', 10)->nullable();
            $table->string(self::C . self::E . '_month', 10)->nullable();
            $table->string(self::C . self::E . '_year', 10)->nullable();
            $table->string(self::PL . '_name', 100)->nullable();
            $table->uuid(self::COL_PLAN);    // ! CHANGED
            $table->decimal(self::PR, 15, 2)->default(0.0);
            $table->string(self::PR . '_currency', 10)->nullable();
            $table->uuid('txn_id')->nullable(); // ! CHANGED
            $table->string(self::PY . '_status', 100)->nullable();
            $table->string(self::PY . '_type')->default('Manually');
            $table->string('receipt')->nullable();
            $table->uuid(self::COL_USER)->default(
                DatabaseConstants::DEFAULT_UUID
            ); // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_PLAN             => DatabaseConstants::TABLE_PLANS,
                self::COL_USER                    => DatabaseConstants::TABLE_USERS,
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
                self::COL_PLAN,
                self::COL_USER,
            ] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
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
