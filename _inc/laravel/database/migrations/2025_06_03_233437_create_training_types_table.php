<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{AppModuleType, IndicatorTechnicalLevel};
use App\Traits\{HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateTrainingTypesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_TRAINING_TYPES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->nullable()->unique(); // ? nullable for tests
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->enum('module', array_column(AppModuleType::cases(), 'value'))->default(AppModuleType::Other->value)->nullable()->index(); // ? nullable to avoid issues with existing data, enforced at model level to default
            $table->enum('level', array_column(IndicatorTechnicalLevel::cases(), 'value'))->default(IndicatorTechnicalLevel::Beginner->value)->nullable()->index(); // ? nullable to avoid issues with existing data, enforced at model level to default
            $table->time(PJC::COL_MIN_DR)->nullable(); // ? if not null, should be used by the linked Training to enforce minimum duration, enforced at model level as less than max duration
            $table->time(PJC::COL_MAX_DR)->nullable(); // ? if not null, should be used by the linked Training to enforce maximum duration, enforced at model level as greater than min duration
            $table->json('rules')->nullable(); // ? rules to be applied to restrict the Training that chose this type, constraining their columns accordingly, at model level
            $table->json('attachments')->nullable();
            $table->json('tags')->nullable();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
        });
        Schema::dropIfExists(self::TABLE);
    }
}
