<?php

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC, ProjectsConstants as PJC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateFormResponsesTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_FORM_RSP;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(FC::COL_FM_ID)->index();
            $table->timestamp(PJC::COL_SBM_AT)->nullable(); // ? should be constrained to reject the submissions according to the rate limit set in the linked COL_FM_ID
            $table->uuid(PJC::COL_SBM_BY)->nullable()->index(); // ? if FC::COL_LMT_ONE_PRSN == true in the linked COL_FM_ID, then this should be unique per form
            $table->string(FC::COL_SBM_EML)->nullable(); // ? if the COL_SBM_BY correctly references a row id in DC::TABLE_USERS, then this should be set as the 'email' of the user, if not null. If FC::COL_LMT_ONE_PRSN == true in the linked COL_FM_ID, then this should be unique per form
            $table->ipAddress(FC::COL_SBM_IP)->nullable();
            $table->string(FC::COL_SBM_UA)->nullable();
            $table->string(FC::COL_SBM_URL)->nullable(); // ? where this came from, if available
            $table->text('response')->nullable(); // * this should represent the sanitized and stringified json of responses, ideally. Kept as such for legacy
            $table->enum('status', ['pending', 'approved', 'rejected', 'archived'])->default('pending')->nullable();
            $table->boolean(FC::COL_CAPTCHA_APV)->default(false)->nullable();
            $table->boolean(FC::COL_CST_CHK)->default(false)->nullable();
            $table->boolean(FC::COl_CSRF_TKN_APV)->default(false)->nullable();
            $table->boolean(DC::COL_MW_FREE)->default(true)->nullable(); // ? is_malware_free, set after scanning the response for malware in product; for tests, defaulted to true
            $table->dateTime(FC::COL_EXP_AT)->default(now()->addDays(730)->format('Y-m-d H:i:s'))->nullable();
            $table->foreign(FC::COL_FM_ID)
                ->references('id')
                ->on(DC::TABLE_FORM_BUILD)
                ->restrictOnDelete();
            foreach (
                [
                    PJC::COL_SBM_BY => DC::TABLE_USERS,
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
            $table->json('lakes')->nullable();
            $table->json('edits')->nullable();
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach ([FC::COL_FM_ID] as $column) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for ' . $column
                            . ' on table ' . self::TABLE . ': ' . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
