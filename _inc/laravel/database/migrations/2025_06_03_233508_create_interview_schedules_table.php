<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateInterviewSchedulesTable extends Migration
{
    private const TABLE = 'interview_schedules';
    private const COL_EMPLOYEE = 'employee';
    private const COL_CAND = 'candidate';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->uuid(self::COL_CAND); // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE); // ! CHANGED
            $table->date('date');
            $table->time('time');
            $table->text('comment')->nullable();
            $table->string(self::COL_EMPLOYEE . '_response')->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR); // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_CAND              => DatabaseConstants::TABLE_JOB_APPS,
                self::COL_EMPLOYEE          => DatabaseConstants::TABLE_USERS,
                DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
            ] as $col => $tbl)
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_CAND,
                self::COL_EMPLOYEE,
                DatabaseConstants::TABLE_CREATOR,
            ] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
