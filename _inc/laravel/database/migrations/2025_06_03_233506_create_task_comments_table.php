<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\UserType;
use App\Traits\{HasCommentColumns, HasNullableAuditColumns, TaskConnected};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskCommentsTable extends Migration
{
    use HasNullableAuditColumns, TaskConnected, HasCommentColumns;

    private const TABLE = DC::TABLE_TSK_CMT;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addCommentColumns(
                $table,
                unnullify: [UC::COL_USER_ID, UC::COL_U_TP],
                userTypeValues: array_column(UserType::cases(), 'value'),
            );
            $this->addTaskColumns($table, unique: false, nullable: false, cascade: true);
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropTaskColumnForeign($table, self::TABLE);
            $this->dropCommentColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
