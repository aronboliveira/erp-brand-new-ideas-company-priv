<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

// todo checkgpt
class CreateTrainingsTable extends Migration
{
    private const ENTITY = 'training';
    private const TABLE = self::ENTITY . 's';
    private const TYPE = self::ENTITY . '_type';
    private const T = 'trainer';
    private const D = 'date';
    private const B = 'branch';
    private const E = 'employee';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();             // ! CHANGED
            $table->uuid(self::B);                    // ! CHANGED
            $table->uuid(self::T);                   // ! CHANGED
            $table->integer(self::T . '_option');
            $table->uuid(self::TYPE);             // ! CHANGED
            $table->float(self::ENTITY . '_cost')->default(0.00);
            $table->uuid(self::E);                  // ! CHANGED
            $table->date('start_' . self::D);
            $table->date('end_' . self::D);
            $table->text('description')->nullable();
            $table->integer('performance')->default(0);
            $table->integer('status')->default(0);
            $table->text('remarks')->nullable();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR);                // ! CHANGED
            $table->timestamps();
            foreach (
                [
                    self::B                       => DatabaseConstants::TABLE_BRANCHES,
                    self::TYPE                    => DatabaseConstants::TABLE_TRAINING_TYPES,
                    self::T                       => DatabaseConstants::TABLE_TRAINERS,
                    self::E                       => DatabaseConstants::TABLE_EMPLOYEES,
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
        try {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                foreach (
                    [
                        self::B,
                        self::TYPE,
                        self::T,
                        self::E,
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
        } catch (\Exception $e) {
            Log::warning(
                'One or more foreign keys on `' . self::TABLE . '` did not exist: '
                    . $e->getMessage()
            );
        }
        Schema::dropIfExists(self::TABLE);
    }
}
