<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\PurchaseStatus;
use App\Traits\{HasNullableAuditColumns, HasNfeColumns, HasProductSecurityCoverage, RegistersShipping, TracksFailures};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePurchasesTable extends Migration
{
    use HasNullableAuditColumns, HasNfeColumns, HasProductSecurityCoverage, RegistersShipping, TracksFailures;
    private const TABLE = DC::TABLE_PURCHASES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(BC::COL_PRC_ID, 80)->unique(); // ? queryable identifier, automatically generated as 'PRCH-{UUID}-{YYYYMMDD:HHMMSS}'
            $table->unsignedDecimal(BC::COL_SVC_FEE, 16, 2)->default(0.00)->nullable(); // ? service fee associated with the purchase, if any
            $table->unsignedBigInteger(BC::COL_PRC_NB)->default(0); // ? sequential number, auto-incremented for the user check/UI
            $table->string('source')->nullable(); // ? source of the purchase, e.g., a e-commerce platform, a physical store, etc.
            $table->string('notes')->nullable();
            $table->uuid(UC::COL_VD_ID)->index(); // ? polymorphic FK → row in DC::TABLE_VENDORS or row in DC::TABLE_USERS whereIn('type', [UserType::Vendor->value, UserType::Company->value])
            $table->uuid(BC::COL_CST_ID)->nullable()->index(); // * nullable for tests // ? polymorphic FK → row in DC::TABLE_CUSTOMERS or in DC::TABLE_USERS
            $table->uuid(BC::COL_CAT_ID)->nullable()->index(); // ? polymorphic FK → row in DC::TABLE_PROD_SERV_CATS or in DC::TABLE_PRD_CAT
            $table->uuid(BC::COL_PRD_SV_ID)->nullable()->index(); // ? polymorphic FK → row in DC::TABLE_PROD_SERVS or in DC::TABLE_PRODUCTS. If the link is sucessfuly set, overrides BC::COL_CAT_ID in this table with the BC::COL_CAT_ID (if existing and not empty) from the linked row in DC::TABLE_PROD_SERVS or DC::TABLE_PRODUCTS
            $table->uuid(BC::COL_WRH_ID)->nullable()->index(); // ? FK → warehouses
            $table->uuid(BC::COL_OD_ID)->nullable()->index(); // ? FK → order request
            $table->uuid(BC::COL_BL_ID)->nullable()->index(); // ? FK → bill
            $table->uuid(BC::COL_INV_ID)->nullable()->index(); // ? FK → invoice // ? cannot coexist with BC::COL_BL_ID and has priority, nullifying the first if both are set
            $table->uuid(BC::COL_TAX_ID)->nullable(); // FK → taxes
            $table->enum(BC::COL_STT_LB, array_column(PurchaseStatus::cases(), 'value'))->default(PurchaseStatus::Draft->value)->index(); // ? boot/save should mirror the 'status' as the index of the case here // ? booted and saving should enforce the default in nullish cases
            $table->unsignedInteger('status')->default(0); // ? this is a legacy way of saving, which should reflex the index of the cases in the PurchaseStatus enum // ? for it to be the index of PurchaseStatus::Paid->value: a. if a row in DC::TABLE_ORDERS is linked, its BC::COL_PAY_STT needs to be in [PaymentStatus::Authorized->value, PaymentStatus::Completed->value]; b. if a row in DC::TABLE_BILLS is linked, its BC::COL_PAY_STT needs to be in [PaymentStatus::Authorized->value, PaymentStatus::Completed->value]; c. if a row in DC::TABLE_INVS is linked, its BC::COL_PAY_STT needs to be in [PaymentStatus::Authorized->value, PaymentStatus::Completed->value]; if any of these is met but not all, set as PurchaseStatus::PartiallyPaid->value; if none, set as PurchaseStatus::Unpaid->value // ? PurchasesStatus::Draft->value is reserved only for tests where the now() is before the send_date and none of the conditions before are met.
            $this->addProductSecurityCoverageColumns($table);
            $table->dateTime(BC::COL_PRC_DT)->useCurrent();
            $table->dateTime(BC::COL_SD_DT)->nullable(); // ? HAS to be gt than purchase_date if not null
            $table->unsignedTinyInteger(BC::COL_DSC_APL)->default(0); // ? pseudoboolean; campled odds to 1, evens to 0 => the discounts themselves are found in the linked DC::TABLE_ORDERS / DC::TABLE_BILLS // DC::TABLE_INVS // DC::TABLE_PAY linked
            $table->unsignedTinyInteger(BC::COL_SHIP_DSP)->default(1); // ? pseudoboolean; campled odds to 1, evens to 0
            $this->addNfeColumns($table);
            $this->addShippingColumns($table); // ? on save, the shipping columns found in the linked row of DC::TABLE_BILLS / DC::TABLE_INVS / DC::TABLE_ORDERS (always checking if, in fact, they have each possible column on the schema) should be updated to mirror the ones in this table
            foreach (
                [
                    BC::COL_WRH_ID => DC::TABLE_WHS,
                    BC::COL_TAX_ID => DC::TABLE_TAXES,
                    BC::COL_OD_ID  => DC::TABLE_ORDERS,
                    BC::COL_BL_ID  => DC::TABLE_BILLS,
                    BC::COL_INV_ID => DC::TABLE_INVS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->json('delivery')->nullable();
            $table->json('attachments')->nullable();
            $table->json('taxes')->nullable(); // ? this should merge, on save, the not included ids from the BC::COL_TXS_LST (array or null) column from the linked DC::TABLE_PAY and DC::TABLE_BNK_TRF, filtered by valid ids in DC::TABLE_TAXES, as well as BC::COL_TAX_ID (string or null) in the linked row of DC::TABLE_INVS, DC::TABLE_ORDERS, 'taxes' (array or null) in DC::TABLE_BILLS
            $this->addAuditColumns($table);
            $this->addFailureTrackingColumns($table);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_WRH_ID,
                    BC::COL_TAX_ID,
                    BC::COL_OD_ID,
                    BC::COL_BL_ID,
                    BC::COL_INV_ID,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
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
