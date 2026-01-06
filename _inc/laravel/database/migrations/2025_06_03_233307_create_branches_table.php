<?php

use App\Config\Constants\{
    CompaniesConstants as CPC,
    DatabaseConstants as DC
};
use App\Enums\CountryName;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

class CreateBranchesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_BRANCHES;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('company')->nullable()->index(); // ? nullable for tests
                $table->string('name')->unique();
                $table->string('country', 64)->default(CountryName::Brazil->value)->nullable()->index();
                $table->string('state', 1024)->nullable()->index();
                $table->string('city', 1024)->nullable()->index();
                $table->string('zip', 32)->nullable();
                $table->text('address')->nullable(); // todo normalize on model
                $table->string('phone', 32)->nullable()->index();
                $table->string('email', 254)->nullable()->index();
                $table->string(CPC::COL_FND)->nullable()->default(DC::DEFAULT_UUID);
                $table->uuid(CPC::COL_MNG)->nullable()->default(DC::DEFAULT_UUID);
                $table->uuid(CPC::COL_ADM)->nullable()->default(DC::DEFAULT_UUID);
                $table->text('description')->nullable();
                $table->text('departments')->nullable();
                $table->decimal('budget', 15, 2)->default(0.00);
                $table->json('budgets')->nullable();
                $table->decimal('expenses', 15, 2)->default(0.00);
                $table->decimal('profit', 15, 2)->default(0.00);
                $this->addAuditColumns($table);
                foreach (['company', CPC::COL_ADM, CPC::COL_MNG] as $col)
                    $table->foreign($col)
                        ->references('id')
                        ->on(DC::TABLE_USERS)
                        ->nullOnDelete();
            });
        DB::statement('ALTER TABLE ' . self::TABLE . ' ADD INDEX idx_address (address(255))');
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            try {
                foreach (['company', CPC::COL_ADM, CPC::COL_MNG] as $col) {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
                }
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DC::COL_TABLE_CREATOR
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
