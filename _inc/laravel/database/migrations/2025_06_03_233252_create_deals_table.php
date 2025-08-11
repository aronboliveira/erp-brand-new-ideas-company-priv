<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDealsTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_DEALS;
    private const COL_PL = 'pipeline_id';
    private const COL_STG = 'stage_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();         // ! CHANGED
            $table->string('name');
            $table->string('phone')->nullable();
            $table->decimal('price', 15, 2)->default(0.0);
            $table->uuid(self::COL_PL); // ! CHANGED
            $table->uuid(self::COL_STG); // ! CHANGED
            $table->integer('group_id');           // ! CHANGED
            $table->string('sources')->nullable();
            $table->string('products')->nullable();
            $table->text('notes')->nullable();
            $table->string('labels')->nullable();
            $table->string('permissions')->nullable(); // ! CHANGED
            $table->string('status')->nullable();
            $table->integer('order')->default(0);
            $table->integer('is_active')->default(1);
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR); // ! CHANGED
            foreach ([
                self::COL_PL                       => DatabaseConstants::TABLE_PIPELINES,
                self::COL_STG                          => DatabaseConstants::TABLE_STAGES,
                DatabaseConstants::TABLE_CREATOR    => DatabaseConstants::TABLE_USERS,
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
                self::COL_PL,
                self::COL_STG,
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
