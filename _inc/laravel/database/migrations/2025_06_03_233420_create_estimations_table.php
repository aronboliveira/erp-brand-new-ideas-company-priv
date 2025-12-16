<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\FinancialEstimationStatus;
use App\Traits\{HasFormalProjections, HasNullableAuditColumns, HasPaymentRequestColumns, RegistersShipping};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEstimationsTable extends Migration
{
    use HasFormalProjections, HasNullableAuditColumns, HasPaymentRequestColumns, RegistersShipping;
    private const TABLE = DC::TABLE_EST;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid(BC::COL_EST_ID)->index()->unique(); // ? queryable secondary identifier
                $table->uuid(PJC::COL_CLIENT_ID)->index();
                $this->addPaymentRequestColumns($table, isProjection: true, issues: true, addIssueDays: 2);
                $this->addShippingColumns($table);
                $table->uuid(PJC::COL_PJ_ID)->nullable()->index();
                $table->uuid(BC::COL_TAX_ID)->nullable();
                $table->text(BC::COL_REF_N)->nullable();
                $this->addFormalProjectionColumns($table, 30);
                $table->enum('status', array_column(FinancialEstimationStatus::cases(), 'value'))->default(FinancialEstimationStatus::Open->value)->index();
                $table->text('terms')->nullable();
                foreach (
                    [
                        PJC::COL_PJ_ID => DC::TABLE_PROJECTS,
                        BC::COL_TAX_ID => DC::TABLE_TAXES,
                    ] as $col => $tableName
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tableName)
                        ->nullOnDelete();
                $table->foreign(PJC::COL_CLIENT_ID)
                    ->references('id')
                    ->on(DC::TABLE_CLIENTS)
                    ->restrictOnDelete();
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropPaymentRequestColumnForeigns($table, self::TABLE);
            $this->dropFormalProjectionColumnForeigns($table, self::TABLE);
            foreach (
                [
                    PJC::COL_PJ_ID,
                    BC::COL_TAX_ID,
                    PJC::COL_CLIENT_ID,
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
