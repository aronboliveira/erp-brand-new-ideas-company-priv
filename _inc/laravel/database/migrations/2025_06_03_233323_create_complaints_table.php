<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateComplaintsTable extends Migration
{
    private const ENTITY = 'complaint';
    private const TABLE = self::ENTITY . 's';
    private const COL_EMPLOYEE = 'employee_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE);    // ! CHANGED
            $table->integer(self::ENTITY . '_from');
            $table->integer(self::ENTITY . '_against');
            $table->string('title');
            $table->date(self::ENTITY . '_date');
            $table->string('description')->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR);
            $table->timestamps();
            foreach ([
                self::COL_EMPLOYEE                     => DatabaseConstants::TABLE_EMPLOYEES,
                DatabaseConstants::TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
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
                DatabaseConstants::TABLE_CREATOR,
            ] as $column) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
