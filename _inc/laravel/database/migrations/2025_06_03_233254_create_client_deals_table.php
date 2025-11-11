<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateClientDealsTable extends Migration
{
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
            $table->timestamps();
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
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
            foreach (
                [
                    DC::TABLE_CREATOR => DC::TABLE_USERS,
                    DC::TABLE_UPDATER => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_CLIENT,
                    self::COL_DEAL,
                    DC::TABLE_CREATOR,
                    DC::TABLE_UPDATER
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
