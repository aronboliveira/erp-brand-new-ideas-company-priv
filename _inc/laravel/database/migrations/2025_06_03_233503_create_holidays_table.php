<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EventObservance, HolidayType};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateHolidaysTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_HLD;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('code')->nullable()->unique(); // ? a secondary code, for querying derictly
                $table->string('name', 254)->nullable()->unique(); // * if null, at model level it is assigned as "Holiday-{date}-{PJC::COL_E_EDT}"
                $table->date('date');
                $table->date(PJC::COL_E_DT);
                $table->text('occasion')->nullable(); // ? the description or reason for the holiday
                $table->enum('type', array_column(HolidayType::cases(), 'value'))->default(HolidayType::National->value);
                $table->enum('observance', array_column(EventObservance::cases(), 'value'))->default(EventObservance::Optional->value);
                $table->boolean('recurring')->default(false)->nullable(); // ? nullable for tests, enforced at boot as boolean
                $table->uuid('event')->nullable()->index();
                $table->uuid('award')->nullable()->index();
                $table->uuid('coupon')->nullable()->index();
                $table->uuid('project')->nullable()->index();
                $table->uuid('task')->nullable()->index();
                $table->uuid('meeting')->nullable()->index();
                $table->uuid('goal')->nullable()->index();
                $table->json(PJC::COL_RDC_SHFT_BY)->nullable(); // ? number of hours to reduce from shift on this holiday dates, with a relation expection array<{$day}, {$working_times_reduced, like 2 hours for morning shift, 3 hours for afternoon shift}>
                $table->json('countries')->nullable(); // ? array of country codes that this holiday applies to (defaults to all countries if null), to be used to filter out the designations/departments/branches/vendors/customers below if their "address" (check if on the Schema) attribute does not has words matching with these states (either to the long or acronym form, like BR or Brazil)
                $table->json('states')->nullable(); // ? array of state codes that this holiday applies to (defaults to all states if null), to be used to filter out the designations/departments/branches/vendors/customers below if their "address" attribute does not has words matching with these states (either to the long or acronym form, like Rio de Janeiro or RJ)
                $table->json('designations')->nullable(); // ? array of designation ids that this holiday applies to (defaults to all designations if null)
                $table->json('departments')->nullable(); // ? array of departments ids that this holiday applies to (defaults to all departments if null)
                $table->json('branches')->nullable(); // ? array of branches ids that this holiday applies to (defaults to all branches if null)
                $table->json('companies')->nullable(); // ? array of company ids that this holiday applies to (defaults to all companies if null)
                $table->json('vendors')->nullable(); // ? array of vendor ids that this holiday applies to (defaults to all vendors if null)
                $table->json('customers')->nullable(); // ? array of customer ids that this holiday applies to (defaults to all customers if null)
                foreach (['event' => DC::TABLE_EVENTS, 'award' => DC::TABLE_AWD, 'coupon' => DC::TABLE_COUPONS, 'project' => DC::TABLE_PROJECTS, 'task' => DC::TABLE_TASKS, 'meeting' => DC::TABLE_MEETINGS, 'goal' => DC::TABLE_GL] as $col => $tableName)
                    $table->foreign($col)
                        ->references('id')
                        ->on($tableName)
                        ->nullOnDelete();
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (['event', 'award', 'coupon', 'project', 'task', 'meeting', 'goal'] as $col)
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
                            . ' on table '
                            . self::TABLE
                            . ': '
                            . $e->getMessage()
                    );
                }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
