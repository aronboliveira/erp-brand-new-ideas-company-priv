<?php

use App\Config\Constants\{DatabaseConstants as DC, TemplatesConstants as TC, UsersConstants as UC};
use App\Traits\{EmployeeConnected, HasDocumentColumns, HasNullableAuditColumns};
use Illuminate\Support\{Facades\Log, Facades\Schema};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

class CreateEmployeeDocumentsTable extends Migration
{
    use EmployeeConnected, HasNullableAuditColumns, HasDocumentColumns;
    private const TABLE_NAME = DC::TABLE_EDOCS;
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $this->addEmployeeColumns($table);
            $table->uuid(TC::COL_DC_ID);
            $table->text(TC::COL_DC_V);
            $this->addDocumentColumns($table);
            $table->foreign(TC::COL_DC_ID)
                ->references('id')
                ->on(DC::TABLE_DOCS)
                ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE_NAME);
            $this->dropEmployeeColumnForeign($table, self::TABLE_NAME);
            foreach (
                [
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
