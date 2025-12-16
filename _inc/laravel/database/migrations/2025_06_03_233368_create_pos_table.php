<?php

use App\Config\Constants\{BillsConstants as BC, CompaniesConstants as CC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{PosStatus, PosType};
use App\Traits\{HasNullableAuditColumns, RegistersShipping, TracksFailures};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePosTable extends Migration
{
    use HasNullableAuditColumns, RegistersShipping, TracksFailures;
    private const TABLE = DC::TABLE_POS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(BC::COL_POS_ID)->index(); // ? pos queryable identifier
            $table->date(BC::COL_POS_DT)->nullable(); // * it's not clear why a POS would need a date different from created at, but it's in the specs in the legacy code, maybe it's the actual date of its physical creation?
            $table->string(BC::COL_DVC_SR)->nullable()->index(); // ? nullable for tests, device serial number
            $table->string(BC::COL_MAC_ADR)->nullable(); // ? nullable for tests, application version
            $table->string(BC::COL_IP_ADR)->nullable(); // ? nullable for tests, ip address
            $table->string(BC::COL_DVC_MD)->nullable(); // ? nullable for tests, device model
            $table->string(BC::COL_OPS_SYS)->nullable(); // ? nullable for tests, operating system
            $table->uuid(CC::COL_CP_ID)->nullable()->index(); // ? nullable for tests
            $table->uuid(UC::COL_BRC_ID)->nullable(); // ? nullable for tests
            $table->uuid(BC::COL_WRH_ID)->nullable()->index();
            $table->uuid(UC::COL_DEP_ID)->nullable();
            $table->uuid(BC::COL_CST_ID)->nullable();
            $table->uuid(BC::COL_CAT_ID)->nullable(); // * it's not clear why POS would need a product category, but it's in the specs in the legacy code
            $table->uuid(CC::COL_MNF_ID)->nullable(); // ? nullable for tests
            $table->string(CC::COL_MNF_NM)->nullable(); // ? nullable for tests // ? the name of the manufacturer operating the POS
            $table->uuid(UC::COL_VD_ID)->nullable(); // ? nullable for tests
            $table->string(CC::COL_VD_NM, 255)->nullable(); // ? nullable for tests // ? the name of the vendor operating the POS
            $table->enum('type', PosType::values())->default(PosType::Other->value)->nullable()->index(); // ? nullable for tests
            $table->integer('status')->default(0); // * this should be converted to enum later, it's not clear in the specs what statuses a POS can have
            $table->integer(BC::COL_SHIP_DSP)->default(1);
            $table->enum(BC::COL_STT_LB, PosStatus::values())->default(PosStatus::Other->value)->nullable()->index(); // ? nullable for tests
            $table->boolean(BC::COL_IO)->default(true)->nullable(); // ? nullable for tests
            $table->boolean(BC::COL_ACP_CRD)->default(true)->nullable(); // ? nullable for tests
            $table->boolean(BC::COL_ACP_DBT)->default(true)->nullable(); // ? nullable for tests
            $table->boolean(BC::COL_ACP_PIX)->default(true)->nullable(); // ? nullable for tests
            $table->boolean(BC::COL_ACP_CSH)->default(true)->nullable(); // ? nullable for tests
            $table->json(BC::COL_PIX_QR)->nullable(); // ? nullable for tests, the data for the qr code used in pix payments
            $table->json(BC::COL_ACP_FLG)->nullable(); // ? nullable for tests, the card flags
            $table->datetime(BC::COL_LST_TRS)->nullable(); // ? nullable for tests
            $table->datetime(DC::COL_LA)->nullable(); // ? nullable for tests
            $table->unsignedInteger(BC::COL_TRS_CNT)->default(0)->nullable(); // ? nullable for tests
            $table->unsignedDecimal(BC::COL_ACC_TTL)->default(0.00)->nullable(); // ? nullable for tests
            $table->unsignedDecimal(BC::COL_SVC_FEE, 5, 2)->default(0.00)->nullable(); // ? nullable for tests
            foreach (
                [
                    CC::COL_CP_ID => DC::TABLE_USERS,
                    BC::COL_WRH_ID => DC::TABLE_WRH,
                    CC::COL_MNF_ID => DC::TABLE_USERS,
                    UC::COL_VD_ID => DC::TABLE_VENDORS,
                    UC::COL_BRC_ID => DC::TABLE_BRANCHES,
                    UC::COL_DEP_ID => DC::TABLE_DEPARTMENTS,
                    BC::COL_CST_ID => DC::TABLE_CUSTOMERS,
                    BC::COL_CAT_ID => DC::TABLE_PROD_SERV_CATS,
                ] as $col => $tab
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tab)
                    ->nullOnDelete();
            $this->addBillingColumns($table);
            $this->addAuditColumns($table);
            $this->addFailureTrackingColumns($table);
        });
    }
    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_CST_ID,
                    BC::COL_WRH_ID,
                    BC::COL_CAT_ID,
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
