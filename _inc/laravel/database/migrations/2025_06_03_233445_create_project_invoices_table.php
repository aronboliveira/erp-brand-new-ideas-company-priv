<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProjectInvoicesTable extends Migration
{
    // ? this is just a "bridge" table to link projects and invoices, as a project can have multiple invoices and an invoice can be linked to multiple projects in some cases (though rare)
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PRJ_INV;
    public function up(): void
    {
        Schema::create(
            self::TABLE,
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid(BC::COL_INV_ID)->index();
                $table->uuid(PJC::COL_PJ_ID)->index();
                $table->unique([BC::COL_INV_ID, PJC::COL_PJ_ID], 'uniq_inv_prj');
                $table->uuid(BC::COL_BL_ID)->nullable()->index(); // * if the invoice is linked to a bill (through BC::COL_BL_ID), then it's the source of truth for that link
                $table->uuid(BC::COL_TAX_ID)->nullable();
                $table->date(BC::COL_DUE_DT)->index(); // * if the invoice has a BC::COL_DUE_DT, it is the source of truth for that due date
                $table->unsignedTinyInteger('status')->default(1); // ? constrained with the PaymentStatus::getAllIndexes ints at model level
                foreach (
                    [
                        BC::COL_BL_ID => DC::TABLE_BILLS,
                        BC::COL_TAX_ID => DC::TABLE_TAXES,
                    ] as $column => $referencedTable
                )
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->nullOnDelete();
                foreach (
                    [
                        BC::COL_INV_ID                => DC::TABLE_INVS,
                        PJC::COL_PJ_ID                => DC::TABLE_PROJECTS,
                    ] as $column => $referencedTable
                )
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->restrictOnDelete();
                $this->addAuditColumns($table);
                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_INV_ID,
                    PJC::COL_PJ_ID,
                    BC::COL_BL_ID,
                    BC::COL_TAX_ID,
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
