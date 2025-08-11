<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLeadEmailsTable extends Migration
{
    private const TABLE = 'lead_emails';
    private const COL_LEAD = 'lead_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->uuid(self::COL_LEAD)->index();
            $table->string('to');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            $table->timestamps();
            $table->foreign(self::COL_LEAD)
                ->references('id')
                ->on(DatabaseConstants::TABLE_LEADS)
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, self::COL_LEAD) &&
                    $table->dropForeign([self::COL_LEAD]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . self::COL_LEAD
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
