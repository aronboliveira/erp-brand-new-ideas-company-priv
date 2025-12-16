<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, SettingsConstants as SC, UsersConstants as UC};
use App\Enums\{MonthName, PaymentMethod, PaymentStatus};
use App\Traits\{HasCreditCardInfo, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateOrdersTable extends Migration
{
    use HasCreditCardInfo, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_ORDERS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(UC::COL_USER_ID)->nullable()->index(); // ? not every buyer is a registered user
            $table->uuid(BC::COL_OD_ID)->index(); // ? external order identifier, not clear purpose yet
            $table->string('name', 1024)->nullable();
            $table->string('email', 256)->nullable()->unique();
            $table->uuid(UC::COL_PLAN_ID)->index();
            $table->string(UC::COL_PLAN_NM, 124)->nullable(); // * when the system is more mature, then this can be enumerated

            $table->unsignedDecimal('price', 15, 2)->default(0.00);
            $table->unsignedDecimal('discount', 15, 2)->default(0.00)->nullable();
            $table->string(BC::COL_PRC_CUR, 10)->default(SC::DEF_SITE_CURRENCY_ID)->nullable();
            // * booted and saving should error out if the validation for the card AND the pix key AND the payslip id are not met
            $table->unsignedSmallInteger(BC::COL_N_INTR)->default(1)->nullable(); // ? nullable for testing purposes

            $this->addCreditCardInfoColumns($table);

            $table->uuid(BC::COL_TAX_ID)->nullable();
            $table->json(BC::COL_OT_TX_ID)->nullable(); // * model should ensure these taxes exist through queries on the taxes table

            $table->string(BC::COL_PIX_KEY)->nullable()->index(); // * pix key can be email, phone, cpf/cnpj or random key (max 36 characters) => model should constrain length according to its type
            // ? should be stored encrypted in the model

            $table->uuid(BC::COL_PSLP_ID)->nullable()->index();

            $table->enum(BC::COL_PAY_STT, PaymentStatus::values())->default(PaymentStatus::Undefined->value)->nullable()->index(); // * model should ensure valid transitions between statuses
            $table->enum(BC::COL_PAY_TP, PaymentMethod::values())->default(PaymentMethod::Other->value)->index();
            $table->longText('receipt')->nullable(); // * not clear for now, so kept for legacy purposes
            $table->json(BC::COL_RCP_MD)->nullable(); // * saving all useful receipt data here
            $table->foreign(UC::COL_PLAN_ID)
                ->references('id')
                ->on(DC::TABLE_PLANS)
                ->cascadeOnDelete();
            foreach (
                [
                    BC::COL_PSLP_ID             => DC::TABLE_PSLP,
                    BC::COL_TAX_ID              => DC::TABLE_TAXES,
                    UC::COL_USER_ID             => DC::TABLE_USERS,
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
                    UC::COL_PLAN_ID,
                    BC::COL_PSLP_ID,
                    BC::COL_TAX_ID,
                    UC::COL_USER_ID,
                ] as $col
            ) {
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
