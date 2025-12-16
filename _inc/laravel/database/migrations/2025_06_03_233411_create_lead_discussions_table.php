<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{UserType};
use App\Traits\{HasNullableAuditColumns, LeadConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLeadDiscussionsTable extends Migration
{
    use HasNullableAuditColumns, LeadConnected;
    private const TABLE = DC::TABLE_LD_DSC;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(UC::COL_USER_ID)->nullable()->index(); // ? nullable for tests // ? the user who made the comment
            $table->enum(UC::COL_U_TP, UserType::values())->default(UserType::Client)->nullable(); // ? nullable for tests
            $this->addLeadColumns($table, unique: false, nullable: false, cascade: true);
            $table->text('comment');
            $table->boolean(AC::COL_CAN_NADM_DL)->default(false)->nullable(); // ? nullable for tests
            $table->boolean(AC::COL_IS_FLAG)->default(false)->nullable(); // ? nullable for tests
            $table->boolean(AC::COL_IS_RPL)->default(false)->nullable(); // ? nullable for tests
            $table->boolean(AC::COL_IS_RPLD)->default(false)->nullable(); // ? nullable for tests
            $table->string('label', 255)->nullable();
            $table->json('attachments')->nullable();
            $table->json('reactions')->nullable(); // ? still to be defined, so not relational for now
            $table->json('metadata')->nullable();
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
            $this->dropLeadColumnForeign($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
