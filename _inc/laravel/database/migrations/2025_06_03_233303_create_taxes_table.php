<?php

use App\Config\Constants\DatabaseConstants as DB;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTaxesTable extends Migration
{
    private const TABLE = DB::TABLE_TAXES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->decimal('rate', 5, 2)->default(0.00);
            $table->timestamps();
            $table->uuid(DB::TABLE_CREATOR)->nullable();
            $table->uuid(DB::TABLE_UPDATER)->nullable();
            foreach (
                [
                    DB::TABLE_CREATOR  => DB::TABLE_USERS,
                    DB::TABLE_UPDATER  => DB::TABLE_USERS,
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
            try {
                foreach ([DB::TABLE_UPDATER, DB::TABLE_CREATOR] as $col)
                    if (Schema::hasColumn(self::TABLE, $col))
                        $table->dropForeign([$col]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DB::TABLE_CREATOR
                        . ' on table '
                        . self::TABLE
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
