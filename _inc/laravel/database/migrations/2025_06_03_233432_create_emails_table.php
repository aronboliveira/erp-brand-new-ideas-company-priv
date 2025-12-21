<?php

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    EmailsConstants as EC,
    MessagesConstants as MC
};
use App\Enums\AppModuleType;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEmailsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_EMAILS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title')->nullable()->index(); // ? the subject of the email
            $table->string('provider')->nullable()->index(); // ? the application/module from which the email was sent (e.g.: outlook, protonmail, gmail, nextcloud, zimbra, etc.)
            $table->text('description')->nullable();
            $table->text('body')->nullable(); // ? the plain text version of the email body
            $table->text('html')->nullable(); // ? the html version of the email body
            $table->text('notes')->nullable();
            $table->string('from', 254)->nullable()->index(); // * this should never have been nullable, but to avoid issues with existing data, keeping it as nullable for now and, if the COL_FROM_ID does not point to a user with a valid email, selecting the email from the DC::DEFAULT_UUID (it's the system one) OR, if null, then from the first 'super admin' (falling back to 'admin') who has it, at model level // ? constrained at model level with NormalizesAddresses trait
            $table->uuid(EC::COL_FROM_ID)->nullable()->index(); // ? the sender is not necessarily a user
            $table->string('to', 254)->nullable()->index(); // * this should never have been nullable, but to avoid issues with existing data, keeping it as nullable for now and, if the COL_TO_ID does not point to a user with a valid email, selecting the email from a random 'super admin' (falling back to 'admin') who has it, at model level  // ? constrained at model level with NormalizesAddresses trait
            $table->uuid(EC::COL_TO_ID)->nullable()->index(); // ? the recipient is not necessarily a user
            $table->boolean(AC::COL_IS_RPL)->default(false)->nullable(); // ? nullable for tests, constrained at model level
            $table->json('thread')->nullable(); // ? array of email ids representing the email thread, constrained at model level (there will be a migration for Threads later on)
            $table->json('cc')->nullable(); // ? array of emails, constrained at model level with NormalizesAddresses trait
            $table->json('bcc')->nullable(); // ? array of emails, constrained at model level with NormalizesAddresses trait
            $table->boolean(AC::COL_IS_FV)->default(false)->nullable(); // ? nullable for tests, constrained at model level, whether the message is flagged as favorite/pinned/important
            $table->boolean(MC::COL_IS_DFT)->default(false)->nullable(); // ? nullable for tests, constrained at model level
            $table->boolean(MC::COL_IS_TRS)->default(false)->nullable(); // ? nullable for tests, constrained at model level
            $table->boolean(MC::COL_IS_ARC)->default(false)->nullable(); // ? nullable for tests, constrained at model level
            $table->boolean(MC::COL_IS_SPAM)->default(false)->nullable(); // ? nullable for tests, constrained at model level
            $table->boolean(DC::COL_MW_FREE)->default(true)->nullable(); // ? nullable for tests, constrained at model level
            $table->timestamp(MC::COL_SNT_AT)->nullable(); // ? nullable for tests, constrained at model level to be never be in the future or >= than RD_AT
            $table->boolean(MC::COL_IS_RD)->default(false)->nullable(); // ? nullable for tests, constrained at model level, whether the message has been read
            $table->timestamp(MC::COL_RD_AT)->nullable(); // ? nullable for tests, constrained at model level, the timestamp when the message was read, nullified is MC::COL_IS_RD is false; if not null, then never < MC::COL_SNT_AT
            $table->string(EC::COL_D_URL, 254)->nullable();
            $table->uuid(DC::COL_DOC_ID)->nullable()->index();
            $table->string(EC::COL_EM, 254)->unique();
            $table->enum(AC::COL_MT, array_column(AppModuleType::cases(), 'value'))->default(AppModuleType::Other->value)->nullable()->index(); // ? nullable to avoid issues with existing data, enforced at model level
            $table->uuid(AC::COL_MI)->nullable(); // * kept for legacy
            $table->unsignedInteger('counter')->default(0); // ? number of times the email was opened
            $table->json('headers')->nullable();
            $table->json('attachments')->nullable();
            $table->json('templates')->nullable(); // ? list of available ids (or titles) for DC::TABLE_EMAIL_TEMPLATES, filtered by the id existing + to be filtered by the module_type later, manually
            $table->json('variables')->nullable(); // ? json object with key-value pairs representing variables to be replaced in the template body and the expected types/format of these variables
            $table->json('settings')->nullable(); // ? json object representing additional settings for the email template, such as priority, read receipt request, etc.
            $table->json(DC::COL_MW_SCAN)->nullable(); // ? metadata from malware scan
            $table->json('metadata')->nullable();
            foreach (
                [
                    DC::COL_DOC_ID => DC::TABLE_DOCS,
                    EC::COL_FROM_ID => DC::TABLE_USERS,
                    EC::COL_TO_ID => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table
                    ->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    DC::COL_DOC_ID,
                    EC::COL_FROM_ID,
                    EC::COL_TO_ID,
                ] as $column
            ) {
                try {
                    Schema::hasColumn($table->getTable(), $column)
                        &&
                        $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
