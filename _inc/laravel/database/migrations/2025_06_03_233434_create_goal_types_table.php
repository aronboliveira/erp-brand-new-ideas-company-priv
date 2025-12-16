<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Enums\GoalType;
use App\Traits\{HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Schema};

class CreateGoalTypesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE         = DC::TABLE_GOAL_TYPES;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('category', array_column(GoalType::cases(), 'value'))->nullable()->index(); // ? nullable to avoid issues with existing data, unique later
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#006666')->nullable();
            $table->json('rules')->nullable(); //* this should contain an array with fields that represent columns found in the Goals migration, and impose valid constraints (such as max, min for a string column, acceptable attachment mimes, etc) that do not conflict with the own Goals migration, while unfitting rule keys are filtered out // ? nullable to avoid issues with existing data
            $table->json('metadata')->nullable();
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
