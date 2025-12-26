<?php

use App\Config\Constants\DatabaseConstants as DC;
use App\Traits\{HasFileColumns, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateDocumentsTable extends Migration
{
    use HasNullableAuditColumns, HasFileColumns;

    private const TABLE = DC::TABLE_DOCS;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('number')->index()->nullable();
                $table->string(DC::COL_IR)->default('false'); // todo must be changed to bool later
                $table->boolean(DC::COL_IPV)->default(false)->nullable();
                $this->addFileColumns($table);
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
