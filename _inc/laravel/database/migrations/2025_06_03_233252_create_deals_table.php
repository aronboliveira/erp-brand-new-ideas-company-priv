<?php

use App\Config\Constants\{ActivitiesConstants as AC, BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\EvaluationStatus;
use App\Traits\{HasNullableAuditColumns, PipelineConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDealsTable extends Migration
{
    use HasNullableAuditColumns, PipelineConnected;
    private const TABLE = DC::TABLE_DEALS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('phone', 32)->nullable();
            $table->string('email', 254)->nullable();
            $table->decimal('price', 15, 2)->default(0.00);
            $this->addPipelineColumns($table, unique: false, nullable: true, cascade: false);
            $table->uuid(PJC::COL_STG_ID)->nullable();
            $table->integer(PJC::COL_GRP_ID)->index();
            $table->text('sources')->nullable(); // * boot/save should filter to explode and search for ids/name/title/label (check if name/title/label exists)
            $table->text('products')->nullable(); // * boot/save should wait for the ProductService to be migrated and then filter to explode and search for ids/name/title/label (check if name/title/label exists)
            $table->text('description')->nullable();
            $table->string('customer')->nullable()->index(); // ? nullable for now
            $table->text('notes')->nullable();
            $table->text('labels')->nullable();
            $table->text('permissions')->nullable();
            $table->string('status')->nullable();
            $table->enum(BC::COL_STT_LB, array_column(EvaluationStatus::cases(), 'value'))->default(EvaluationStatus::Draft)->nullable();
            $table->integer('order')->default(0);
            $table->uuid('responsible')->nullable()->index(); // ? nullable for now
            $table->uuid('supervisor')->nullable()->index(); // ? nullable for now
            $table->uuid(PJC::COL_PLN_SCHD_ID)->nullable()->index();
            $table->json('involded')->nullable();
            $table->integer(AC::COL_IA)->default(1);
            foreach (
                [
                    'responsible'                  => DC::TABLE_USERS,
                    'supervisor'                   => DC::TABLE_USERS,
                    PJC::COL_STG_ID                => DC::TABLE_STAGES,
                    PJC::COL_PLN_SCHD_ID           => DC::TABLE_PLN_SCHD,
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
            $this->dropPipelineColumnForeign($table, self::TABLE);
            foreach (
                [
                    'responsible',
                    'supervisor',
                    PJC::COL_STG_ID,
                    PJC::COL_PLN_SCHD_ID,
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
