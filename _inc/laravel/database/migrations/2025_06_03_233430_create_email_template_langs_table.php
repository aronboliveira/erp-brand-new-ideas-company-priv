<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateEmailTemplateLangsTable extends Migration
{
    private const TABLE = 'email_template_langs';
    private const COL_PARENT = 'parent_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(self::COL_PARENT)->index();
            $table->string('lang', 100)->default(DatabaseConstants::DEFAULT_LANG);
            $table->string('subject');
            $table->text('content');
            $table->timestamps();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
            foreach (
                [
                    self::COL_PARENT               => DatabaseConstants::TABLE_EMAIL_TEMPLATES,
                    DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
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
            foreach (
                [
                    self::COL_PARENT,
                    DatabaseConstants::COL_TABLE_CREATOR,
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
