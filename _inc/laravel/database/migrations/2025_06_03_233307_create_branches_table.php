<?php

use App\Config\Constants\{
    CompaniesConstants as CPC,
    DatabaseConstants as DC
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBranchesTable extends Migration
{
    private const TABLE = DC::TABLE_BRANCHES;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string(CPC::COL_BRC_NM)->unique()->index();
                $table->text('address')->nullable()->index();
                $table->string('phone', 32)->nullable();
                $table->string(CPC::COL_FND)->nullable()->default(DC::DEFAULT_UUID);
                $table->uuid(CPC::COL_MNG)->nullable()->default(DC::DEFAULT_UUID);
                $table->uuid(CPC::COL_ADM)->nullable()->default(DC::DEFAULT_UUID);
                $table->text('description')->nullable();
                $table->text('departments')->nullable();
                $table->decimal('budget', 10, 2)->default(0.00);
                $table->decimal('expenses', 10, 2)->default(0.00);
                $table->decimal('profit', 10, 2)->default(0.00);
                $table->timestamps();
                $table->uuid(DC::TABLE_CREATOR)->nullable()->default(DC::DEFAULT_UUID);
                $table->uuid(DC::TABLE_UPDATER)->nullable()->default(DC::DEFAULT_UUID);
                foreach ([DC::TABLE_CREATOR, DC::TABLE_UPDATER, CPC::COL_ADM, CPC::COL_MNG] as $col)
                    $table->foreign($col)
                        ->references('id')
                        ->on(DC::TABLE_USERS)
                        ->nullOnDelete();
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                foreach ([CPC::COL_ADM, CPC::COL_MNG, DC::TABLE_UPDATER, DC::TABLE_CREATOR] as $col) {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
                }
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DC::TABLE_CREATOR
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
