<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEstimationsTable extends Migration
{
    private const TABLE = 'estimations';
    private const COL_CLIENT = 'client_id';
    private const COL_TAX = 'tax_id';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                      // ! CHANGED
                $table->uuid(self::COL_CLIENT);                          // ! CHANGED
                $table->uuid(self::COL_TAX);                             // ! CHANGED
                $table->uuid('estimation_id');                      // ! CHANGED
                $table->string('status');
                $table->date('issue_date');
                $table->float('discount');
                $table->text('terms')->nullable();
                $table->uuid(DatabaseConstants::TABLE_CREATOR);                         // ! CHANGED
                $table->timestamps();
                foreach ([
                    self::COL_CLIENT              => DatabaseConstants::TABLE_USERS,
                    self::COL_TAX                 => DatabaseConstants::TABLE_TAXES,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
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
                self::COL_CLIENT,
                self::COL_TAX,
                DatabaseConstants::TABLE_CREATOR,
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
