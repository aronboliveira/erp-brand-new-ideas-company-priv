<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateInvoicesTable extends Migration
{
    private const TABLE              =  DatabaseConstants::TABLE_INVS;
    private const COL_INVOICE_ID     = 'invoice_id';
    private const COL_CUSTOMER_ID    = 'customer_id';
    private const COL_ISSUE_DATE     = 'issue_date';
    private const COL_DUE_DATE       = 'due_date';
    private const COL_SEND_DATE      = 'send_date';
    private const COL_REF_NUMBER     = 'ref_number';
    private const COL_STATUS         = 'status';
    private const COL_SHIPPING_DISPLAY = 'shipping_display';
    private const COL_DISCOUNT_APPLY = 'discount_apply';
    private const COL_CATEGORY_ID    = 'category_id';
    private const COL_TAX_ID         = 'tax_id';
    private const COL_CREATED_BY     = DatabaseConstants::TABLE_CREATOR;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                                             // ! CHANGED
            $table->uuid(self::COL_INVOICE_ID);         // ! CHANGED
            $table->uuid(self::COL_CUSTOMER_ID);                                       // ! CHANGED
            $table->date(self::COL_ISSUE_DATE);
            $table->date(self::COL_DUE_DATE);
            $table->date(self::COL_SEND_DATE)->nullable();
            $table->text(self::COL_REF_NUMBER)->nullable();
            $table->integer(self::COL_STATUS)->default(0);
            $table->integer(self::COL_SHIPPING_DISPLAY)->default(1);
            $table->integer(self::COL_DISCOUNT_APPLY)->default(0);
            $table->uuid(self::COL_CATEGORY_ID);                                       // ! CHANGED
            $table->uuid(self::COL_TAX_ID)->nullable();                                // * ADDED
            $table->uuid(self::COL_CREATED_BY);                                        // ! CHANGED
            $table->timestamps();
            foreach (
                [
                    self::COL_CUSTOMER_ID => DatabaseConstants::TABLE_CUSTOMERS,
                    self::COL_CATEGORY_ID => DatabaseConstants::TABLE_PROD_SERV_CATS,
                    self::COL_TAX_ID => DatabaseConstants::TABLE_TAXES,
                    self::COL_CREATED_BY      =>  DatabaseConstants::TABLE_USERS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_CUSTOMER_ID,
                    self::COL_CATEGORY_ID,
                    self::COL_TAX_ID,
                    self::COL_CREATED_BY,
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
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
