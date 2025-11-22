<?php

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{HasDocumentColumns, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDocumentsTable extends Migration
{
    use HasNullableAuditColumns, HasDocumentColumns;

    private const TABLE = DC::TABLE_DOCS;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('is_required')->default('false');
                $table->boolean('is_private')->default(false)->nullable();
                $this->addDocumentColumns($table);
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
