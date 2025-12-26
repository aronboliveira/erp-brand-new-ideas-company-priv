<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Enums\EmailTemplateType;
use App\Traits\{HasNullableAuditColumns, IsTemplate};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEmailTemplatesTable extends Migration
{
    use HasNullableAuditColumns, IsTemplate;
    private const TABLE = DC::TABLE_EMAIL_TEMPLATES;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->enum('type', array_column(EmailTemplateType::cases(), 'value'))->default(EmailTemplateType::Other->value)->nullable()->index(); // ? nullable to avoid issues with existing data
                $table->string('title')->nullable()->index();
                $table->string('from')->nullable();
                $table->uuid('notification')->nullable()->index();
                $this->addTemplateColumns($table);
                $table->json('variables')->nullable(); // ? json object with key-value pairs representing variables to be replaced in the template body and the expected types/format of these variables
                $table->json('settings')->nullable(); // ? json object representing additional settings for the email template, such as priority, read receipt request, etc.
                $table->json(DC::COL_PLT_AV)->nullable(); // ? platforms where this template is available, e.g., web, mobile, outlook, gmail, protonmail, etc.
                $table->foreign('notification')
                    ->references('id')
                    ->on(DC::TABLE_NOTIFICATION_TEMPLATES)
                    ->nullOnDelete();
                $this->addAuditColumns($table);
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            try {
                Schema::hasColumn(self::TABLE, 'notification') &&
                    $table->dropForeign(['notification']);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . 'notification'
                        . ' on table '
                        . self::TABLE
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
