<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateIndicatorsTable extends Migration
{
    private const TABLE = 'indicators';
    private const COL_BRANCH = 'branch';
    private const COL_DEP = 'department';
    private const COL_DESIGN = 'designation';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                // ! CHANGED
            $table->uuid(self::COL_BRANCH)->index();              // ! CHANGED
            $table->uuid(self::COL_DEP)->index();          // ! CHANGED
            $table->uuid(self::COL_DESIGN)->index();         // ! CHANGED
            $table->string('rating')->nullable();
            $table->integer('attendance')->default(0);
            $table->integer('administration')->default(0);
            $table->integer('customer_experience')->default(0);
            $table->integer('integrity')->default(0);
            $table->integer('marketing')->default(0);
            $table->integer('professionalism')->default(0);
            $table->uuid('created_user')->index();        // ! CHANGED
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->index();          // ! CHANGED
            foreach ([
                self::COL_BRANCH                 => DatabaseConstants::TABLE_BRANCHES,
                self::COL_DEP                    => DatabaseConstants::TABLE_DEPARTMENTS,
                self::COL_DESIGN                 => DatabaseConstants::TABLE_DESIGNS,
                DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable) {
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_BRANCH,
                self::COL_DEP,
                self::COL_DESIGN,
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
