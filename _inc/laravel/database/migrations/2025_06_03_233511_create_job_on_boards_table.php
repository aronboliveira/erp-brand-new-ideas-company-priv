<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJobOnBoardsTable extends Migration
{
    private const TABLE = 'job_on_boards';
    private const S = 'salary';
    private const COL_APPLICATION = 'application_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();                            // ! CHANGED
            $table->uuid(self::COL_APPLICATION);
            $table->date('joining_date')->nullable();
            $table->string('status')->nullable();
            $table->integer('convert_to_employee')->default(0);
            $table->string('job_type')->nullable();
            $table->integer('days_of_week')->nullable();
            $table->integer(self::S)->nullable();
            $table->string(self::S . '_type')->nullable();
            $table->string(self::S . '_duration')->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR);
            foreach ([
                self::COL_APPLICATION            => DatabaseConstants::TABLE_JOB_APPS,
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
                self::COL_APPLICATION,
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
