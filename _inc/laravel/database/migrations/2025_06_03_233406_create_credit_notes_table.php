<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{HasNullableAuditColumns, IsCardNote};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateCreditNotesTable extends Migration
{
    use HasNullableAuditColumns, IsCardNote;
    private const TABLE = DC::TABLE_CR_NOTES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addCardNoteColumns($table);
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropCardNoteColumnForeigns($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    BC::COL_BL_ID
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
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
