<?php

use App\Config\Constants\{DatabaseConstants as DC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePurchasePayments extends Migration
{
    private const TABLE          = DC::TABLE_PRC_PAY;
    private const COL_ACCOUNT_ID = 'account_id';
    private const COL_PURCHASE_ID = 'purchase_id';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                                 // ! CHANGED
            $table->uuid(self::COL_PURCHASE_ID);                           // ! CHANGED
            $table->date('date');
            $table->decimal('amount', 15, 2)->default(0.00);               // ! CHANGED
            $table->uuid(self::COL_ACCOUNT_ID);                            // ! CHANGED
            $table->integer('payment_method');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->string('add_receipt')->nullable();                     // * added
            $table->timestamps();
            $table->uuid(DC::COL_TABLE_CREATOR)->nullable();
            foreach (
                [
                    self::COL_PURCHASE_ID               => DC::TABLE_PURCHASES,
                    self::COL_ACCOUNT_ID                => DC::TABLE_BANK_ACC,
                    DC::COL_TABLE_CREATOR    => DC::TABLE_USERS,
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
                    self::COL_PURCHASE_ID,
                    self::COL_ACCOUNT_ID,
                    DC::COL_TABLE_CREATOR,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to execute down for '
                            . $column
                            . ' foreign key column: '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
