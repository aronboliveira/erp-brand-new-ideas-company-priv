<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePipelinesTable extends Migration
{
    private const TABLE_NAME = DC::TABLE_PIPELINES;

    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string(PJC::COL_PPL_NM);
            $table->integer(AC::COL_OD)->default(0);
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            $table->timestamps();
            foreach ([DC::TABLE_UPDATER, DC::TABLE_CREATOR] as $column)
                $table->foreign($column)
                    ->references('id')
                    ->on(DC::TABLE_USERS)
                    ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            foreach ([DC::TABLE_CREATOR, DC::TABLE_UPDATER] as $column) {
                try {
                    Schema::hasColumn(self::TABLE_NAME, $column) &&
                        $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ' on table '
                            . self::TABLE_NAME
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
