<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateResignationsTable extends Migration
{
    private const TABLE = 'resignations';
    private const TABLE_CREATOR = 'created_by';
    private const COL_EMPLOYEE = 'employee_id';
    private const DATE = 'date';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();           // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE);              // ! CHANGED
            $table->date('notice_' . self::DATE);
            $table->date('resignation_' . self::DATE);
            $table->string('description')->nullable();
            $table->uuid(self::TABLE_CREATOR);               // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_EMPLOYEE           => DatabaseConstants::TABLE_EMPLOYEES,
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
            foreach ([self::COL_EMPLOYEE, self::TABLE_CREATOR] as $col) {
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
