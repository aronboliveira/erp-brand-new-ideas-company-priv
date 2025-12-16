<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{AppModuleType, EvaluationStatus, MimeType};
use App\Traits\{HasNullableAuditColumns, TracksFailures};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDocumentUploadsTable extends Migration
{
    use HasNullableAuditColumns, TracksFailures;
    private const TABLE = DC::TABLE_DOC_UP;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index(); // * this was never made specific in legacy, so we keep it generic. It may represent the name of the document, or the name of the user uploading the document
            $table->string('role'); // * this was never made specific in legacy, so we keep it generic. It may represent the role of the user uploading the document, or the role of the document itself
            $table->text('description')->nullable();
            $table->string('document'); // * this was never made specific in legacy, so we keep it generic. It may represent the path to the document, or the document name or even the uuid to query into a Document row
            $table->enum('module', array_column(AppModuleType::cases(), 'value'))->default(AppModuleType::Other->value)->nullable(); // ? nullable for tests, enforced as default at boot/save
            $table->enum('type', array_column(MimeType::cases(), 'value'))->default(MimeType::OTHER->value)->nullable(); // ? nullable for tests, enforced as default at boot/save, filtered with isDocument from the MimeType enum, falling back to OTHER
            $table->enum('status', array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::Pending->value)->nullable(); // ? nullable for tests, enforced as default at boot/save
            $table->unsignedDecimal('progress', 5, 2)->default(0.00)->nullable(); // ? nullable for tests, enforced as default at boot/save, constrained between 0 and 100
            $table->unsignedDecimal(DC::COL_RQ_SPC, 20, 2)->default(0.00); // * in bytes
            $table->string(DC::COL_OBJ_URL)->nullable(); // ? nullable for tests
            $table->char('sha256', 64)->nullable()->index();
            $table->char('md5', 32)->nullable()->index();
            $table->boolean(DC::COL_IS_ENC)->default(false)->nullable(); // ? nullable for tests, enforced as default at boot/save
            $table->string(DC::COL_ENC_ALG, 64)->nullable(); // ? nullable for tests
            $table->boolean(DC::COL_MW_FREE)->default(true)->nullable(); // ? nullable for tests, enforced as default at boot/save
            $table->uuid(DC::COL_DOC_ID)->nullable()->index(); // ? nullable for tests
            $table->uuid(UC::COL_USER_ID)->nullable()->index(); // ? nullable for tests // ? the uploader
            $table->foreign(DC::COL_DOC_ID)
                ->references('id')
                ->on(DC::TABLE_DOCS)
                ->nullOnDelete(); // * in production, if the linked document does not exist, this should cascade
            $table->foreign(UC::COL_USER_ID)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->nullOnDelete();
            $this->addAuditColumns($table);
            $this->addFailureTrackingColumns($table);
            $table->json('policy')->nullable();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            $table->json(DC::COL_MW_SCAN)->nullable(); // * malware scan metadata like timestamps and log paths
        });
    }
    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    DC::COL_DOC_ID,
                    UC::COL_USER_ID,
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
