<?php

use App\Config\Constants\{DatabaseConstants as DC, EmailsConstants as EC};
use App\Traits\{HasNullableAuditColumns, IsTemplateLang};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEmailTemplateLangsTable extends Migration
{
    use HasNullableAuditColumns, IsTemplateLang;
    private const TABLE = DC::TABLE_EML_TMP_LG;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(EC::COL_PRT_ID)->index();
            $table->string('subject');
            $this->addTemplateLangColumns($table);
            $table->foreign(EC::COL_PRT_ID)
                ->references('id')
                ->on(DC::TABLE_EMAIL_TEMPLATES)
                ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropTemplateLangColumnForeigns($table, self::TABLE);
            try {
                Schema::hasColumn(self::TABLE, EC::COL_PRT_ID)
                    && $table->dropForeign([EC::COL_PRT_ID]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . EC::COL_PRT_ID
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
