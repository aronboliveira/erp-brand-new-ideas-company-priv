<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, PriorityLevel};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateMilestonesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_MSS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(PJC::COL_PJ_ID)->index();
            $table->string('title')->index();
            $table->text('description')->nullable();
            $table->enum('priority', array_column(PriorityLevel::cases(), 'value'))->default(PriorityLevel::Medium->value)->index(); // ? enforced at model level
            $table->enum('status', array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::Pending->value)->index(); // ? enforced at model level
            $table->unsignedDecimal('progress', 5, 2)->default(0.00)->nullable();
            $table->unsignedDouble('cost', 15, 2)->default(0.00);
            $table->date(PJC::COL_S_DT)->nullable(); // ? enforced at boot/save that is >= due_date
            $table->date(PJC::COL_D_DATE)->nullable();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            $table->json('involved')->nullable(); // ? list of user id/names or employee id/names involved in this milestone
            foreach (
                [
                    PJC::COL_PJ_ID => DC::TABLE_PROJECTS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
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
                    PJC::COL_PJ_ID,
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
