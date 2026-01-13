<?php

use App\Config\Constants\{ActivitiesConstants as AC, CompaniesConstants as CC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use App\Enums\{ApprovalType, EncryptionType, Frequency, MeetingType};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

class CreateZoomMeetingsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_ZM_MT;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(CC::COL_MT_ID)->index();
            $table->string('code')->nullable()->unique(); // ? set from the 'code' of the linked row DC::TABLE_MEETINGS (via CC::COL_MT_ID); if null or the column is not set, generated as ZM-{UUID} on save, checking uniqueness with do/while;
            $table->string('title')->nullable()->index(); // ? same from 'code', but querying for 'title'
            $table->string('password')->nullable(); // ? hashed at model level
            $table->enum(AC::COL_ENC_TP, [EncryptionType::EnhancedEncryption->value, EncryptionType::EndToEnd->value])->default(EncryptionType::EnhancedEncryption->value)->nullable(); // ? '0=enhanced encryption, 1=end to end encryption');
            $table->integer('duration')->default(0); // ? should respect the constraints imposed by PJC::COL_MIN_DR, PJC::COL_EXP_DR and PJC::COL_MAX_DR from the row in DC::TABLE_MEETINGS
            $table->string(AC::COL_STRT_URL)->nullable(); // ? must be filtered as a zoom valid url
            $table->string(AC::COL_JOIN_URL)->nullable(); // ? must be filtered as a zoom valid url // ? should mirror the 'url' of the linked row in DC::TABLE_MEETINGS, if that's not null
            $table->string(AC::COL_RGT_URL)->nullable(); // ? must be filtered as a zoom valid url
            $table->enum('type', array_column(MeetingType::cases(), 'value'))->default(MeetingType::Scheduled->value)->nullable();
            $table->enum('frequency', array_column(Frequency::cases(), 'value'))->default(Frequency::Once->value)->nullable(); // ? only valid for 'recurring_fixed' and 'recurring_no_fixed' types, else nullified
            $table->string('timezone', 128)->default('UTC')->nullable();
            $table->uuid(PJC::COL_PJ_ID)->nullable()->index();
            $table->uuid(UC::COL_USER_ID)->nullable();
            $table->string(PJC::COL_CLIENT_ID)->nullable()->index(); // ? polymorphic key to reference the id of a row in DC::TABLE_USERS whereIn('type', [UserType::Client->value, UserType::Customer->value, UserType::Vendor->value, UserType::Company->value]) || a id of a row in DC::TABLE_CUSTOMERS || DC::TABLE_CLIENTS || DC::TABLE_VENDORS || DC::TABLE_LEADS
            $table->timestamp(PJC::COL_S_DT)->default(DB::raw('CURRENT_TIMESTAMP(0)')); // ? set as the 'date' from the linked row in DC::TABLE_MEETINGS
            $table->enum('audio', ['both', 'telephony', 'voip'])->default('both')->nullable();
            $table->enum(AC::COL_AUTO_RCD, ['local', 'cloud', 'none'])->default('none')->nullable();
            $table->unsignedInteger(AC::COL_MAX_PRT)->default(100)->nullable();
            $table->text('agenda')->nullable();
            $table->string('status')->default('waiting')->nullable();
            $table->boolean(AC::COL_MT_CHAT)->default(true)->nullable();
            $table->boolean(AC::COL_PV_CHAT)->default(true)->nullable();
            $table->boolean(AC::COL_SCR_SHR)->default(true)->nullable();
            $table->enum(AC::COL_WHO_CAN_SHR_SCR, ['host_only', 'all_participants'])->default('host_only')->nullable();
            $table->boolean(AC::COL_WT_ROOM)->default(false)->nullable();
            $table->boolean(AC::COL_BRK_ROOM)->default(false)->nullable();
            $table->boolean(AC::COL_FC_MD)->default(false)->nullable();
            $table->boolean(AC::COL_USE_PMI)->default(false)->nullable();
            $table->boolean(AC::COL_ALT_HST_ENB)->default(false)->nullable();
            $table->text(AC::COL_ALT_HST)->nullable(); // ? comma separated list of alternative host emails or ids (for DC::TABLE_USERS), nullified if AC::COL_ALT_HST_ENB is false
            $table->boolean(AC::COL_CLS_RGT_AFT_HRS)->default(false)->nullable();
            $table->boolean(AC::COL_MUTE_UPON_ENTRY)->default(false)->nullable();
            $table->boolean(AC::COL_CTC_NM_RQ)->default(false)->nullable();
            $table->boolean(AC::COL_CTC_EML_RQ)->default(false)->nullable();
            $table->boolean(AC::COL_ALW_SHR_BT)->default(true)->nullable();
            $table->boolean(AC::COL_ALW_MT_DV)->default(true)->nullable();
            $table->enum(AC::COL_APV_TP, array_column(ApprovalType::cases(), 'value'))->default(ApprovalType::NoRegistration->value)->nullable();
            $table->foreign(CC::COL_MT_ID)->references('id')->on(DC::TABLE_MEETINGS)->cascadeOnDelete();
            foreach (
                [
                    PJC::COL_PJ_ID                 => DC::TABLE_PROJECTS,
                    UC::COL_USER_ID                 => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $table->json('settings')->nullable();
            $table->json('participants')->nullable(); // ? string[] of emails or user ids
            $table->json('webhooks')->nullable();
            $table->json('metadata')->nullable();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    CC::COL_MT_ID,
                    PJC::COL_PJ_ID,
                    UC::COL_USER_ID,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to execute down for '
                            . $column
                            . ' foreign key column: '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
