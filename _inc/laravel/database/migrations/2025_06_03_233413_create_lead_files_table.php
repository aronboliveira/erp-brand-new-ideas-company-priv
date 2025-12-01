<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLeadFilesTable extends Migration
{
    private const TABLE = 'lead_files';
    private const COL_LEAD = 'lead_id';
    private const FL = 'file';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();       // ! CHANGED
            $table->uuid(self::COL_LEAD);             // ! CHANGED
            $table->string(self::FL . '_name');
            $table->string(self::FL . '_path');
            $table->timestamps();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
            foreach (
                [
                    self::COL_LEAD                   => DatabaseConstants::TABLE_LEADS,
                    DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
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
            foreach (
                [
                    self::COL_LEAD,
                    DatabaseConstants::COL_TABLE_CREATOR,
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
