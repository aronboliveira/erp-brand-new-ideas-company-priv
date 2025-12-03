<?php

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateWarehousesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_WRH;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->nullable(); // ? nullable for tests
            $table->string('name')->unique();
            $table->uuid(CC::COL_CP_ID)->index()->nullable(); // ? nullable for tests
            $table->string('zip')->index();
            $table->string('country', 256)->nullable(); // ? nullable for tests
            $table->string('state', 256)->index()->nullable(); // ? nullable for tests
            $table->string('city', 256)->index();
            $table->text('address');
            $table->text(CC::COL_ADR_DTL)->nullable();
            $table->string('notes')->nullable();
            $table->string('phone', 32)->nullable(); // ? nullable for tests
            $table->string('email')->nullable(); // ? nullable for tests
            $table->uuid(CC::COL_OWN_ID)->nullable(); // * not every warehouse owner should be registered
            $table->string(CC::COL_OWN_NM)->nullable(); // ? nullable for tests, because the owner should be at least a company or an employee or the company
            $table->boolean(CC::COL_IA)->default(true)->nullable(); // ? nullable for tests
            $table->boolean(CC::COL_IS_SHP)->default(true)->nullable(); // ? nullable for tests
            $table->date(CC::COL_FD_DT)->nullable(); // ? nullable for tests
            $table->json('dimensions')->nullable(); // ? nullable for tests, defining warehouse dimensions
            $table->json('capacity')->nullable(); // ? nullable for tests, defining capacity per type of products
            $table->json('employees')->nullable(); // ? nullable for tests
            $table->json('supervisors')->nullable(); // ? nullable for tests
            $table->json('managers')->nullable(); // ? nullable for tests
            $table->json('partners')->nullable();
            $table->json('sections')->nullable();
            $table->json(CC::COL_REACH)->nullable(); // ? nullable for tests, representing the limits of the warehouse reach
            $table->time(CC::COL_OP_TM)->default('08:00:00')->nullable(); // ? nullable for tests
            $table->time(CC::COL_CL_TM)->default('18:00:00')->nullable(); // ? nullable for tests
            $table->json(CC::COL_WK_DYS)->nullable(); // * booted and saving should compare to a set of valid weekdays as a Enum for each day
            $table->unsignedDecimal(UC::COL_AVG_RT, 3, 2)->default(5.00)->nullable(); // ? nullable for tests, average rating of the warehouse by employees/partners
            foreach (
                [
                    CC::COL_CP_ID => DC::TABLE_USERS,
                    CC::COL_OWN_ID => DC::TABLE_USERS,
                ] as $col => $referencedTable
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    CC::COL_CP_ID,
                    CC::COL_OWN_ID,
                ] as $col
            ) Schema::hasColumn(self::TABLE, $col) &&
                $table->dropForeign([$col]);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
