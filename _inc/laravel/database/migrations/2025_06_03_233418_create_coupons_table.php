<?php

use App\Config\Constants\{ActivitiesConstants as AC, BillsConstants as BC, CompaniesConstants as CC, DatabaseConstants as DC};
use App\Enums\PaymentPatternType;
use App\Traits\{HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateCouponsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_COUPONS;

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->index()->unique();
            $table->string('name')->nullable();
            $table->float('discount')->default(0.00);
            $table->enum(BC::COL_DSC_TP, array_map(fn($e) => $e->value, PaymentPatternType::cases()))->default(PaymentPatternType::Fixed->value)->nullable(); // ? nullable for testing, enforced to default at model level
            $table->uuid(CC::COL_GIVEN_BY)->nullable();
            $table->float(BC::COL_MIN_UNLCK)->default(0.00)->nullable(); // ? nullable for testing, enforced at model
            $table->float(BC::COL_MAX_DSC)->default(0.00)->nullable(); // ? nullable for testing, enforced at model
            $table->unsignedSmallInteger('limit')->default(1); // ? max usages
            $table->text('description')->nullable();
            $table->boolean(AC::COL_IA)->default(true);
            $table->datetime(BC::COL_VLD_FRM)->default(now())->nullable(); // ? nullable for testing, enforced at model level
            $table->datetime(BC::COL_VLD_TO)->nullable(now()->addMonth(1)); // ? nullable for testing, enforced at model level
            $table->date(BC::COL_DT_LMT_TO_USER)->nullable(); // ? nullable for testing, enforced at model level, to add up through model method with the user creation date, by default +7 days
            $table->boolean('stackable')->default(true)->nullable(); // ? if can be used with other coupons // ? nullable for testing, enforced at model level
            $table->boolean(BC::COL_MUST_BE_VRF)->default(false)->nullable(); // ? must the user have true to COL_EM_V_AT in order to use the coupon // ? nullable for testing, enforced at model level
            $table->unsignedTinyInteger(BC::COL_MIN_PRV_ORD)->default(0)->nullable(); // ? nullable for testing, enforced at model level
            $table->unsignedInteger(BC::COL_MAX_PRV_ORD)->default(2)->nullable(); // ? nullable for testing, enforced at model level
            $table->boolean(BC::COL_CAN_BE_GIFT)->default(false)->nullable(); // ? nullable for testing, enforced at model level
            $table->json(BC::COL_EXC_RLS)->nullable(); // ? list to be compared to the UserType enum cases again the candidate user through model method, be querying the COL_TP of the user instance
            $table->json(BC::COL_EXC_PRD)->nullable(); // ? list of ProductService IDs, checked by method
            $table->json(BC::COL_EXC_CAT)->nullable(); // ? list of ProductServiceCategory IDs, checked by method
            $table->json(BC::COL_APL_CAT)->nullable(); // ? list of ProductServiceCategory IDs, checked by method
            $table->json('rules')->nullable(); // ? additional rules in json, checked by method, pertaining the user model
            $table->foreign(CC::COL_GIVEN_BY)->references('id')->on(DC::TABLE_USERS)->onDelete('set null');
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            Schema::hasColumn($table, CC::COL_GIVEN_BY) && $table->dropForeign([CC::COL_GIVEN_BY]);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
