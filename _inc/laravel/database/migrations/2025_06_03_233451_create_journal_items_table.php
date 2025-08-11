<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJournalItemsTable extends Migration
{
    private const TABLE      = 'journal_items';
    private const COL_ACCOUNT = 'account';
    private const COL_JOURNAL = 'journal';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();              // ! CHANGED
            $table->uuid(self::COL_JOURNAL);            // ! CHANGED
            $table->uuid(self::COL_ACCOUNT);            // ! CHANGED
            $table->text('description')->nullable();
            $table->float('debit', 15, 2)->default(0.00);
            $table->float('credit', 15, 2)->default(0.00);
            $table->timestamps();
            foreach ([
                self::COL_JOURNAL => DatabaseConstants::TABLE_JOURNAL_ENTRIES,
                self::COL_ACCOUNT => DatabaseConstants::TABLE_COAS,
            ] as $col => $tbl)
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->cascadeOnDelete(); // * ADDED
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_JOURNAL,
                self::COL_ACCOUNT,
            ] as $col) {
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
