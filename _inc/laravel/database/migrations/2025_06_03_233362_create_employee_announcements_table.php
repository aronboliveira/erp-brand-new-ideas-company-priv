<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEmployeeAnnouncementsTable extends Migration
{
    private const ENTITY = 'employee';
    private const TABLE = self::ENTITY . '_announcements';
    private const COL_EMPLOYEE = self::ENTITY . '_id';
    private const COL_ANNOUNCEMENT = 'announcement_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();           // ! CHANGED
            $table->uuid(self::COL_ANNOUNCEMENT);         // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE);             // ! CHANGED
            $table->timestamps();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR);              // ! CHANGED
            foreach (
                [
                    self::COL_ANNOUNCEMENT                   => self::TABLE,
                    self::COL_EMPLOYEE                       => DatabaseConstants::TABLE_EMPLOYEES,
                    DatabaseConstants::COL_TABLE_CREATOR     => DatabaseConstants::TABLE_USERS,
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
                    self::COL_ANNOUNCEMENT,
                    self::COL_EMPLOYEE,
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
