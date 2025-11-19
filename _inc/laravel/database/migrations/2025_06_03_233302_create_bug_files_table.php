<?php

use App\Config\Constants\{DatabaseConstants as DC, PermissionsConstants as PC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBugFilesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = 'bug_files';
    private const COL_BUG = 'bug_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('file');
            $table->string('name');
            $table->string('extension');
            $table->string('file_size'); // * Considerar armazenar como bytes numéricos
            $table->uuid(self::COL_BUG);
            $table->string('user_type')->default(PC::CL);
            $table->foreign(self::COL_BUG)
                ->references('id')
                ->on(DC::TABLE_BUGS)
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
                    self::COL_BUG,
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
