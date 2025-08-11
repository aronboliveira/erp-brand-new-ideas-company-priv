<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEmployeesTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_EMPLOYEES;
    private const ACCOUNT = 'account';
    private const BANK = 'bank';
    private const BRANCH = 'branch';
    private const SALARY = 'salary';
    private const COL_USER = 'user_id';
    private const COL_BRANCH = self::BRANCH . '_id';
    private const COL_DEPARTMENT = 'department_id';
    private const COL_DESIGN = 'designation_id';
    private const COL_TAX = 'tax_payer_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();            // ! CHANGED
            $table->uuid(self::COL_USER);                   // ! CHANGED
            $table->string('name')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->uuid('employee_id')->default(DatabaseConstants::DEFAULT_UUID); // ! CHANGED
            $table->uuid(self::COL_BRANCH);                 // ! CHANGED
            $table->string(self::BRANCH . '_location')->nullable();
            $table->uuid(self::COL_DEPARTMENT);             // ! CHANGED
            $table->uuid(self::COL_DESIGN);            // ! CHANGED
            $table->string('company_doj')->nullable();
            $table->string('documents')->nullable();
            $table->string(self::ACCOUNT . '_holder_name')->nullable();
            $table->string(self::ACCOUNT . '_number')->nullable();
            $table->string(self::BANK . '_name')->nullable();
            $table->string(self::BANK . '_identifier_code')->nullable();
            $table->uuid(self::COL_TAX)->nullable();
            $table->uuid(self::SALARY . '_type')->nullable();   // ! CHANGED
            $table->integer(self::SALARY)->nullable();
            $table->integer('is_active')->default(1);
            $table->uuid(DatabaseConstants::TABLE_CREATOR);                // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_USER                           => DatabaseConstants::TABLE_USERS,
                self::COL_BRANCH                => DatabaseConstants::TABLE_BRANCHES,
                self::COL_DEPARTMENT                     => DatabaseConstants::TABLE_DEPARTMENTS,
                self::COL_DESIGN                    => DatabaseConstants::TABLE_DESIGNS,
                self::COL_TAX                      => DatabaseConstants::TABLE_TAXES,
                DatabaseConstants::TABLE_CREATOR    => DatabaseConstants::TABLE_USERS,
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
                self::COL_USER,
                self::COL_BRANCH,
                self::COL_DEPARTMENT,
                self::COL_DESIGN,
                self::COL_TAX,
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
