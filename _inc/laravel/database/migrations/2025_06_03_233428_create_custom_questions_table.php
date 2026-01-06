<?php

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC};
use App\Traits\{DescribesClientField, DescribesHtmlLinkedEntity, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};
// * this class is mostly redundant and all the data comes from a linked CustomField (if not null) and the COL_IR, if null, absorbs the 'required' column from the linked CustomField as well
class CreateCustomQuestionsTable extends Migration
{
    use HasNullableAuditColumns, DescribesClientField, DescribesHtmlLinkedEntity;

    private const TABLE = DC::TABLE_CUSTOM_QUESTIONS;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('question');
            $table->string(DC::COL_IR)->nullable();
            $this->addClientFieldColumns($table, nullableModule: true, enumType: true);
            $this->addHtmlLinkedColumns($table, true);
            $table->uuid(FC::COL_CT_FD_ID)->nullable()->index();
            $table->foreign(FC::COL_CT_FD_ID)
                ->references('id')
                ->on(DC::TABLE_CUSTOM_FIELDS)
                ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            try {
                Schema::hasColumn(self::TABLE, FC::COL_CT_FD_ID)
                    && $table->dropForeign([FC::COL_CT_FD_ID]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . FC::COL_CT_FD_ID
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
