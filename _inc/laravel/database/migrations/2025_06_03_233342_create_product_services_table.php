<?php

use App\Config\Constants\{ActivitiesConstants as AC, BanksConstants as BKC, BillsConstants as BC, DatabaseConstants as DC, SettingsConstants as SC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProductServicesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PROD_SERVS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('sku')->unique();
            $table->decimal(BC::COL_SL_PRC, 15, 4)->default(0.0000);
            $table->decimal(BC::COL_PC_PRC, 15, 4)->default(0.0000);
            $table->json(BC::COL_AC_CUR)->nullable(); // * kept nullable for tests, but should be imposed as at least accepting the site default currency at booted and save
            $table->json(BC::COL_AC_MUNITS)->nullable(); // * array of accepted measurement units 
            $table->text('description')->nullable();
            $table->json('attributes')->nullable();
            $table->json('tags')->nullable();
            $table->string(DC::COL_PRO_IMG)->nullable(); // ? maybe product image? not clear
            $table->string('icon')->nullable(); // todo add default later
            $table->float('quantity')->default(0.0); // * maybe move to separate stock table and later should to unsigned bigint
            $table->uuid(BC::COL_TAX_ID)->nullable();
            $table->uuid(BC::COL_CAT_ID)->nullable(); // ? the main category
            $table->json('categories')->nullable(); // ? constrained in booted and saving for filtering only arrays that have id/cateogry_id of existing categories
            $table->json(DC::COL_RL_CAT)->nullable(); // ? constrained in booted and saving for filtering only arrays that have id/cateogry_id of existing categories // ? used for graph connections of user preferences
            $table->uuid(BC::COL_UNIT_ID)->nullable(); // * kept for legacy code only, not funcional due to inheritance to ProductServiceUnit, but usable to query a unit
            $table->unsignedBigInteger(BC::COL_UNITS_SOLD)->default(0)->nullable(); // ? nullable for tests
            $table->unsignedBigInteger(BC::COL_UNITS_CNC)->default(0)->nullable(); // ? nullable for tests
            $table->unsignedBigInteger(BC::COL_UNITS_RTRN)->default(0)->nullable(); // ? nullable for tests
            $table->string('type')->default('0'); // * this is not clear yet, so it will be kept for compatibility, but probably referes to the index of the label, so 0 to 9
            $table->uuid(BKC::COL_SL_COA)->nullable();
            $table->uuid(BKC::COL_EXP_COA)->nullable();
            $table->timestamp(AC::COL_AV_FROM)->index()->default(now())->nullable();
            $table->timestamp(AC::COL_AV_UNTIL)->index()->default(now()->addYears(1))->nullable();
            $table->boolean(AC::COL_IA)->default(true)->nullable(); // ? default true para testes
            $table->boolean(BC::COL_ON_SALE)->default(false)->nullable();
            $table->boolean(BC::COL_IS_LK)->default(false)->nullable();
            $table->boolean(BC::COL_IS_TRS)->default(false)->nullable();
            foreach (
                [
                    BC::COL_TAX_ID              => DC::TABLE_TAXES,
                    BC::COL_CAT_ID              => DC::TABLE_PROD_SERV_CATS,
                    BKC::COL_SL_COA             => DC::TABLE_COAS,
                    BKC::COL_EXP_COA            => DC::TABLE_COAS,
                ] as $c => $t
            )
                $table->foreign($c)
                    ->references('id')
                    ->on($t)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_TAX_ID,
                    BC::COL_CAT_ID,
                    BKC::COL_SL_COA,
                    BKC::COL_EXP_COA,
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
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
