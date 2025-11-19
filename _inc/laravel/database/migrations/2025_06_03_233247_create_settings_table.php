<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateSettingsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE_NAME = DC::TABLE_SETTINGS;
    private const COL_NAME = 'name';
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(self::COL_NAME)->nullable();
            $table->text('value')->nullable();
            $table->unique([self::COL_NAME, DC::TABLE_CREATOR]);
            $table->uuid(UC::COL_USER_ID)->nullable();
            $table->foreign(UC::COL_USER_ID)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->cascadeOnDelete();
            $table->unique(
                [self::COL_NAME, DC::TABLE_CREATOR, UC::COL_USER_ID],
                self::TABLE_NAME
                    . '_' . self::COL_NAME
                    . '_' . DC::TABLE_CREATOR
                    . '_' . UC::COL_USER_ID . '_unique'
            );
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE_NAME);
            try {
                Schema::hasColumn(self::TABLE_NAME, UC::COL_USER_ID) &&
                    $table->dropForeign([UC::COL_USER_ID]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . UC::COL_USER_ID
                        . ' on table '
                        . self::TABLE_NAME
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
