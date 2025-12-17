<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
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
            $table->uuid(UC::COL_USER_ID)->nullable()->index(); // ? enforced at boot/save to be a user where type === 'vendor'
            $this->addSalesRepresentantColumns($table, 'vendor');
            $this->addShippingColumns($table);
            $this->addBillingColumns($table);
            $table->float('balance')->default(0.00);  // * potentially change to decimal(15,2)
            $table->json('offers')->nullable(); // * the JSON counts with a nullable identifier to be queried if the product or service is registered within the system
            $table->foreign(UC::COL_USER_ID)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->nullOnDelete();
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
