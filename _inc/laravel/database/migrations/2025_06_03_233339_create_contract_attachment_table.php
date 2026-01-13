<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\AttachmentModuleType;
use App\Traits\{HasNullableAuditColumns, HasFileColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateContractAttachmentTable extends Migration
{
    use HasNullableAuditColumns, HasFileColumns;
    private const TABLE = DC::TABLE_CTC_ATC;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->nullable()->unique(); // ? evaluated and, if not following pattern, generated at boot/save as CTC-ATC-{UUID}, checking uniqueness with do/while // * nullable for tests
            $table->uuid(PJC::COL_CTC_ID)->index();
            $table->uuid(UC::COL_USER_ID)->nullable()->index(); // ? the submitter of the attachment
            $table->timestamp(PJC::COL_SBM_AT)->nullable(); // ? submission attempt id, if applicable
            $table->uuid(PJC::COL_APV_BY)->nullable()->index(); // ? the approver of the attachment, if applicable. If the linked row of DC::TABLE_CONTRACTS has the 'status' column in [EvaluationStatus::Accept->value, EvaluationStatus::Active->value] and this is null, then "import" from COL_APV_BY if this is not null in the linked contract row
            $table->timestamp(PJC::COL_APV_AT)->nullable(); // ? approval timestamp, if applicable. If the linked row of DC::TABLE_CONTRACTS has the 'status' column in [EvaluationStatus::Accept->value, EvaluationStatus::Active->value] and this is null, then "import" from COL_APV_AT if this is not null in the linked contract row
            $table->uuid(PJC::COL_REJ_BY)->nullable()->index(); // ? the rejector of the attachment, if applicable. If the linked row of DC::TABLE_CONTRACTS has the 'status' column in [EvaluationStatus::Suspended->value, EvaluationStatus::Cancelled->value, EvaluationStatus::Expired->value, EvaluationStatus::Decline->value] and this is null, then "import" from COL_REJ_BY if this is not null in the linked contract row
            $table->timestamp(PJC::COL_REJ_AT)->nullable(); // ? rejection timestamp, if applicable. If the linked row of DC::TABLE_CONTRACTS has the 'status' column in [EvaluationStatus::Suspended->value, EvaluationStatus::Cancelled->value, EvaluationStatus::Expired->value, EvaluationStatus::Decline->value] and this is null, then "import" from COL_REJ_AT if this is not null in the linked contract row
            $this->addFileColumns($table);
            $table->enum(PJC::COL_ATC_TP, array_column(AttachmentModuleType::cases(), 'value'))
                ->default(AttachmentModuleType::Other->value)
                ->nullable()
                ->index(); // ? enforced at model level to be one of AttachmentModuleType values
            $table->string('files')->nullable(); // ? a list of comma-separated file identifiers that can be either: a. a id for a row in DC::TABLE_DOCS, a safe url (within env(APP_URL) or trusted cloud storage domains) or a path relative to local storage of the server. Must include the main file as the first entry (found in the HasFileColumns columns), if not null, prioritizing url over file_path
            $table->foreign(PJC::COL_CTC_ID)
                ->references('id')
                ->on(DC::TABLE_CONTRACTS)
                ->restrictOnDelete();
            foreach (
                [
                    UC::COL_USER_ID,
                    PJC::COL_APV_BY,
                    PJC::COL_REJ_BY,
                ] as $column
            )
                $table->foreign($column)
                    ->references('id')
                    ->on(DC::TABLE_USERS)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    PJC::COL_CTC_ID,
                    UC::COL_USER_ID,
                    PJC::COL_APV_BY,
                    PJC::COL_REJ_BY,
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
