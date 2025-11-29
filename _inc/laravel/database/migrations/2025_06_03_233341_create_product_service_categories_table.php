<?php

use App\Config\Constants\{ActivitiesConstants as AC, BanksConstants as BKC, DatabaseConstants as DC};
use App\Enums\ConsumableType;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProductServiceCategoriesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE_NAME = DC::TABLE_PROD_SERV_CATS;
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('code')->unique()->index()->nullable(); // ? nullable for testing
            $table->string('type')->default('0'); // * this is not clear yet, so it will be kept for compatibility, but probably referes to the index of the label, so 0 to 9
            $table->enum(DC::COL_TP_LB, ConsumableType::values())->default(ConsumableType::Service->value)->nullable()->index();
            $table->uuid(BKC::COL_COA)->nullable();
            $table->string('color')->default('#fc544b')->nullable();
            $table->string('icon')->nullable();
            $table->json('attributes')->nullable();
            $table->text('description')->nullable();
            $table->json(DC::COL_RL_CAT)->nullable();
            $table->text('notes')->nullable();
            $table->boolean(AC::COL_IA)->default(true)->nullable();
            foreach (
                [
                    BKC::COL_COA => DC::TABLE_COAS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE_NAME);
            foreach (
                [
                    BKC::COL_COA,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE_NAME, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
