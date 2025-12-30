<?php

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\CountryName;
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
            $table->string('name')->index();
            $table->uuid(CC::COL_CP_ID)->nullable()->index(); // ? nullable for tests
            $table->string('zip')->index();
            $table->string('country', 64)->default(CountryName::Brazil->value)->nullable(); // ? nullable for tests,  // * constrained at model with CountryName enum either through direct cases or through the keys of the enum, as available in CountryName::normalize, falling back to null if not found
            $table->string('state', 1024)->nullable()->index(); // ? nullable for tests, // * if the country is the code or full name of Brazil, Argentina, Bolivia, Colombia, Chile, China, Ecuador, Guyana, Paraguay, Peru, Portugal, Suriname, United States, Uruguay or Venezuela, then use normalize/tryFrom from the following enums, respectively (if failed, nullify): BrazilState, ArgentinaProvince, BoliviaDepartment, ChileRegion, ChinaState, ColombiaDepartment, EcuadorProvince, GuyanaRegion, ParaguayDepartment, PeruDepartment, PortugalState, SurinameDistrict, UnitedStatesState, UruguayDepartment, VenezuelaState; for other countries, just store the string as is; if 'country' is null, then try to "reverse search" the state in all enums and set the country accordingly, if found; otherwise, leave both as null
            $table->string('city', 1024)->index();
            $table->string('address', 1024);
            $table->unique(['zip', 'name']);
            $table->text(CC::COL_ADR_DTL)->nullable();
            $table->string('notes')->nullable();
            $table->string('phone', 32)->nullable(); // ? nullable for tests
            $table->string('email', 254)->nullable(); // ? nullable for tests
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
