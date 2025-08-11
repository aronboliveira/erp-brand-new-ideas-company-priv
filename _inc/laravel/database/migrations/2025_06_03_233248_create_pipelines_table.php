<?php

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    ProjectsConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePipelinesTable extends Migration
{
    private const TABLE_NAME = DatabaseConstants::TABLE_PIPELINES;

    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table): void {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->string(ProjectsConstants::COL_PPL_NM);
            $table->integer(ActivitiesConstants::COL_OD)->default(0);
            $table->uuid(DatabaseConstants::TABLE_CREATOR); // ! CHANGED
            $table->timestamps();
            $table->foreign(DatabaseConstants::TABLE_CREATOR)
                ->references('id')
                ->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE_NAME, DatabaseConstants::TABLE_CREATOR) &&
                    $table->dropForeign([DatabaseConstants::TABLE_CREATOR]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DatabaseConstants::TABLE_CREATOR
                        . ' on table '
                        . self::TABLE_NAME
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
