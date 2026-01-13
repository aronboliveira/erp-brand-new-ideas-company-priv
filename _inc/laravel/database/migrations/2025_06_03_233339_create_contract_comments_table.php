<?php

use App\Config\Constants\{
    DatabaseConstants as DC,
    UsersConstants as UC,
    ProjectsConstants as PC
};
use App\Enums\UserType;
use App\Traits\{HasCommentColumns, HasNullableAuditColumns};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

class CreateContractCommentsTable extends Migration
{
    use HasNullableAuditColumns, HasCommentColumns;
    private const TABLE = DC::TABLE_CTC_CMT;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid(PC::COL_CTC_ID)->index();
                $this->addCommentColumns(
                    $table,
                    unnullify: [UC::COL_USER_ID, UC::COL_U_TP],
                    userTypeValues: array_column(UserType::cases(), 'value'),
                    defaultUserType: UserType::Client
                ); // ? the attachments here can refer to not only a id of a row in DC::TABLE_DOCS, a safe url/file_path (according to FiltersSecureAttachments) but also to a id of a row in DC::TABLE_CTC_ATC
                $table->foreign(PC::COL_CTC_ID)
                    ->references('id')
                    ->on(DC::TABLE_CONTRACTS)
                    ->cascadeOnDelete();
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropCommentColumnForeigns($table, self::TABLE);
            foreach ([PC::COL_CTC_ID] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
                            . ' on table '
                            . self::TABLE
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });

        Schema::dropIfExists(self::TABLE);
    }
}
