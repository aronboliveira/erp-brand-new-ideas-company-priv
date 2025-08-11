<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTimeTrackersTable extends Migration
{
    private const TABLE = 'time_trackers';
    private const COL_PROJECT = 'project_id';
    private const COL_TASK = 'task_id';
    private const COL_CREATOR = 'created_by';
    private const T = 'time';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();                     // ! CHANGED
            $table->uuid(self::COL_PROJECT)->nullable();            // ! CHANGED
            $table->uuid(self::COL_TASK)->nullable();               // ! CHANGED
            $table->text('tag_id')->nullable();
            $table->string('name')->nullable();
            $table->integer('is_billable')->default(0);
            $table->dateTime('start_' . self::T)->nullable();
            $table->dateTime('end_' . self::T)->nullable();
            $table->string('total_' . self::T)->default('0');
            $table->string('is_active')->default('1');
            $table->uuid(self::COL_CREATOR)->nullable();            // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_PROJECT  => DatabaseConstants::TABLE_PROJECTS,
                self::COL_TASK     => DatabaseConstants::TABLE_PROJ_TSKS,
                self::COL_CREATOR  => DatabaseConstants::TABLE_USERS,
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
                self::COL_PROJECT,
                self::COL_TASK,
                self::COL_CREATOR,
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
