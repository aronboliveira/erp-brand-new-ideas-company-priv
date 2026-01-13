<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\AppModuleType;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBasicFavoritesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_BSC_FV;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('module', array_column(AppModuleType::cases(), 'value'))->default(AppModuleType::Other->value)->index();
            $table->string(AC::COL_FV_TB)->default(DC::TABLE_NOTES)->nullable()->index(); // * can refer to the name of the any DC::TABLE_, in DatabaseConstants.php
            $table->uuid(AC::COL_FV_ID)->index(); // * polymorphic relation to the id in the table defined in AC::COL_FV_TB
            $table->uuid(UC::COL_USER_ID)->index();
            $table->text('notes')->nullable();
            $table->foreign(UC::COL_USER_ID)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    'user_id',
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
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
