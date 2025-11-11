<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDealDiscussionsTable extends Migration
{
    private const TABLE = 'deal_discussions';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid(AC::COL_DL)->index();
                $table->text('comment');
                $table->uuid(DC::TABLE_CREATOR)->nullable();
                $table->uuid(DC::TABLE_UPDATER)->nullable();
                $table->timestamps();
                $table->foreign(AC::COL_DL)
                    ->references('id')
                    ->on(DC::TABLE_DEALS)
                    ->cascadeOnDelete();
                foreach (
                    [
                        DC::TABLE_UPDATER => DC::TABLE_USERS,
                        DC::TABLE_CREATOR => DC::TABLE_USERS,
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
                    AC::COL_DL,
                    DC::TABLE_CREATOR,
                    DC::TABLE_UPDATER,
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
