<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, SettingsConstants as SC, UsersConstants as UC};
use App\Enums\MonthName;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateOrdersTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_ORDERS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(UC::COL_USER_ID)->index()->nullable(); // ? not every buyer is a registered user
            $table->uuid(BC::COL_OD_ID)->index(); // ? external order identifier, not clear purpose yet
            $table->string('name', 1024)->nullable();
            $table->string('email', 256)->unique()->nullable();
            $table->uuid(UC::COL_PLAN_ID)->index();
            $table->string(UC::COL_PLAN_NM, 124)->nullable(); // * when the system is more mature, then this can be enumerated

            $table->unsignedDecimal('price', 15, 2)->default(0.00);
            $table->unsignedDecimal('discount', 15, 2)->default(0.00)->nullable();
            $table->string(BC::COL_PRC_CUR, 10)->default(SC::DEF_SITE_CURRENCY_ID)->nullable();
            // * booted and saving should error out if the validation for the card AND the pix key AND the payslip id are not met
            $table->unsignedSmallInteger(BC::COL_N_INTR)->default(1)->nullable(); // ? nullable for testing purposes

            $table->string(BC::COL_CD_FLG, 20)->nullable(); // * when the system is more mature, then this can be enumerated
            $table->string(BC::COL_CD_NB, 19)->nullable(); // ? this should be stored encrypted in the model and strip out any non-numeric characters
            $table->string(BC::COL_CD_DG, 4)->nullable(); // ? this should be stored encrypted in the model and strip out any non-numeric characters
            $table->string(BC::COL_CD_HNM, 124)->nullable(); // ? this should be stored encrypted in the model
            $table->enum(BC::COL_CD_EX_M, MonthName::values())->default(MonthName::January->value)->nullable(); // * model should ensure this is never less than the current month if the year is the current year
            $table->string(BC::COL_CD_EX_Y, 4)->nullable(); // * model should ensure this is never less than the current year

            $table->uuid(BC::COL_TAX_ID)->nullable();
            $table->json(BC::COL_OT_TX_ID)->nullable(); // * model should ensure these taxes exist through queries on the taxes table

            $table->string(BC::COL_PIX_KEY)->index()->nullable(); // * pix key can be email, phone, cpf/cnpj or random key (max 36 characters) => model should constrain length according to its type
            // ? should be stored encrypted in the model

            $table->uuid(BC::COL_PSLP_ID)->index()->nullable();

            $table->enum(BC::COL_PAY_STT, PaymentStatus::values())->default(PaymentStatus::Undefined->value)->index()->nullable(); // * model should ensure valid transitions between statuses
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
