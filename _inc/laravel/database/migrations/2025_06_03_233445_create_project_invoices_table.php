<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProjectInvoicesTable extends Migration
{
    private const ENTITY = 'project';
    private const TABLE = self::ENTITY . '_invoices';
    private const COL_INVOICE = 'invoice_id';
    private const COL_PROJECT = self::ENTITY . '_id';
    private const COL_CLIENT = 'client_id';
    private const COL_TAX    = 'tax_id';
    public function up(): void
    {
        Schema::create(
            self::TABLE,
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid(self::COL_INVOICE)->index();
                $table->uuid(self::COL_PROJECT)->index();
                $table->uuid(self::COL_CLIENT)->index();
                $table->uuid(self::COL_TAX)->index();
                $table->date('due_date');
                $table->smallInteger('status')->default(1);
                $table->timestamps();
                $table->uuid(DatabaseConstants::COL_TABLE_CREATOR); // ! CHANGED
                foreach (
                    [
                        self::COL_INVOICE                => DatabaseConstants::TABLE_INVS,
                        self::COL_PROJECT                => DatabaseConstants::TABLE_PROJECTS,
                        self::COL_CLIENT                 => DatabaseConstants::TABLE_CLIENTS,
                        self::COL_TAX                    => DatabaseConstants::TABLE_TAXES,
                        DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
                    ] as $column => $referencedTable
                )
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_INVOICE,
                    self::COL_PROJECT,
                    self::COL_CLIENT,
                    self::COL_TAX,
                    DatabaseConstants::COL_TABLE_CREATOR,
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
