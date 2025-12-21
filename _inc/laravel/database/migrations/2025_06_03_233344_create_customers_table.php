<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BillsConstants as BC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Traits\{HasBasicUserLikeColumns, HasNullableAuditColumns, HasSalesRepresentantColumns, RegistersShipping};
use Illuminate\Database\{
    Migrations\Migration,
    Schema\Blueprint
};
use Illuminate\Support\Facades\{
    Log,
    Schema
};

class CreateCustomersTable extends Migration
{
    use HasBasicUserLikeColumns, HasNullableAuditColumns, HasSalesRepresentantColumns, RegistersShipping;

    private const TABLE = DC::TABLE_CUSTOMERS;

    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) return;

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $this->addUserLikeColumns($table);
            $table->string('phone', 64)->nullable()->index();
            $table->string('website', 255)->nullable();
            $table->json(AC::COL_SC_MD)->nullable(); // ? filtered as an array containing direct urls for a valid social media domains, or an associative array with the keys (normalized) as the names of a recognized social media and some value in a inner key that has a valid social media domain url, else filtered out
            $this->addSalesRepresentantColumns($table, 'customer');
            $table->integer(BC::COL_OD_C)->default(0)->nullable();
            $table->float(UC::COL_AVG_RT, 2, 2)->default(5.00)->nullable()->index(); // ? Nullable para testes; idealmente decimal(3,2) com range 0–5
            $this->addShippingColumns($table);
            $this->addBillingColumns($table);
            $table->decimal('balance', 15, 2)->default(0.00);
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropSalesRepresentantColumnForeigns($table, self::TABLE, 'customer');
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });

        Schema::dropIfExists(self::TABLE);
    }
}
