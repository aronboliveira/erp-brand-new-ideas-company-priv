<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDealsTable extends Migration
{
    private const TABLE = DC::TABLE_DEALS;
    private const COL_PL = 'pipeline_id';
    private const COL_STG = 'stage_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('phone')->nullable();
            $table->decimal('price', 15, 2)->default(0.00);
            $table->uuid(self::COL_PL)->nullable()->index();
            $table->uuid(self::COL_STG)->nullable();
            $table->integer('group_id')->index();
            $table->text('sources')->nullable();
            $table->text('products')->nullable();
            $table->text('notes')->nullable();
            $table->text('labels')->nullable();
            $table->text('permissions')->nullable();
            $table->string('status')->nullable();
            $table->integer('order')->default(0);
            $table->integer('is_active')->default(1);
            $table->timestamps();
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            foreach (
                [
                    self::COL_PL                       => DC::TABLE_PIPELINES,
                    self::COL_STG                          => DC::TABLE_STAGES,
                    DC::TABLE_CREATOR    => DC::TABLE_USERS,
                    DC::TABLE_UPDATER    => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_PL,
                    self::COL_STG,
                    DC::TABLE_CREATOR,
                    DC::TABLE_UPDATER
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
