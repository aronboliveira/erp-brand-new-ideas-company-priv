<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLeadCallsTable extends Migration
{
    private const TABLE = 'lead_calls';
    private const COL_LEAD = 'lead_id';
    private const COL_USER = 'user_id';
    private const C = 'call';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();             // ! CHANGED
            $table->uuid(self::COL_LEAD);                    // ! CHANGED
            $table->string('subject');
            $table->string(self::C . '_type', 30);
            $table->string('duration', 20);
            $table->uuid(self::COL_USER);                    // ! CHANGED
            $table->text('description')->nullable();
            $table->text(self::C . '_result')->nullable();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
            $table->timestamps();
            foreach (
                [
                    self::COL_LEAD                 => DatabaseConstants::TABLE_LEADS,
                    self::COL_USER                      => DatabaseConstants::TABLE_USERS,
                    DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
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
                    self::COL_LEAD,
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
