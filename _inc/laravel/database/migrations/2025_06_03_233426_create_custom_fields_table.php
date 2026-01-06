<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\{DescribesClientField, DescribesHtmlLinkedEntity, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateCustomFieldsTable extends Migration
{
    use DescribesClientField, DescribesHtmlLinkedEntity, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_CUSTOM_FIELDS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addClientFieldColumns($table, nullableModule: true, enumType: true);
            $this->addHtmlLinkedColumns($table, true);
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
