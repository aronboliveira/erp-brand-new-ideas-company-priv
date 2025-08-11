<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateExpensesTable extends Migration
{
    private const TABLE = 'expenses';
    private const COL_PROJ = 'project_id';
    private const COL_TSK = 'task_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                // ! CHANGED
            $table->string('name');
            $table->date('date')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 16, 2)->default(0.00);
            $table->string('attachment')->nullable();
            $table->uuid(self::COL_PROJ);                   // ! CHANGED
            $table->uuid(self::COL_TSK);                      // ! CHANGED
            $table->uuid(DatabaseConstants::TABLE_CREATOR);                   // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_PROJ                    => DatabaseConstants::TABLE_PROJECTS,
                self::COL_TSK                     => DatabaseConstants::TABLE_PROJ_TSKS,
                DatabaseConstants::TABLE_CREATOR   => DatabaseConstants::TABLE_USERS,
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
                self::COL_PROJ,
                self::COL_TSK,
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
