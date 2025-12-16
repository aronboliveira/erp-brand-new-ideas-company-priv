<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateLeaveTypesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_LEAVE_TYPES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title')->index();
            $table->unsignedSmallInteger('days')->default(0);
            $table->unsignedSmallInteger(PJC::COL_EXT_DY)->default(0)->nullable(); // ? nullable for tests
            $table->boolean('paid')->default(false)->nullable()->index(); // ? nullable for tests
            $table->boolean(PJC::COL_HLT_RL)->default(true)->nullable()->index(); // ? nullable for tests
            $table->unsignedInteger(PJC::COL_SL_MIN_DD_PCT)->default(0)->nullable(); // ? nullable for tests
            $table->unsignedInteger(PJC::COL_SL_MAX_DD_PCT)->default(0)->nullable(); // ? nullable for tests
            $table->text('description')->nullable();
            $table->json('categories')->nullable();
            $table->json('conditions')->nullable();
            $table->json('attachments')->nullable();
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
