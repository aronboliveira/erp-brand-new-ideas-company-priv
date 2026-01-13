<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{GoalType};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateGoalsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_GL;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->enum('type', array_column(GoalType::cases(), 'value'))->index();
            $table->string('from')->nullable(); // * this is not clear, but keep for legacy. Probably a datetime later. Filtered as such at boot/save, in model
            $table->string('to')->nullable(); // * this is not clear, but keep for legacy. Probably a datetime later. Filtered as such at boot/save, in model
            $table->unsignedDecimal('amount', 15, 2)->default(0.00)->nullable(); // * presumed as the total amount to reach the goal, but this should be nullable, since some goals are not directly quantifiable, such as 'Increase User Engagement' etc
            $table->text('description')->nullable();
            $table->boolean(PJC::COL_IS_DSP)->default(true); // * this is not clear, maybe it is to define if the goal is displayed in some UI, but keep for legacy
            $table->json('metrics')->nullable(); // * this is more realistic for a broad goal definition, an array of key-value pairs defining the metrics to be tracked for this goal, nullable for now
            $table->json('trackings')->nullable(); // * array of ids to be queryable and filtered in DB::table(DC::TABLE_GL_TRK), nullable for now
            $table->uuid('leader')->nullable()->index(); // * id of the goal leader (an employee), nullable for now
            $table->uuid(PJC::COL_PLN_SCHD_ID)->nullable()->index(); // ? if from or to mark dates, then the COL_S_DT and COL_E_DT of this should respect the datetime limits, else nullified
            $table->json('involved')->nullable(); // * array of ids of employees (forcefully adding the leader if he is not found), filtered by querying into the DB::table(DC::TABLE_EMPLOYEES) for existing ids only, nullable for now
            $table->json('sponsors')->nullable(); // * array of ids of companies (users of type === 'company'), filtered by querying into the DB::table(DC::TABLE_USERS) for existing ids only WHERE the type matches, nullable for now
            $table->json('stakeholders')->nullable(); // * array of ids of users (employees or users of type === 'company' or type === 'admin' or type === 'super admin'), filtered by querying into the DB::table(DC::TABLE_USERS) for existing ids only, nullable for now
            $table->json('tags')->nullable();
            foreach (
                [
                    'leader' => DC::TABLE_EMPLOYEES,
                    PJC::COL_PLN_SCHD_ID => DC::TABLE_PLN_SCHD,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    'leader',
                    PJC::COL_PLN_SCHD_ID,
                ] as $column
            )
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
        });
        Schema::dropIfExists(self::TABLE);
    }
}
