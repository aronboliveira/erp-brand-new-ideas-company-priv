<?php

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema, Log};

class CreateDealFilesTable extends Migration
{
    use HasNullableAuditColumns;
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
            $table->foreign(self::COL_DEAL)
                ->references('id')
                ->on(DC::TABLE_DEALS)
                ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE_NAME);
            foreach (
                [
                    self::COL_DEAL,
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
