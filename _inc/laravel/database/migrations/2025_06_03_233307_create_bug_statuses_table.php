<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBugStatusesTable extends Migration
{
    // ! Se AC::COL_TSK_STT na tabela de bugs for string/código e BugStatus.id for UUID, há desencontro de tipos/semântica.
    private const TABLE = 'bug_statuses';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer(AC::COL_OD)->default(0);
            $table->string(AC::COL_TT);
            $table->timestamps();
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            foreach (
                [
                    DC::TABLE_CREATOR  => DC::TABLE_USERS,
                    DC::TABLE_UPDATER  => DC::TABLE_USERS,
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
            try {
                foreach ([DC::TABLE_UPDATER, DC::TABLE_CREATOR] as $col)
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DC::TABLE_CREATOR
                        . ' on table '
                        . self::TABLE
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
