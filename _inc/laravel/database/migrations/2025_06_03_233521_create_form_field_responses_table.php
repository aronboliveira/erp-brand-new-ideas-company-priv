<?php

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC, UsersConstants as UC};
use App\Enums\FieldType;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

class CreateFormFieldResponsesTable extends Migration
{
    use HasNullableAuditColumns;

    private const TABLE = DC::TABLE_FM_FLD_RSP;

    public function up(): void
    {
        try {
            Log::info("Creating table", ['table' => self::TABLE]);

            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->enum('type', array_column(FieldType::cases(), 'value'))
                    ->default(FieldType::Text->value)
                    ->nullable();
                $table->uuid(FC::COL_FM_ID)->index();
                $table->uuid(UC::COL_USER_ID)->index()->nullable();
                $table->uuid(FC::COL_FM_DT_RSP_ID)->index()->nullable();
                $table->string('name')->index()->nullable();
                $table->string(FC::COL_HTML_LB)->nullable();
                $table->string(FC::COL_HTML_ID)->nullable();
                $table->text('value')->nullable();
                $table->boolean('checked')->default(false)->nullable(); // * nullable for testing, enforced as boolean in boot/save, only for checkbox/radio type, else it's forced as null
                $table->uuid(FC::COL_NM_ID)->nullable(); // * these are uuids to link to FormFields. Not very effective, but keeps consistency with other tables
                $table->uuid(FC::COL_SUBJ_ID)->nullable();
                $table->uuid(FC::COL_EML_ID)->nullable();
                $table->uuid(FC::COL_PPL_ID)->nullable();
                $table->foreign(FC::COL_FM_ID)
                    ->references('id')
                    ->on(DC::TABLE_FORM_BUILD)
                    ->restrictOnDelete();
                foreach (
                    [
                        UC::COL_USER_ID       => DC::TABLE_USERS,
                        FC::COL_FM_DT_RSP_ID  => DC::TABLE_FORM_RSP,
                        FC::COL_NM_ID         => DC::TABLE_FM_FD,
                        FC::COL_SUBJ_ID       => DC::TABLE_FM_FD,
                        FC::COL_EML_ID        => DC::TABLE_FM_FD,
                        FC::COL_PPL_ID        => DC::TABLE_PIPELINES,
                    ] as $col => $tbl
                )
                    $table->foreign($col)
                        ->references('id')
                        ->on($tbl)
                        ->nullOnDelete();
                $this->addAuditColumns($table);
                $table->json('metadata')->nullable();
                $table->index([FC::COL_FM_ID, FC::COL_FM_DT_RSP_ID], 'idx_form_field_rsp_form_response');
            });

            Log::info("Successfully created table", ['table' => self::TABLE]);
        } catch (\Exception $e) {
            Log::error("Failed to create table", [
                'table' => self::TABLE,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function down(): void
    {
        Log::info("Starting rollback for table", ['table' => self::TABLE]);

        try {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                try {
                    $this->dropAuditColumnForeigns($table, self::TABLE);
                    Log::info("Successfully dropped audit column foreign keys", [
                        'table' => self::TABLE
                    ]);
                } catch (\Exception $e) {
                    Log::warning("Failed to drop audit column foreign keys", [
                        'table' => self::TABLE,
                        'error' => $e->getMessage()
                    ]);
                }

                $foreignKeyColumns = [
                    FC::COL_FM_ID,
                    UC::COL_USER_ID,
                    FC::COL_FM_DT_RSP_ID,
                    FC::COL_NM_ID,
                    FC::COL_SUBJ_ID,
                    FC::COL_EML_ID,
                    FC::COL_PPL_ID,
                ];

                foreach ($foreignKeyColumns as $column) {
                    $this->dropForeignKeyForColumn($table, $column);
                }
            });
            Schema::dropIfExists(self::TABLE);
        } catch (\Exception $e) {
            Log::error("Failed to rollback table", [
                'table' => self::TABLE,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Safely drop foreign key for a given column
     */
    private function dropForeignKeyForColumn(Blueprint $table, string $column): void
    {
        try {
            if (!Schema::hasColumn(self::TABLE, $column)) {
                Log::debug("Column does not exist, skipping foreign key drop", [
                    'table' => self::TABLE,
                    'column' => $column
                ]);
                return;
            }

            $foreignKeyName = $this->getForeignKeyName(self::TABLE, $column);

            if ($foreignKeyName) {
                $table->dropForeign($foreignKeyName);
                Log::debug("Successfully dropped foreign key", [
                    'table' => self::TABLE,
                    'column' => $column,
                    'constraint' => $foreignKeyName
                ]);
            } else {
                Log::debug("No foreign key found for column", [
                    'table' => self::TABLE,
                    'column' => $column
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to drop foreign key for column", [
                'table' => self::TABLE,
                'column' => $column,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the actual foreign key constraint name from the database
     */
    private function getForeignKeyName(string $table, string $column): ?string
    {
        try {
            $databaseName = DB::getDatabaseName();

            $result = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = ? 
                AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ", [$databaseName, $table, $column]);

            if (!empty($result)) {
                return $result[0]->CONSTRAINT_NAME;
            }

            return null;
        } catch (\Exception $e) {
            Log::warning("Failed to query foreign key name from database", [
                'table' => $table,
                'column' => $column,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
