<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Enums\IndicatorTechnicalLevel;
use App\Traits\{HasNullableAuditColumns, HasRatingColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateIndicatorsTable extends Migration
{
    use HasNullableAuditColumns, HasRatingColumns;
    private const TABLE = DC::TABLE_IND;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addRatingColumns($table, nullableEmployee: true);
            $table->uuid('department')->nullable()->index();
            $table->uuid('designation')->nullable()->index();
            $table->uuid('project')->nullable()->index();
            $table->uuid(DC::COL_CRT_USR)->index();
            $table->enum('level', array_column(IndicatorTechnicalLevel::cases(), 'value'))->default(IndicatorTechnicalLevel::None->value)->nullable()->index(); // ? nullable for testing, enforced with the Enum at model/level
            $table->json('sources')->nullable(); // ? a list of uuids for sources that refer to Source or Appraisals rows querying through their ids, filtered at boot/save with that in consideration
            $table->foreign(DC::COL_CRT_USR)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->cascadeOnDelete();  // ? the user who created the indicator entry is not necessarily the user who made the rating
            foreach (
                [
                    'department'                  => DC::TABLE_DEPARTMENTS,
                    'designation'                 => DC::TABLE_DESIGNS,
                    'project'                     => DC::TABLE_PROJECTS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropRatingColumnForeigns($table, self::TABLE);
            foreach (
                [
                    'department',
                    'designation',
                    'project',
                    DC::COL_CRT_USR,
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
