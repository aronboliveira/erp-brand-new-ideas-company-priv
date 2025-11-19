<?php

use App\Config\Constants\{DatabaseConstants as DC, TemplatesConstants as TC, UsersConstants as UC};
use App\Traits\HasDocumentColumns;
use Illuminate\Support\{Facades\Log, Facades\Schema};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

class CreateEmployeeDocumentsTable extends Migration
{
    private const TABLE_NAME = DC::TABLE_EDOCS;
    use HasDocumentColumns;
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->uuid(UC::COL_EMP_ID)->nullable();
            $table->uuid(TC::COL_DC_ID);
            $table->text(TC::COL_DC_V);
            $this->addDocumentColumns($table);
            foreach (
                [
                    UC::COL_EMP_ID          => DC::TABLE_EMPLOYEES,
                    TC::COL_DC_ID           => DC::TABLE_DOCS,
                ] as $column => $referencedTable
            ) {
                $onDelete = ($column === TC::COL_DC_ID) ? 'cascade' : 'set null';
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->onDelete($onDelete);
            }
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE_NAME);
            foreach (
                [
                    UC::COL_EMP_ID,
                    TC::COL_DC_ID,
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE_NAME, $col)
                        && $table->dropForeign([$col]);
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

        Schema::dropIfExists(self::TABLE_NAME);
    }
}
