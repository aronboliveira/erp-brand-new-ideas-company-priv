<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateContractNotesTable extends Migration
{
    private const ENTITY = 'contract';
    private const TABLE = self::ENTITY . '_notes';
    private const COL_CONTRACT = self::ENTITY . '_id';
    private const COL_USER = 'user_id';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                     // ! CHANGED
                $table->uuid(self::COL_CONTRACT);                        // ! CHANGED
                $table->uuid(self::COL_USER);                            // ! CHANGED
                $table->string('notes')->nullable();
                $table->uuid(DatabaseConstants::COL_TABLE_CREATOR);                         // ! CHANGED
                $table->timestamps();
                foreach (
                    [
                        self::COL_CONTRACT                    => DatabaseConstants::TABLE_CONTRACTS,
                        self::COL_USER                        => DatabaseConstants::TABLE_USERS,
                        DatabaseConstants::COL_TABLE_CREATOR      => DatabaseConstants::TABLE_USERS,
                    ] as $column => $referencedTable
                )
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->onDelete('cascade');
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_CONTRACT,
                    self::COL_USER,
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
