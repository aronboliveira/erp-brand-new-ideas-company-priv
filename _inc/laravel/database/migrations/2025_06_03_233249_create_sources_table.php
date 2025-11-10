<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateSourcesTable extends Migration
{
    private const TABLE = 'sources';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            $table->foreign(DC::TABLE_CREATOR)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->nullOnDelete();
            $table->foreign(DC::TABLE_UPDATER)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([DC::TABLE_CREATOR, DC::TABLE_UPDATER] as $column) {
                try {
                    if (Schema::hasColumn(self::TABLE, $column))
                        $table->dropForeign([$column]);
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
