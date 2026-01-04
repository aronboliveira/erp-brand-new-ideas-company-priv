<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateFormFieldResponsesTable extends Migration
{
    // todo 
    private const TABLE = 'form_field_responses';
    private const COL_FORM = 'form_id';
    private const COL_SUBJ = 'subject_id';
    private const COL_NAME = 'name_id';
    private const COL_EMAIL = 'email_id';
    private const COL_USER = 'user_id';
    private const COL_PL = 'pipeline_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid(self::COL_FORM);
            $table->uuid(self::COL_SUBJ);
            $table->uuid(self::COL_NAME);
            $table->uuid(self::COL_EMAIL);
            $table->uuid(self::COL_USER);
            $table->uuid(self::COL_PL);
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->nullable();
            $table->timestamps();
            foreach (
                [
                    self::COL_FORM  => DatabaseConstants::TABLE_FORM_BUILD,
                    self::COL_SUBJ  => DatabaseConstants::TABLE_FORM_FIELDS,
                    self::COL_NAME  => DatabaseConstants::TABLE_FORM_FIELDS,
                    self::COL_EMAIL => DatabaseConstants::TABLE_FORM_FIELDS,
                    self::COL_USER  => DatabaseConstants::TABLE_USERS,
                    self::COL_PL    => DatabaseConstants::TABLE_PIPELINES,
                    DatabaseConstants::COL_TABLE_CREATOR => DatabaseConstants::TABLE_USERS
                ] as $col => $tbl
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($tbl)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach (
                [
                    self::COL_FORM,
                    self::COL_SUBJ,
                    self::COL_NAME,
                    self::COL_EMAIL,
                    self::COL_USER,
                    self::COL_PL,
                    DatabaseConstants::COL_TABLE_CREATOR
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
        Schema::dropIfExists(self::TABLE);
    }
}
