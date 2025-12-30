<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateCustomQuestionsTable extends Migration
{
    // todo
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_CUSTOM_QUESTIONS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('question');
            $table->string(DC::COL_IR)->nullable(); // * to be changed to boolean later, but should be constrained into "true", "false", "0", "1", etc.
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
