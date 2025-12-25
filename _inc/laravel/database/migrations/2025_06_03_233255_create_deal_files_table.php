<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Traits\{HasFileColumns, HasNullableAuditColumns};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

class CreateDealFilesTable extends Migration
{
    use HasNullableAuditColumns, HasFileColumns;

    private const TABLE = DC::TABLE_DL_FL;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(AC::COL_DL)->index();
            $table->string(DC::COL_FL_NM, 1024)->nullable()->index();
            $this->addFileColumns($table);
            $table->foreign(AC::COL_DL)
                ->references('id')
                ->on(DC::TABLE_DEALS)
                ->restrictOnDelete();
            $table->softDeletes();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                $this->dropAuditColumnForeigns($table, self::TABLE);
            } catch (\Throwable $e) {
                Log::warning('Failed to drop audit foreigns on ' . self::TABLE . ': ' . $e->getMessage());
            }

            foreach ([AC::COL_DL] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col) && $table->dropForeign([$col]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to drop foreign key for ' . $col . ': ' . $e->getMessage());
                }
            }
        });

        Schema::dropIfExists(self::TABLE);
    }
}
