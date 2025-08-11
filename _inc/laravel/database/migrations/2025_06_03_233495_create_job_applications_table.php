<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJobApplicationsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_JOB_APPS;
    private const COL_JOB = 'job';
    private const COL_CQ = 'custom_question';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();           // ! CHANGED
            $table->uuid(self::COL_JOB);                     // ! CHANGED
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('profile')->nullable();
            $table->string('resume')->nullable();
            $table->text('cover_letter')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->integer('stage')->default(1);
            $table->integer('order')->default(0);
            $table->text('skill')->nullable();
            $table->integer('rating')->default(0);
            $table->integer('is_archive')->default(0);
            $table->text(self::COL_CQ)->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR);              // ! CHANGED
            foreach ([
                self::COL_JOB                    => DatabaseConstants::TABLE_JOBS,
                DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_JOB,
                DatabaseConstants::TABLE_CREATOR,
            ] as $column) {
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
