<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateAssetsTable extends Migration
{
    private const TABLE = 'assets';
    private const DATE = 'date';
    private const COL_EMPLOYEE = 'employee_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();                    // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE)->nullable();           // * stores comma-separated UUIDs
            $table->string('name');
            $table->date('purchase_' . self::DATE);
            $table->date('supported_' . self::DATE);
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR);                        // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_EMPLOYEE                     => DatabaseConstants::TABLE_EMPLOYEES,
                DatabaseConstants::TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
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
