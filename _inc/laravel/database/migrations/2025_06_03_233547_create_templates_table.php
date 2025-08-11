<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTemplatesTable extends Migration
{
    private const ENTITY = 'template';
    private const TABLE = self::ENTITY . 's';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->string(self::ENTITY . '_name');
            $table->text('prompt');
            $table->string('module');
            $table->text('field_json');
            $table->integer('is_tone');
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            $table->foreign(DatabaseConstants::TABLE_CREATOR)
                ->references('id')
                ->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, DatabaseConstants::TABLE_CREATOR)
                    && $table->dropForeign([DatabaseConstants::TABLE_CREATOR]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DatabaseConstants::TABLE_CREATOR
                        . ' on table '
                        . self::TABLE
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
