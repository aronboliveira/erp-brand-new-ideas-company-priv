<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateUserDealsTable extends Migration
{
    private const TABLE = 'user_deals';
    private const COL_DEAL = 'deal_id';
    private const COL_USER = 'user_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(self::COL_DEAL);
            $table->uuid(self::COL_USER);
            $table->timestamps();
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            $table->foreign(self::COL_DEAL)
                ->references('id')
                ->on(DC::TABLE_DEALS)
                ->cascadeOnDelete();
            foreach (
                [
                    self::COL_USER     => DC::TABLE_USERS,
                    DC::TABLE_CREATOR  => DC::TABLE_USERS,
                    DC::TABLE_UPDATER  => DC::TABLE_USERS
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
                    self::COL_DEAL,
                    self::COL_USER,
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
