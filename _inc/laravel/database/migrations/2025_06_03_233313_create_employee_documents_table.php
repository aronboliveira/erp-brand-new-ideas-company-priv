<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Support\{Facades\Log, Facades\Schema, Str};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

class CreateEmployeeDocumentsTable extends Migration
{
    private const TABLE_NAME = 'employee_documents';
    private const COL_EMPLOYEE = 'employee_id';
    public function up(): void
    {
        $docs_singular = Str::singular(DatabaseConstants::TABLE_DOCS);
        Schema::create(self::TABLE_NAME, function (Blueprint $table) use ($docs_singular) {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->uuid(self::COL_EMPLOYEE); // ! CHANGED
            $table->uuid($docs_singular . '_id'); // ! CHANGED
            $table->string($docs_singular . '_value');
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            $table->timestamps();
            foreach ([
                self::COL_EMPLOYEE                     => DatabaseConstants::TABLE_EMPLOYEES,
                $docs_singular . '_id'                => DatabaseConstants::TABLE_DOCS,
                DatabaseConstants::TABLE_CREATOR       => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable) {
                $onDelete = ($column === DatabaseConstants::TABLE_CREATOR)
                    ? 'set null'
                    : 'cascade';
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->onDelete($onDelete);
            }
        });
    }

    public function down(): void
    {
        $docs_singular = Str::singular(DatabaseConstants::TABLE_DOCS);
        Schema::table(self::TABLE_NAME, function (Blueprint $table) use ($docs_singular): void {
            foreach ([
                self::COL_EMPLOYEE,
                $docs_singular . '_id',
                DatabaseConstants::TABLE_CREATOR,
            ] as $col) {
                try {
                    Schema::hasColumn(self::TABLE_NAME, $col)
                        && $table->dropForeign([$col]);
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

        Schema::dropIfExists(self::TABLE_NAME);
    }
}
