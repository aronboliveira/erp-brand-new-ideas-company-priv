<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\EvaluationStatus;
use App\Traits\{HasNullableAuditColumns, HasRatingColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateAppraisalsTable extends Migration
{
    use HasNullableAuditColumns, HasRatingColumns;
    private const TABLE = DC::TABLE_APR;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $this->addRatingColumns($table);
            $table->string(PJC::COL_APR_DT)->nullable(); // * later consider using date type
            $table->enum('status', array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::Pending->value)->nullable()->index(); // ? nullable for testing, enforced with the Enum at model/level
            $table->text('remark')->nullable();
            $table->uuid('appraiser')->nullable()->index();
            $table->json('acknowledgers')->nullable(); // ? list of user IDs who acknowledged the appraisal with timestamps of steps, filtered in boot/save by querying into the Users Table and Employee table by checking by id or name in both, and user_id in Employee table (always checking if the Schema has the column first)
            $table->json('metadata')->nullable(); // ? extra data related to the appraisal
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        try {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $this->dropAuditColumnForeigns($table, self::TABLE);
                $this->dropRatingColumnForeigns($table, self::TABLE);
            });
        } catch (\Exception $e) {
            Log::warning(
                'One or more foreign keys on `' . self::TABLE . '` did not exist: '
                    . $e->getMessage()
            );
        }
        Schema::dropIfExists(self::TABLE);
    }
}
