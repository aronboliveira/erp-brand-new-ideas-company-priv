<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBugStatusesTable extends Migration
{
    // ! Se AC::COL_TSK_STT na tabela de bugs for string/código e BugStatus.id for UUID, há desencontro de tipos/semântica.
    use HasNullableAuditColumns;
    private const TABLE = 'bug_statuses';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer(AC::COL_OD)->default(0);
            $table->string(AC::COL_TT);
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
