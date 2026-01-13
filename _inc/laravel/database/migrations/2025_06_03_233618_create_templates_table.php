<?php

use App\Config\Constants\{DatabaseConstants as DC, TemplatesConstants as TC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// todo it's not clear what this is yet, but looks like it's related to AI models
class CreateTemplatesTable extends Migration
{
    use HasNullableAuditColumns;
    private const ENTITY = 'template';
    private const TABLE = DC::TABLE_TMP;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(TC::COL_TMP_NM)->index();
            $table->text('prompt');
            $table->string('module');
            $table->text(TC::COL_FD_JSON);
            $table->integer(TC::COL_IS_TN);
            $this->addNullableAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
