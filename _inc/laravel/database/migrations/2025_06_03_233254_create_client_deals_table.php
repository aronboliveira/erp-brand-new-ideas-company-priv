<?php

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateClientDealsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = 'client_deals';
    private const COL_CLIENT = 'client_id';
    private const COL_DEAL = 'deal_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(self::COL_CLIENT)->index();
            $table->uuid(self::COL_DEAL)->index();
            $table->unique([self::COL_DEAL, self::COL_CLIENT]);
            foreach (
                [
                    self::COL_CLIENT => DC::TABLE_USERS,
                    self::COL_DEAL   => DC::TABLE_DEALS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    self::COL_CLIENT,
                    self::COL_DEAL,
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
