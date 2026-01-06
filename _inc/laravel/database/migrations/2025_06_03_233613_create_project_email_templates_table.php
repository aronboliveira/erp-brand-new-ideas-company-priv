<?php

use App\Config\Constants\{EmailsConstants as EC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProjectEmailTemplatesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PRJ_EM_TMP;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 254)->nullable()->unique(); // ? unique code with the pattern PRJ-EM-TMP-{UUID}; if null or invalidated, generated automatically on saving with the help of a check of do/while to ensure it's unique
            $table->string('name', 254)->nullable()->index();
            $table->uuid(EC::COL_TMP)->index();
            $table->uuid(PJC::COL_PJ_ID)->index();
            $table->boolean(EC::COL_IA)->default(false);
            $table->json('fonts')->nullable(); // ? names of fonts suggested to the user, with the first one being the default
            $table->json('colors')->nullable(); // ? filtered at saving as a set of hexcodes of colors to be suggested to the user when using this template in the project context
            $table->json('variables')->nullable(); // ? MUST incorporate all the variables from the linked template via EC::COL_TMP, and never alter them
            $table->json('settings')->nullable(); // ? MUST incorporate all the settings from the linked template via EC::COL_TMP, and never alter them
            $table->json('tags')->nullable();
            foreach (
                [
                    EC::COL_TMP                => DC::TABLE_EMAIL_TEMPLATES,
                    PJC::COL_PJ_ID                    => DC::TABLE_PROJECTS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->onDelete('cascade');
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    EC::COL_TMP,
                    PJC::COL_PJ_ID,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to execute down for '
                            . $column
                            . ' foreign key column: '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
