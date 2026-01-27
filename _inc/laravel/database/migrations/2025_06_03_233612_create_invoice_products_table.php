<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, SettingsConstants as SC};
use App\Traits\{HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateInvoiceProductsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_INV_PRD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(BC::COL_INV_ID)->index();
            $table->uuid(BC::COL_PRD_ID)->unique();
            $table->unsignedInteger('quantity')->min(1)->default(1);
            $table->string('tax', 50)->nullable(); // * this will kept for legacy
            $table->decimal('price', 16, 2)->default(0.00);
            $table->string(BC::COL_CUR_ID, 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable(); // ? nullable for testing purposes
            $table->float('discount')->default(0.00)->nullable(); // ? nullable for testing purposes
            $table->unsignedDecimal(BC::COL_SVC_FEE, 16, 2)->default(0.00)->nullable(); // ? nullable for testing purposes
            $table->boolean(BC::COL_IS_SCD)->default(false)->nullable(); // ? nullable for testing purposes
            $table->boolean(BC::COL_CAN_CHG_BK)->default(false)->nullable(); // ? nullable for testing purposes
            $table->string('reference')->nullable();
            $table->text('description');
            $table->text('notes')->nullable();
            $table->json(BC::COL_TXS_LST)->nullable(); // ? nullable for testing purposes
            $table->json('attachments')->nullable();
            $table->uuid('contract')->nullable()->index();
            $table->uuid('loan')->nullable()->index();
            $table->uuid(BC::COL_WRH_ID)->nullable();
            $table->foreign(BC::COL_INV_ID)
                ->references('id')
                ->on(DC::TABLE_INVS)
                ->restrictOnDelete();
            foreach (
                [
                    'contract'          => DC::TABLE_CONTRACTS,
                    'loan'              => DC::TABLE_LN,
                    BC::COL_WRH_ID      => DC::TABLE_WRH,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
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
                    BC::COL_INV_ID,
                    'contract',
                    'loan',
                    BC::COL_WRH_ID,
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
