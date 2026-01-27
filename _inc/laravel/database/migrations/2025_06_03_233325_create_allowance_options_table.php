<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Traits\{HasNullableAuditColumns, IsNumericBenefit};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateAllowanceOptionsTable extends Migration
{
    use HasNullableAuditColumns, IsNumericBenefit;
    private const TABLE = DC::TABLE_ALLOWANCE_OPTS;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addNumericBenefitColumns($table);
            $table->date(BC::COL_VLD_FRM)->nullable()->useCurrent();
            $table->date(BC::COL_VLD_TO)->nullable()->default(now()->addYear(2)->format('Y-m-d'))->index();
            $table->boolean('renews')->default(false)->index();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
