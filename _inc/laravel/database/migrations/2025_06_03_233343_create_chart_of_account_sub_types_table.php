<?php

use App\Config\Constants\{ChartsConstants, DatabaseConstants};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

class CreateChartOfAccountSubTypesTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_COA_SUBTYPES;

    private array $foreignKeys = [
        ChartsConstants::COL_TP         => DatabaseConstants::TABLE_COA_TYPES,
        DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
    ];

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(ChartsConstants::COL_NM)->default('');
            $table->uuid(ChartsConstants::COL_TP);
            $table->string(ChartsConstants::COL_TP_NM)->default('Undefined');
            $table->uuid(DatabaseConstants::TABLE_CREATOR);
            $table->timestamps();
            foreach ($this->foreignKeys as $column => $refTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($refTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable(self::TABLE))
            return;
        Schema::table(self::TABLE, function (Blueprint $table) {
            foreach (array_keys($this->foreignKeys) as $column) {
                if (!Schema::hasColumn(self::TABLE, $column))
                    continue;
                try {
                    $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning("Failed to drop foreign key on `{$column}`: {$e->getMessage()}");
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
