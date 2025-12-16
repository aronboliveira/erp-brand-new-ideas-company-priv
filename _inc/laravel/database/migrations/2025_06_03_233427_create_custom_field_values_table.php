<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateCustomFieldValuesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_CFV;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(DC::COL_RCD_ID)->index();
            $table->uuid(DC::COL_FLD_ID)->index();
            $table->string('value')->nullable();
            $table->boolean('checked')->default(false)->nullable(); // * nullable for testing, enforced as boolean in boot/save, only for checkbox/radio type, else it's forced as null
            $table->unique([DC::COL_RCD_ID, DC::COL_FLD_ID]);
            $table->foreign(DC::COL_FLD_ID)
                ->references('id')
                ->on(DC::TABLE_CUSTOM_FIELDS)
                ->onDelete('cascade');
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            try {
                Schema::hasColumn(self::TABLE, DC::COL_FLD_ID)
                    && $table->dropForeign([DC::COL_FLD_ID]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DC::COL_FLD_ID
                        . ' on table '
                        . self::TABLE
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
