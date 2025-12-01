<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLeadsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_LEADS;
    private const COL_USER = 'user_id';
    private const COL_PL = 'pipeline_id';
    private const COL_STG = 'stage_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();          // ! CHANGED
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->uuid(self::COL_USER); // ! CHANGED
            $table->uuid(self::COL_PL); // ! CHANGED
            $table->uuid(self::COL_STG); // ! CHANGED
            $table->string('sources')->nullable();
            $table->string('products')->nullable();
            $table->text('notes')->nullable();
            $table->string('labels')->nullable();
            $table->integer('order')->default(0);
            $table->integer('is_active')->default(1);
            $table->integer('is_converted')->default(0);
            $table->date('date')->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR);
            foreach (
                [
                    self::COL_USER                        => DatabaseConstants::TABLE_USERS,
                    self::COL_PL                    => DatabaseConstants::TABLE_PIPELINES,
                    self::COL_STG                       => DatabaseConstants::TABLE_LEAD_STAGES,
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
                    self::COL_USER,
                    self::COL_PL,
                    self::COL_STG,
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
