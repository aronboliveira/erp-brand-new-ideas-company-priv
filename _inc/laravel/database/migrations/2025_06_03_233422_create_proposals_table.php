<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProposalsTable extends Migration
{
    private const TABLE                    = DatabaseConstants::TABLE_PROPOSALS;
    private const COL_PROPOSAL_ID          = 'proposal_id';
    private const COL_CUSTOMER_ID          = 'customer_id';
    private const COL_ISSUE_DATE           = 'issue_date';
    private const COL_SEND_DATE            = 'send_date';
    private const COL_CATEGORY_ID          = 'category_id';
    private const COL_STATUS               = 'status';
    private const COL_DISCOUNT_APPLY       = 'discount_apply';
    private const COL_IS_CONVERT           = 'is_convert';
    private const COL_CONVERTED_INVOICE_ID = 'converted_invoice_id';
    private const COL_CREATED_BY           = 'created_by';
    private const COL_TAX_ID               = 'tax_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                                     // ! CHANGED
            $table->unsignedBigInteger(self::COL_PROPOSAL_ID);
            $table->uuid(self::COL_CUSTOMER_ID);                               // ! CHANGED
            $table->date(self::COL_ISSUE_DATE);
            $table->date(self::COL_SEND_DATE)->nullable();                     // ! CHANGED
            $table->uuid(self::COL_CATEGORY_ID);                               // ! CHANGED
            $table->integer(self::COL_STATUS)->default(0);
            $table->integer(self::COL_DISCOUNT_APPLY)->default(0);
            $table->integer(self::COL_IS_CONVERT)->default(0);
            $table->uuid(self::COL_CONVERTED_INVOICE_ID)->nullable();          // ! CHANGED
            $table->uuid(self::COL_CREATED_BY);                                // ! CHANGED
            $table->uuid(self::COL_TAX_ID)->nullable();                        // ! CHANGED
            $table->timestamps();
            foreach (
                [
                    self::COL_CUSTOMER_ID => DatabaseConstants::TABLE_CUSTOMERS,
                    self::COL_CATEGORY_ID => DatabaseConstants::TABLE_PROD_SERV_CATS,
                    self::COL_CREATED_BY => DatabaseConstants::TABLE_USERS,
                    self::COL_TAX_ID => DatabaseConstants::TABLE_TAXES,
                    self::COL_CONVERTED_INVOICE_ID => DatabaseConstants::TABLE_INVS,
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
                    self::COL_CREATED_BY,
                    self::COL_TAX_ID,
                    self::COL_CONVERTED_INVOICE_ID,
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
