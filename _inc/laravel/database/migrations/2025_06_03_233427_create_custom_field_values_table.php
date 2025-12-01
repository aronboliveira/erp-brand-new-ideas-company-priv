<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateCustomFieldValuesTable extends Migration
{
    private const TABLE = 'custom_field_values';
    private const COL_RECORD = 'record_id';
    private const COL_FIELD = 'field_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                    // ! CHANGED
            $table->uuid(self::COL_RECORD)->index();                // ! CHANGED
            $table->uuid(self::COL_FIELD)->index();                 // ! CHANGED
            $table->string('value')->nullable();
            $table->timestamps();
            $table->unique([self::COL_RECORD, self::COL_FIELD]);
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
            $table->foreign(self::COL_FIELD)
                ->references('id')
                ->on(DatabaseConstants::TABLE_CUSTOM_FIELDS)
                ->onDelete('cascade'); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, self::COL_FIELD)
                    && $table->dropForeign([self::COL_FIELD]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . self::COL_FIELD
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
