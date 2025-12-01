<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateProjectEmailTemplatesTable extends Migration
{
    private const TABLE = 'project_email_templates';
    private const COL_TEMPLATE = 'template_id';
    private const COL_PROJ = 'project_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary(); // !CHANGED use UUID primary key
            $table->uuid(self::COL_TEMPLATE); // !CHANGED UUID foreign key to email_templates.id
            $table->uuid(self::COL_PROJ);  // !CHANGED UUID foreign key to projects.id
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
            foreach (
                [
                    self::COL_TEMPLATE                => DatabaseConstants::TABLE_EMAIL_TEMPLATES,
                    self::COL_PROJ                    => DatabaseConstants::TABLE_PROJECTS,
                    DatabaseConstants::COL_TABLE_CREATOR  => DatabaseConstants::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_TEMPLATE,
                    self::COL_PROJ,
                    DatabaseConstants::COL_TABLE_CREATOR,
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
