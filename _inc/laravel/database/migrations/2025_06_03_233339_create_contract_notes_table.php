<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateContractNotesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_CTC_NTS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->nullable()->unique(); // ? evaluated and, if not following pattern, generated at boot/save as CTC-NTS-{UUID}, checking uniqueness with do/while // * nullable for tests
                $table->uuid(PJC::COL_CTC_ID)->index();
                $table->uuid(UC::COL_USER_ID)->nullable()->index();
                $table->string('notes')->nullable(); // ? if positive for Utility::looksLikeUuid, then query into DC::TABLE_NOTES, DC::TABLE_DOCS and DC::TABLE_CTC_ATC to find the note content, otherwise store directly
                $table->foreign(PJC::COL_CTC_ID)
                    ->references('id')
                    ->on(DC::TABLE_CONTRACTS)
                    ->cascadeOnDelete();
                $table->foreign(UC::COL_USER_ID)
                    ->references('id')
                    ->on(DC::TABLE_USERS)
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
                    PJC::COL_CTC_ID,
                    UC::COL_USER_ID,
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
