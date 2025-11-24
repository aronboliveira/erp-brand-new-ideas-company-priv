<?php

use App\Config\Constants\{
    BillsConstants as BC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{
    Migrations\Migration,
    Schema\Blueprint
};
use Illuminate\Support\Facades\{
    Log,
    Schema
};

class CreateCustomersTable extends Migration
{
    use HasNullableAuditColumns;

    private const TABLE = DC::TABLE_CUSTOMERS;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(BC::COL_CST_ID)->nullable()->index();
            $table->string('name')->index()->nullable();
            $table->string('email')->nullable();
            $table->string(BC::COL_TX_N)->nullable();
            $table->json(BC::COL_OT_TX_ID)->nullable();
            $table->string('contact')->nullable();
            $table->string('avatar', 100)->default('');
            $table->integer(BC::COL_OD_C)->default(0)->nullable();
            $table->boolean(BC::COL_IS_PRM)->default(false)->index()->nullable();
            $table->boolean(UC::COL_IA)->index()->default(true);
            $table->float(UC::COL_AVG_RT, 2, 2)->default(5.00)->index()->nullable(); // ? Nullable para testes; idealmente decimal(3,2) com range 0–5
            $table->json('preferences')->nullable();
            $table->timestamp(UC::COL_EM_V_AT)->nullable();

            $table->string(BC::COL_SHIP_NAME)->nullable();
            $table->string(BC::COL_SHIP_CTR)->nullable();
            $table->string(BC::COL_SHIP_ZIP)->nullable();
            $table->text(BC::COL_SHIP_ADR)->nullable();
            $table->string(BC::COL_SHIP_ST)->nullable();
            $table->string(BC::COL_SHIP_CTY)->nullable();
            $table->string(BC::COL_SHIP_TEL)->nullable();

            $table->string(BC::COL_BL_NAME)->nullable();
            $table->string(BC::COL_BL_EMAIL)->nullable();
            $table->string(BC::COL_BL_TEL)->nullable();
            $table->string(BC::COL_BL_ZIP)->nullable();
            $table->text(BC::COL_BL_ADR)->nullable();
            $table->string(BC::COL_BL_ST)->nullable();
            $table->string(BC::COL_BL_CTY)->nullable();
            $table->string(BC::COL_BL_CTR)->nullable();

            $table->string('lang')->default(DC::DEFAULT_LANG);
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->rememberToken();

            $this->addAuditColumns($table);

            $table->foreign(BC::COL_CST_ID)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                if (Schema::hasColumn(self::TABLE, BC::COL_CST_ID))
                    $table->dropForeign([BC::COL_CST_ID]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for ' .
                        BC::COL_CST_ID .
                        ' on table ' .
                        self::TABLE .
                        ': ' .
                        $e->getMessage()
                );
            }

            $this->dropAuditColumnForeigns($table, self::TABLE);
        });

        Schema::dropIfExists(self::TABLE);
    }
}
