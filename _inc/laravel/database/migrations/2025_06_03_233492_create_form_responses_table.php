<?php

use App\Config\Constants\{DatabaseConstants as DC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateFormResponsesTable extends Migration
{
    // todo
    private const TABLE = DC::TABLE_FORM_RSP;
    private const COL_FORM = 'form_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();               // ! CHANGED
            $table->uuid(self::COL_FORM)->index();             // ! CHANGED
            $table->text('response')->nullable();
            $table->timestamps();
            $table->uuid(DC::COL_TABLE_CREATOR)->nullable();
            foreach (
                [
                    self::COL_FORM    => DC::TABLE_FORM_BUILD,
                    DC::COL_TABLE_CREATOR => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([self::COL_FORM, DC::COL_TABLE_CREATOR] as $column) {
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
