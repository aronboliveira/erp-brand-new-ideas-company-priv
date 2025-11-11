<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema, Log};

class CreateDealFilesTable extends Migration
{
    private const TABLE_NAME = 'deal_files';
    private const FILE = 'file';
    private const COL_DEAL = 'deal_id';
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(self::COL_DEAL)->index();
            $table->string(self::FILE . '_name')->index();
            $table->string(self::FILE . '_path');
            $table->timestamps();
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            $table->foreign(self::COL_DEAL)
                ->references('id')
                ->on(DC::TABLE_DEALS)
                ->cascadeOnDelete();
            foreach (
                [
                    DC::TABLE_CREATOR => DC::TABLE_USERS,
                    DC::TABLE_UPDATER => DC::TABLE_USERS,
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
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_DEAL,
                    DC::TABLE_CREATOR,
                    DC::TABLE_UPDATER
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
