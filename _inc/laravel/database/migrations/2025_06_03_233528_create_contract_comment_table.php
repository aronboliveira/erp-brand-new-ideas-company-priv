<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateContractCommentTable extends Migration
{
    private const ENTITY = 'contract';
    private const TABLE = self::ENTITY . '_comment';
    private const COL_CONTRACT = self::ENTITY . '_id';
    private const COL_USER    = 'user_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();                              // ! CHANGED
            $table->uuid(self::COL_CONTRACT);                                // ! CHANGED
            $table->uuid(self::COL_USER);                                    // ! CHANGED
            $table->string('comment')->nullable();
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR);                                 // ! CHANGED
            foreach ([
                self::COL_CONTRACT               => DatabaseConstants::TABLE_CONTRACTS,
                self::COL_USER                   => DatabaseConstants::TABLE_USERS,
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
            foreach ([
                self::COL_CONTRACT,
                self::COL_USER,
                DatabaseConstants::TABLE_CREATOR,
            ] as $column) {
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
