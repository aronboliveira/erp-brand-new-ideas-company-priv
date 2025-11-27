<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\{HasBasicUserLikeColumns, HasNullableAuditColumns, RegistersShipping, HasSalesRepresentantColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateVendorsTable extends Migration
{
    use HasBasicUserLikeColumns, HasNullableAuditColumns, HasSalesRepresentantColumns, RegistersShipping;
    private const TABLE = DC::TABLE_VENDORS;
    private const B = 'billing';
    private const S = 'shipping';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $this->addUserLikeColumns($table, defaultAvatar: 'chatify.user_avatar.default', hasPassword: true);
            $this->addSalesRepresentantColumns($table, 'vendor');
            $this->addShippingColumns($table);
            $this->addBillingColumns($table);
            $table->float('balance')->default(0.00);  // * potentially change to decimal(15,2)
            $table->json('offers')->nullable(); // * the JSON counts with a nullable identifier to be queried if the product or service is registered within the system
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropSalesRepresentantColumnForeigns($table, self::TABLE, 'vendor');
        });
        Schema::dropIfExists(self::TABLE);
    }
}
