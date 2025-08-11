<?php

use App\Config\Constants\{DatabaseConstants, ProjectsConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateContractsTable extends Migration
{
    private const TABLE = 'contracts';
    private const COL_CLIENT = 'client_id';
    private const COL_CONTRACT_TYPE = 'type';
    private const COL_PROJECT = ProjectsConstants::COL_PJ_ID;
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                          // ! CHANGED
                $table->uuid(self::COL_CLIENT);                            // ! CHANGED
                // CNPJ DAS PARTES
                // RESPONSÁVEIS
                $table->string('subject')->nullable();
                $table->string('value')->nullable();
                $table->uuid(self::COL_CONTRACT_TYPE);                                    // ! CHANGED
                $table->date('start_date');
                $table->date('end_date');
                $table->string('description')->nullable();
                $table->uuid(self::COL_PROJECT)->nullable();                  // ! CHANGED
                $table->text('contract_description')->nullable();
                $table->string('status')->default('pending');
                $table->longText('client_signature')->nullable();
                $table->longText('company_signature')->nullable();
                // TIPO DE CONTRATO
                $table->uuid(DatabaseConstants::TABLE_CREATOR);                              // ! CHANGED
                $table->timestamps();
                foreach ([
                    self::COL_CLIENT           => DatabaseConstants::TABLE_USERS,
                    self::COL_CONTRACT_TYPE    => DatabaseConstants::TABLE_CONTRACT_TYPES,
                    self::COL_PROJECT          => DatabaseConstants::TABLE_PROJECTS,
                    DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
                ] as $column => $referencedTable)
                    $table->foreign($column)
                        ->references('id')
                        ->on($referencedTable)
                        ->onDelete('cascade');
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::COL_CLIENT,
                self::COL_CONTRACT_TYPE,
                self::COL_PROJECT,
                DatabaseConstants::TABLE_CREATOR,
            ] as $col) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
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
