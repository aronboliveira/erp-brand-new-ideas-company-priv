<?php

use App\Config\Constants\{
    DatabaseConstants as DC,
    EmailsConstants as EC,
    ProjectsConstants as PC,
    UsersConstants as UC
};
use App\Traits\{HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateUserEmailTemplatesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_USER_EML_TMPS;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid(EC::COL_TMP);
                $table->uuid(UC::COL_USER_ID);
                $table->boolean(UC::COL_IA)->default(true); // * this is redundant if we have audit or FK columns, but can useful for quick checks...
                $table->unsignedInteger('counter')->default(0)->nullable(); // ? how many times this user used this template // ? nullable for testing, enforced in boot/save
                $table->boolean(PC::COL_IS_FV)->default(false)->nullable(); // is_favorite // ? nullable for testing, enforced in boot/save
                $table->boolean(DC::COL_IS_DEF)->default(false)->nullable(); // is_default // ? nullable for testing, enforced in boot/save
                foreach (
                    [
                        EC::COL_TMP     => DC::TABLE_EMAIL_TEMPLATES,
                        UC::COL_USER_ID => DC::TABLE_USERS,
                    ] as $col => $tbl
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->cascadeOnDelete(); // * ADDED
                $this->addAuditColumns($table);
                $table->json('clients')->nullable(); // ? list of email clients the user wants to use this template with
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    EC::COL_TMP,
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
