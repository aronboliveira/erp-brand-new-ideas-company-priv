<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\AppModuleType;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateNotesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_NOTES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 1024)->nullable(); // ? title of the note
            $table->text('note')->nullable(); // ? the body of the note, properly sanitized
            $table->enum(AC::COL_MT, array_column(AppModuleType::cases(), 'value'))->default(AppModuleType::Other->value)->index(); // ? automatically set to 'other' on creation if invalidated by normalize + tryFrom
            $table->string(AC::COL_MI)->nullable()->index();
            $table->uuid('document')->nullable()->unique(); // * most of the important data is find here // ? nullable for tests
            $table->foreign('document')
                ->references('id')
                ->on(DC::TABLE_DOCS)
                ->restrictOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            try {
                Schema::hasColumn(self::TABLE, 'document')
                    && $table->dropForeign(['document']);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to execute down for '
                        . 'document'
                        . ' foreign key column: '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
