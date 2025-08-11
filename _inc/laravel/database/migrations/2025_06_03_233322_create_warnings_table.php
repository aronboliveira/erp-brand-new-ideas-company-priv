<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateWarningsTable extends Migration
{
    private const ENTITY = 'warning';
    private const TABLE = self::ENTITY . 's';
    private const COL_EMPLOYEE = 'employee_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->uuid(self::ENTITY . '_to'); // ! CHANGED
            $table->uuid(self::ENTITY . '_by'); // ! CHANGED
            $table->string('subject')->nullable();
            $table->date(self::ENTITY . '_date');
            $table->text('description')->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR); // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE); // ! CHANGED
            $table->timestamps();
            foreach ([
                self::ENTITY . '_to'               => DatabaseConstants::TABLE_EMPLOYEES,
                self::ENTITY . '_by'               => DatabaseConstants::TABLE_EMPLOYEES,
                DatabaseConstants::TABLE_CREATOR    => DatabaseConstants::TABLE_EMPLOYEES,
                self::COL_EMPLOYEE                 => DatabaseConstants::TABLE_EMPLOYEES,
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
                self::ENTITY . '_to',
                self::ENTITY . '_by',
                DatabaseConstants::TABLE_CREATOR,
                self::COL_EMPLOYEE,
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
