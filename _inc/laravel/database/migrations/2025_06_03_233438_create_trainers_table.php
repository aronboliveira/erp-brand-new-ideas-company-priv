<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTrainersTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_TRAINERS;
    private const B = 'branch';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();           // ! CHANGED
                $table->uuid(self::B);                  // ! CHANGED
                $table->string('firstname');
                $table->string('lastname');
                $table->string('contact');
                $table->string('email');
                $table->text('address')->nullable();
                $table->text('expertise')->nullable();
                $table->uuid(DatabaseConstants::TABLE_CREATOR);              // ! CHANGED
                $table->timestamps();
                foreach ([
                    self::B                             => DatabaseConstants::TABLE_BRANCHES,
                    DatabaseConstants::TABLE_CREATOR    => DatabaseConstants::TABLE_USERS,
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
                self::B,
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
