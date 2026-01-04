<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\ProposalStatus;
use App\Traits\{CustomerConnected, HasFormalProjections, HasNullableAuditColumns, HasPaymentRequestColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProposalsTable extends Migration
{
    use CustomerConnected, HasFormalProjections, HasPaymentRequestColumns, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PROPOSALS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 128)->index();
            $table->uuid(BC::COL_PPS_ID)->unique(); // ? queryable secondary identifier
            $this->addCustomerColumns($table, unique: false, nullable: false, onDelete: 'restrict');
            $this->addPaymentRequestColumns($table, isProjection: true, issues: true, addIssueDays: 7, prioritizesBillStatus: false);
            $table->unsignedTinyInteger('status')->default(0);
            $table->enum(BC::COL_STT_LB, array_column(ProposalStatus::cases(), 'value'))->default(ProposalStatus::Draft->value)->nullable()->index(); // ? nullable for testing
            $table->unsignedTinyinteger(BC::COL_IS_CNV)->default(0); // todo this should turn into a boolean when the system is more mature
            $table->unsignedSmallInteger('version')->min(1)->default(1); // ? enforced at boot/saving to be minimum 1
            $this->addFormalProjectionColumns($table, 14);
            $table->uuid(PJC::COL_REJ_BY)->nullable()->index();
            $table->datetime(BC::COL_REJ_AT)->nullable(); // ? nullable for testing purposes
            $table->text(BC::COL_REJ_RS)->nullable(); // ? nullable for testing purposes
            $table->uuid(PJC::COL_LD_ID)->nullable();
            $table->uuid(BC::COL_CNV_INV_ID)->nullable();
            $table->uuid(BC::COL_TAX_ID)->nullable();
            $table->json('employees')->nullable(); // * at boot/saving, should filter only for uuids that represent Employee queryable instances
            $table->json('customers')->nullable(); // * at boot/saving, should filter only for uuids that represent Customer queryable instances
            $table->json('signers')->nullable(); // * a simple list of names or uuids to represent signers data, while the signer is the one who accepts in fact
            foreach (
                [
                    PJC::COL_REJ_BY => DC::TABLE_USERS,
                    PJC::COL_LD_ID => DC::TABLE_LEADS,
                    BC::COL_TAX_ID => DC::TABLE_TAXES,
                    BC::COL_CNV_INV_ID => DC::TABLE_INVS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')->on($tbl)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropCustomerColumnForeigns($table, self::TABLE);
            $this->dropPaymentRequestColumnForeigns($table, self::TABLE);
            $this->dropFormalProjectionColumnForeigns($table, self::TABLE);
            foreach (
                [
                    PJC::COL_REJ_BY,
                    PJC::COL_LD_ID,
                    BC::COL_TAX_ID,
                    BC::COL_CNV_INV_ID,
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
