<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\Visibility;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

// * the purpose of this table was never made clear in legacy... barely changed
class CreateTrackPhotosTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_TRK_PHT;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string(PJC::COL_TRK_ID)->index();
            $table->uuid(UC::COL_USER_ID)->index();
            $table->string(PJC::COL_IMG_PATH)->nullable()->index();
            $table->string('url')->nullable();
            $table->dateTime('time')->nullable();
            $table->enum('visibility', array_column(Visibility::cases(), 'value'))->default(Visibility::Private->value)->nullable();
            $table->string('status')->nullable();
            $table->foreign(UC::COL_USER_ID)
                ->references('id')
                ->on(DC::TABLE_USERS)
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
                    UC::COL_USER_ID,
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
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
        Schema::dropIfExists(self::TABLE);
    }
}
