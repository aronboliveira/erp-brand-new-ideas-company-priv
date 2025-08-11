<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateFormResponsesTable extends Migration
{
    private const TABLE = 'form_responses';
    private const COL_FORM = 'form_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();               // ! CHANGED
            $table->uuid(self::COL_FORM)->index();             // ! CHANGED
            $table->text('response')->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            foreach ([
                self::COL_FORM    => DatabaseConstants::TABLE_FORM_BUILD,
                DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([self::COL_FORM, DatabaseConstants::TABLE_CREATOR] as $column) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for ' . $column
                            . ' on table ' . self::TABLE . ': ' . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
