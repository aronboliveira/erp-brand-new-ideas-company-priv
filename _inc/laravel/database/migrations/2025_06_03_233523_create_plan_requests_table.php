<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePlanRequestsTable extends Migration
{
    private const TABLE      = 'plan_requests';
    private const COL_REQUEST = 'requested_plan';
    private const COL_USER   = 'user_id';
    private const COL_PLAN   = 'plan_id';

    public function up(): void
    {
        if (!Schema::hasColumn(DatabaseConstants::TABLE_USERS, self::COL_REQUEST)) {
            Schema::table(DatabaseConstants::TABLE_USERS, function (Blueprint $table): void {
                $table->uuid(self::COL_REQUEST)
                    ->nullable()
                    ->after('plan_expire_date');
                $table->foreign(self::COL_REQUEST)
                    ->references('id')
                    ->on(DatabaseConstants::TABLE_PLANS)
                    ->onDelete('set null');
            });
        }
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();                 // ! CHANGED from bigIncrements
            $table->uuid(self::COL_USER);                  // ! CHANGED from integer
            $table->uuid(self::COL_PLAN);                  // ! CHANGED from integer
            $table->string('duration', 20)->default('monthly');
            $table->timestamps();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
            foreach (
                [
                    self::COL_USER                  => DatabaseConstants::TABLE_USERS,
                    self::COL_PLAN                  => DatabaseConstants::TABLE_PLANS,
                    DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->onDelete($column === self::COL_USER || $column === self::COL_PLAN ? 'cascade' : 'set null');
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_USER,
                    self::COL_PLAN,
                    DatabaseConstants::COL_TABLE_CREATOR,
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
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
