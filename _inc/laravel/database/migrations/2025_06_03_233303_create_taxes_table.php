<?php

use App\Config\Constants\DatabaseConstants as DB;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTaxesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DB::TABLE_TAXES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->decimal('rate', 5, 2)->default(0.00);
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
