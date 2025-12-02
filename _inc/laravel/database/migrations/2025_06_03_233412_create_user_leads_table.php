<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\LeadRole;
use App\Traits\{HasNullableAuditColumns, LeadConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateUserLeadsTable extends Migration
{
    use HasNullableAuditColumns, LeadConnected;
    private const TABLE = DC::TABLE_USR_LD;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addLeadColumns($table, unique: false, nullable: false, cascade: true);
            $table->uuid(UC::COL_USER_ID)->index();
            $table->enum('role', LeadRole::values())->default(LeadRole::Collaborator)->nullable(); // ? nullable for tests
            $table->boolean(AC::COL_CAN_MK_DCS)->default(false)->nullable(); // ? nullable for tests // * should automatically be converted to true in boot/save if the role is manager or supervisor OR if the user_type, fetching from user_id is admin/super_admin/'super admin'
            $table->json('logs')->nullable(); // * in save/booting, should be filtered by having a id key to correspond to a LeadActivityLog entry
            $table->foreign(UC::COL_USER_ID)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            $this->dropLeadColumnForeign($table, self::TABLE);
            foreach (
                [
                    UC::COL_USER_ID,
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col) &&
                        $table->dropForeign([$col]);
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
        Schema::dropIfExists(self::TABLE);
    }
}
