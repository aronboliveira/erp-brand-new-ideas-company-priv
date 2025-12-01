<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateSupportRepliesTable extends Migration
{
    private const TABLE = 'support_replies';
    private const COL_SUP = 'support_id';
    private const COL_USER = 'user';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                  // ! CHANGED
                $table->uuid(self::COL_SUP);                     // ! CHANGED
                $table->uuid(self::COL_USER);                           // ! CHANGED
                $table->text('description')->nullable();
                $table->uuid(DatabaseConstants::COL_TABLE_CREATOR);                     // ! CHANGED
                $table->boolean('is_read')->default(false);     // ! CHANGED
                $table->timestamps();
                foreach (
                    [
                        self::COL_SUP                     => DatabaseConstants::TABLE_SUPPORTS,
                        self::COL_USER                    => DatabaseConstants::TABLE_USERS,
                        DatabaseConstants::COL_TABLE_CREATOR   => DatabaseConstants::TABLE_USERS,
                    ] as $col => $tbl
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->cascadeOnDelete(); // * ADDED
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_SUP,
                    self::COL_USER,
                    DatabaseConstants::COL_TABLE_CREATOR,
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
