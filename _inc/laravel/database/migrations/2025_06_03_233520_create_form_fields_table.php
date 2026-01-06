
<?php

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC};
use App\Traits\{DescribesClientField, DescribesHtmlLinkedEntity, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateFormFieldsTable extends Migration
{
    use DescribesClientField, DescribesHtmlLinkedEntity, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_FM_FD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 254)->nullable();
            $table->uuid(FC::COL_CT_QT_ID)->nullable()->index(); // ? client-field and html-linked columns, if null or invalid, are fetched from the linked DC::TABLE_CUSTOM_QUESTIONS row (if not null and existing in the Table), then from its FC::COL_CT_FD_ID (checking if it's not null and the id exists in DC::TABLE_CUSTOM_FIELDS). The source of truth, however, for NOT NULL && VALID columns is always this table (BOTH for this table columns and the linked question's columns, which should be updated dynamically on any update of any of the three tables). The usage of cache, $with and $appends should be helpful here.
            $this->addClientFieldColumns($table, nullableModule: true, enumType: true);
            $this->addHtmlLinkedColumns($table, true);
            $table->uuid(FC::COL_FM_ID)->index();
            $table->foreign(FC::COL_FM_ID)
                ->references('id')
                ->on(DC::TABLE_FORM_BUILD)
                ->cascadeOnDelete();
            $table->foreign(FC::COL_CT_QT_ID)
                ->references('id')
                ->on(DC::TABLE_CUSTOM_QUESTIONS)
                ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    FC::COL_FM_ID,
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
