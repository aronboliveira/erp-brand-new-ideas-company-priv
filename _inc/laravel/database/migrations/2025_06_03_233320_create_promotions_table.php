<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePromotionsTable extends Migration
{
    private const ENTITY = 'promotion';
    private const TABLE = self::ENTITY . 's';
    private const COL_DESIGN = 'designation_id';
    private const COL_EMPLOYEE = 'employee_id';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();               // ! CHANGED
                $table->uuid(self::COL_EMPLOYEE);                  // ! CHANGED
                $table->uuid(self::COL_DESIGN);               // ! CHANGED
                $table->string(self::ENTITY . '_title')->nullable();
                $table->date(self::ENTITY . '_date');
                $table->string('description')->nullable();
                $table->uuid(DatabaseConstants::TABLE_CREATOR);                   // ! CHANGED
                $table->timestamps();
                foreach ([
                    self::COL_EMPLOYEE               => DatabaseConstants::TABLE_EMPLOYEES,
                    self::COL_DESIGN                 => DatabaseConstants::TABLE_DESIGNS,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
                ] as $column => $referencedTable)
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->onDelete('cascade');
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_EMPLOYEE,
                self::COL_DESIGN,
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
