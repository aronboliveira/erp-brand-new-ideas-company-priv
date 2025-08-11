<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateAppraisalsTable extends Migration
{
    private const TABLE = 'appraisals';
    private const COL_BRANCH = 'branch';
    private const COL_EMPLOYEE = 'employee';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->uuid(self::COL_BRANCH); // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE); // ! CHANGED
            $table->string('rating')->nullable();
            $table->string('appraisal_date')->nullable(); // ! ALERT: consider using date type
            $table->integer('customer_experience')->default(0);
            $table->integer('marketing')->default(0);
            $table->integer('administration')->default(0);
            $table->integer('professionalism')->default(0);
            $table->integer('integrity')->default(0);
            $table->integer('attendance')->default(0);
            $table->text('remark')->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)
                ->default(DatabaseConstants::DEFAULT_UUID);
            $table->timestamps();
            foreach ([
                self::COL_BRANCH                 => DatabaseConstants::TABLE_BRANCHES,
                self::COL_EMPLOYEE               => DatabaseConstants::TABLE_EMPLOYEES,
                DatabaseConstants::TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
            ] as $col => $tbl)
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
                foreach ([
                    self::COL_BRANCH,
                    self::COL_EMPLOYEE,
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
        } catch (\Exception $e) {
            Log::warning(
                'One or more foreign keys on `' . self::TABLE . '` did not exist: '
                    . $e->getMessage()
            );
        }
        Schema::dropIfExists(self::TABLE);
    }
}
