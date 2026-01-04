<?php

use App\Config\Constants\{DatabaseConstants as DC, MessagesConstants as MC, SupportsConstants as SC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateSupportRepliesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_SUP_REP;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code', 128)->unique()->index(); // * unique alphanumeric code for the reply, automatically generated as SUP-REP-{UUID}-{YYYYMMDDHHMMSS}
                $table->uuid(SC::COL_SPT_ID)->index();
                $table->uuid('user'); // ? the replier user, which should have a valid COL_EMP_ID ('employee_id') existing in the DC:TABLE_EMPLOYEES || whereIn('type', [UserType::Admin->value, UserType::SuperAdmin->value, UserType::Company->value]) (else throw to reject at model level)
                $table->text('description')->nullable();
                $table->timestamp(MC::COL_SNT_AT)->nullable(); // ? sent_at, when the reply was sent to the requester; if null, means it is still a draft. Automatically null if 'user' is not valid or null
                $table->boolean(MC::COL_IS_RD)->default(false)->nullable(); // ? is_read, enforced as boolean at model level
                $table->timestamp(MC::COL_RD_AT)->nullable(); // ? read_at. Nulified if nullish MC::COL_IS_RD
                $table->uuid('email')->nullable()->index(); // ? optional email reference if the reply was sent by email
                $table->uuid('notification')->nullable(); // ? optional notification reference if the reply was sent by notificationK
                $table->uuid('task')->nullable()->index(); // ? optional task reference if the reply is linked to a task // ? if null, automatically atempt to get from the 'task' column of the linked COL_SPT_ID
                $table->uuid('form')->nullable()->index(); // ? a form to send to the user as part of the reply
                $table->uuid(SC::COL_FORM_RSP)->nullable()->index(); // ? optional form response reference if the reply included a form response
                $table->uuid('log')->nullable()->index(); // ? optional activity log reference if the reply is linked to an activity
                $table->string('attachment', 254)->nullable(); // * this is never made clear, but we will filter by being a file_path (where file_exists) within the local storage of the server OR a URL starting with https:// + trusted domain (the env('APP_URL') one OR known trusted cloud storage/CDN ones) OR ids to the Document table
                foreach (
                    [
                        SC::COL_SPT_ID => DC::TABLE_SUPPORTS,
                        'user' => DC::TABLE_USERS,
                    ] as $col => $tbl
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->cascadeOnDelete();
                foreach (
                    [
                        'email' => DC::TABLE_EMAILS,
                        'notification' => DC::TABLE_NTF,
                        'task' => DC::TABLE_TASKS,
                        'form' => DC::TABLE_FORM_BUILD,
                        SC::COL_FORM_RSP => DC::TABLE_FORM_RSP,
                        'log' => DC::TABLE_ACT_LOG,
                    ] as $col => $tbl
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->nullOnDelete();
                $table->json(SC::COL_OTHER_ATTACHMENTS)->nullable(); // * json array of strings to be filtered with the same constraints as the main attachment
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    SC::COL_SPT_ID,
                    'user',
                    'email',
                    'notification',
                    'task',
                    'form',
                    SC::COL_FORM_RSP,
                    'log',
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
