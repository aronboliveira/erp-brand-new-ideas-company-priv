<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\{HasNfeColumns, HasNullableAuditColumns, IsCardNote};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateCreditNotesTable extends Migration
{
    use HasNfeColumns, HasNullableAuditColumns, IsCardNote;
    private const TABLE = DC::TABLE_CR_NOTES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addCardNoteColumns($table);
            $this->addNfeColumns($table);
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropCardNoteColumnForeigns($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
