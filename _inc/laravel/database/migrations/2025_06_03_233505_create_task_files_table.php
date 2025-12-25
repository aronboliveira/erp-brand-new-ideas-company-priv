<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\UserType;
use App\Traits\{HasFileColumns, HasNullableAuditColumns, TaskConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;

class CreateTaskFilesTable extends Migration
{
    use HasNullableAuditColumns, HasFileColumns, TaskConnected;
    private const TABLE = DC::TABLE_TSK_FL;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('file')->nullable()->index(); // * redundant column, but keeping it for legacy compatibility
                $this->addFileColumns($table);
                $this->addTaskColumns($table, false, false, true);
                $table->enum(UC::COL_U_TP, UserType::values())->default(UserType::Customer->value)->index(); // ? enforced at boot/saving
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropTaskColumnForeign($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
