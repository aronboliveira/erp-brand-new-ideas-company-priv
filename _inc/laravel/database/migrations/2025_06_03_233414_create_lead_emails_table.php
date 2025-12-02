<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\{HasNullableAuditColumns, LeadConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateLeadEmailsTable extends Migration
{
    use HasNullableAuditColumns, LeadConnected;
    private const TABLE = DC::TABLE_LD_EMAILS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addLeadColumns($table);
            $table->string('from', 255)->index()->nullable(); // ? nullable for testing purposes // it's not clear why this wasn't in the old implementation, // * at boot/saving, this should be normalized with normalizes address
            $table->string('to', 255)->index(); // * at boot/saving, this should be normalized with normalizes address
            $table->string('subject', 255)->nullable();
            $table->unsignedInteger('counter')->default(1)->nullable(); // ? nullable for testing purposes // ? how many messages have been exchanged between the from and to addresses regarding this lead
            $table->boolean(PJC::COL_IS_FUP)->default(false)->nullable(); // ? nullable for testing purposes // * is this email a follow-up message
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();
            $table->json(PJC::COL_ATC_FRULES)->nullable(); // * at boot/saving, this should not for expected keys of filtering rules and match with expected fields from the attachment objects/subarrays, then filter accordingly
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropLeadColumnForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
