<?php

use App\Config\Constants\{
    DatabaseConstants,
    EmailsConstants,
    UsersConstants
};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateUserEmailTemplatesTable extends Migration
{
    private const TABLE = 'user_email_templates';
    private const COL_TEMPLATE = EmailsConstants::COL_TMP;
    private const COL_USER = UsersConstants::COL_USER_ID;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                 // ! CHANGED
                $table->uuid(self::COL_TEMPLATE);                   // ! CHANGED
                $table->uuid(self::COL_USER);                       // ! CHANGED
                $table->boolean(EmailsConstants::COL_IA)->default(true);   // ! CHANGED
                $table->timestamps();
                $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
                foreach (
                    [
                        self::COL_TEMPLATE                 => DatabaseConstants::TABLE_EMAIL_TEMPLATES,
                        self::COL_USER                     => DatabaseConstants::TABLE_USERS,
                        DatabaseConstants::COL_TABLE_CREATOR   => DatabaseConstants::TABLE_USERS,
                    ] as $col => $tbl
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->cascadeOnDelete(); // * ADDED
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_TEMPLATE,
                    self::COL_USER,
                    DatabaseConstants::COL_TABLE_CREATOR,
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
