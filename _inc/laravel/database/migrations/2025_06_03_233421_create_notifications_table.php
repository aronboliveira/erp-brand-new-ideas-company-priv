<?php

use App\Config\Constants\{DatabaseConstants as DC, MessagesConstants as MC, UsersConstants as UC};
use App\Enums\NotificationTemplateType;
use App\Traits\{HasNullableAuditColumns, TracksFailures};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateNotificationsTable extends Migration
{
    use HasNullableAuditColumns, TracksFailures;
    private const TABLE = DC::TABLE_NTF;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(UC::COL_USER_ID)->index(); // ? not clear, but keep for legacy, at first it was intended to point to the recipient user probably
            $table->datetime(MC::COL_SNT_AT)->nullable()->useCurrent(); // * this should be refresh on booted, specially if null // ? nullable for testing
            $table->uuid(MC::COL_SNT_BY)->nullable()->index()->default(DC::DEFAULT_UUID); // ? nullable for testing
            $table->string('type', 32)->default(NotificationTemplateType::Other->value);
            $table->longText('data')->nullable(); // * this is not clear, but keep for legacy
            $table->json('attachments')->nullable();
            $table->unsignedTinyInteger(MC::COL_IS_RD)->default(0); // * this should be a boolean, but keep for legacy
            $table->timestamp(MC::COL_RD_AT)->nullable()->default(null);
            $table->foreign(MC::COL_SNT_BY)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            $table->json('platforms')->nullable(); // * array<string>, normalized, of platform identifiers according to the MessagingPlatform enum
            $this->addAuditColumns($table);
            $this->addFailureTrackingColumns($table);
            foreach (
                [
                    UC::COL_USER_ID => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    UC::COL_USER_ID,
                    MC::COL_SNT_BY,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ' on table '
                            . self::TABLE
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
