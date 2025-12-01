<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateGoalTrackingsTable extends Migration
{
    private const TABLE = 'goal_trackings';
    private const DATE = 'date';
    private const COL_BRANCH   = 'branch';
    private const COL_GOAL_TYPE = 'goal_type';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();            // ! CHANGED
            $table->uuid(self::COL_BRANCH)->index();          // ! CHANGED
            $table->uuid(self::COL_GOAL_TYPE)->index();       // ! CHANGED
            $table->date('start_' . self::DATE);
            $table->date('end_' . self::DATE);
            $table->string('subject')->nullable();
            $table->string('rating')->nullable();
            $table->string('target_achievement')->nullable();
            $table->text('description')->nullable();
            $table->integer('status')->default(0);
            $table->integer('progress')->default(0);
            $table->timestamps();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->index();      // ! CHANGED
            foreach (
                [
                    self::COL_BRANCH                 => DatabaseConstants::TABLE_BRANCHES,
                    self::COL_GOAL_TYPE              => DatabaseConstants::TABLE_GOAL_TYPES,
                    DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_BRANCH,
                    self::COL_GOAL_TYPE,
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
