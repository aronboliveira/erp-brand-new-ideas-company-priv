<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\JobStage;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateJobStagesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_JOB_STG;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->index();
            $table->string('slug', 254)->nullable()->unique(); // ? automatically generated based on snake-cased sanitized title or job_stage_{timestamp}
            $table->enum('status', array_column(JobStage::cases(), 'value'))->default(JobStage::OnHold->value)->index();
            $table->integer('order')->default(0)->index();
            $table->integer('depth')->default(0)->nullable();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean(AC::COL_IA)->default(false)->nullable()->index();
            $table->json('tags')->nullable();
            $table->json('attachments')->nullable();
            $table->json('urls')->nullable(); // ? array of urls for meetings, calls, documents, etc
            $table->json('templates')->nullable(); // ? array of template IDs or names for documents, emails (in DC::TABLE_EMAIL_TEMPLATES) , notifications (in DC::TABLE_NOTIFICATION_TEMPLATES, etc.
            $table->uuid('project')->nullable()->index();
            $table->uuid('goal')->nullable()->index();
            $table->uuid('training')->nullable()->index();
            $table->foreign('project')
                ->references('id')
                ->on(DC::TABLE_PROJECTS)
                ->nullOnDelete();
            $table->foreign('goal')
                ->references('id')
                ->on(DC::TABLE_GL)
                ->nullOnDelete();
            $table->foreign('training')
                ->references('id')
                ->on(DC::TABLE_TRAINING)
                ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (['project', 'goal', 'training'] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        &&
                        $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
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
