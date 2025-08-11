<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJobsTable extends Migration
{
    private const TABLE       = DatabaseConstants::TABLE_JOBS;
    private const COL_BRANCH  = 'branch';
    private const COL_CATEGORY = 'category';
    private const COL_CREATED_BY = 'created_by';
    private const COL_CQ = 'custom_question';
    private const D = 'date';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                       // ! CHANGED
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('requirement')->nullable();
            $table->uuid(self::COL_BRANCH);                      // ! CHANGED
            $table->uuid(self::COL_CATEGORY);                    // ! CHANGED
            $table->text('skill')->nullable();
            $table->integer('position')->nullable();
            $table->date('start_' . self::D)->nullable();
            $table->date('end_' . self::D)->nullable();
            $table->string('status')->nullable();
            $table->string('applicant')->nullable();
            $table->string('visibility')->nullable();
            $table->string('code')->nullable();
            $table->string(self::COL_CQ)->nullable();
            $table->uuid(self::COL_CREATED_BY);                  // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_BRANCH => DatabaseConstants::TABLE_BRANCHES,
                self::COL_CATEGORY => DatabaseConstants::TABLE_JOB_CATS,
                self::COL_CREATED_BY => DatabaseConstants::TABLE_USERS,
                self::COL_CQ => DatabaseConstants::TABLE_CUSTOM_QUESTIONS
            ] as $col => $tbl)
                $table->foreign($col)
                    ->references('id')->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_BRANCH,
                self::COL_CATEGORY,
                self::COL_CQ,
                self::COL_CREATED_BY
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
