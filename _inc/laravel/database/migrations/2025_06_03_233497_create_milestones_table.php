<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateMilestonesTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_MSS;
    private const D = 'date';
    private const COL_PROJECT = 'project_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();               // ! CHANGED
            $table->uuid(self::COL_PROJECT)->index();          // ! CHANGED
            $table->string('title');
            $table->string('status');
            $table->string('progress')->nullable();
            $table->double('cost', 15, 2)->default(0.00);
            $table->date('start_' . self::D)->nullable();
            $table->date('due_' . self::D)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
            foreach (
                [
                    self::COL_PROJECT                 => DatabaseConstants::TABLE_PROJECTS,
                    DatabaseConstants::COL_TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
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
                    self::COL_PROJECT,
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
