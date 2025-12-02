<?php

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Traits\{HasBasicUserLikeColumns, HasNullableAuditColumns, PipelineConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLeadsTable extends Migration
{
    use HasBasicUserLikeColumns, HasNullableAuditColumns, PipelineConnected;
    private const TABLE = DC::TABLE_LEADS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $this->addUserLikeColumns($table, nullableName: true);
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->boolean(PJC::COL_CRT)->default(false)->nullable();
            $table->uuid(UC::COL_USER_ID)->index()->nullable(); // ? not every lead is assigned to a user
            $this->addPipelineColumns($table, nullable: true, cascade: false);
            $table->uuid(PJC::COL_STG_ID)->nullable();
            $table->string('sources')->nullable(); // ? list of uuids, keys or names for querying into Source
            $table->string('products')->nullable(); // ? list of uuids, keys or names for querying into ProductService
            $table->string('labels')->nullable(); // ? list of uuids, keys or names for querying into Label
            $table->integer('order')->default(0);
            $table->text('notes')->nullable();
            $table->integer(PJC::COL_CNV)->default(0);
            $table->date('date')->nullable(); // * it's not clear what this deat is about, but might be the day of response and follow-up
            $table->uuid('caller')->nullable();
            $table->json('involved')->nullable(); // ? list of uuids of users + employees (filtering redundant on boot/save) involved with the lead
            foreach (
                [
                    UC::COL_USER_ID => DC::TABLE_USERS,
                    'caller' => DC::TABLE_EMPLOYEES,
                    PJC::COL_STG_ID => DC::TABLE_LEAD_STAGES,
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
                    UC::COL_USER_ID,
                    'caller',
                    PJC::COL_STG_ID,
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
