<?php

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\{BranchConnected, EmployeeConnected, HasNullableAuditColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateTrainingsTable extends Migration
{
    use BranchConnected, EmployeeConnected, HasNullableAuditColumns;
    private const TABLE = DC::TABLE_TRAINING;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable()->index(); // * at boot/save, set to "Training — {branch name || company name} on {PJC::COL_S_DT}" if null and if the query from the CC_COL_TRN_TP is successful, then the append " — {type name}", if the module is set for the type, then " ({module})"
            $table->uuid('company')->nullable()->index(); // * at boot/save, demand that there should either a Company linked (DB::table(DC::TABLE_USERS)->where('type', 'company') or a Branch linked; if the later, then company is inferred from branch
            $this->addBranchColumns($table, unique: false, nullable: true, prefixed: false); // ? nullable to allow for company-wide trainings
            $this->addEmployeeColumns($table, unique: false, nullable: false, cascade: false);
            $table->uuid('trainer')->index();
            $table->unsignedTinyInteger(CC::COL_TRAINER_OPT)->default(0)->index(); // * should be clamped according to the cases of the WorkActivityScope enum, which actually returns strings, but we store as int in accordance to the index (0 => internal, for instance)
            $table->uuid(CC::COL_TRN_TP)->index();
            $table->unsignedDecimal(CC::COL_TRN_CST, 10, 2)->default(0.00); // * enforced at boot/save to be never negative
            $table->time(PJC::COL_MIN_DR)->nullable(); // ? should be within the min duration of the type if it's set, always less than max duration
            $table->time(PJC::COL_MAX_DR)->nullable(); // ? should be within the max duration of the type if it's set, always greater than min duration
            $table->time(PJC::COL_EXP_DR)->nullable()->index(); // ? should be within the min and max duration if they are set
            $table->date(PJC::COL_S_DT)->index();
            $table->date(PJC::COL_E_DT);
            $table->boolean('required')->default(false)->nullable()->index(); // ? nullable for tests, enforced as boolean at model level
            $table->text('description')->nullable();
            $table->uuid('certificate')->nullable()->index(); // ? reference to a document template that is the certificate issued upon completion, which may be null
            $table->unsignedTinyInteger('performance')->default(0)->index(); // * should be clamped according to the cases indexes of ResultPerformance enum
            $table->unsignedTinyInteger('status')->default(0)->index(); // * should be clamped according to the cases indexes of WorkActivityProgress enum
            $table->text('remarks')->nullable();
            $table->json('attachments')->nullable();
            $table->json(CC::COL_RQ_CERT)->nullable(); // ? list of required certificates for the training, enforced at model level, filtered by existing as an id or name column with the DC::TABLE_DOCS
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->foreign('company')
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->nullOnDelete();
            $table->foreign('certificate')
                ->references('id')
                ->on(DC::TABLE_DOCS)
                ->nullOnDelete();
            foreach (
                [
                    CC::COL_TRN_TP        => DC::TABLE_TRAINING_TYPES,
                    'trainer'   => DC::TABLE_TRAINERS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->restrictOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        try {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $this->dropAuditColumnForeigns($table, self::TABLE);
                $this->dropEmployeeColumnForeign($table, self::TABLE);
                $this->dropBranchColumnForeign($table, self::TABLE, prefixed: false);
                foreach (
                    [
                        'company',
                        CC::COL_TRN_TP,
                        'trainer',
                        'certificate'
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
        } catch (\Exception $e) {
            Log::warning(
                'One or more foreign keys on `' . self::TABLE . '` did not exist: '
                    . $e->getMessage()
            );
        }
        Schema::dropIfExists(self::TABLE);
    }
}
