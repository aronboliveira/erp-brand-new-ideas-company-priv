<?php

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\CallType;
use App\Traits\{HasNullableAuditColumns, LeadConnected};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateLeadCallsTable extends Migration
{
    use HasNullableAuditColumns, LeadConnected;
    private const TABLE = DC::TABLE_LD_CALLS;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(UC::COL_USER_ID)->index();
            $table->string('from', 254)->index(); // * boot/save should normalize as email address or phone number
            $table->uuid(AC::COL_TO_ID, 254)->index()->nullable();
            $table->string('to', 254)->index(); // * boot/save should normalize as email address or phone number
            $table->uuid(AC::COL_FRM_ID)->index()->nullable();
            $this->addLeadColumns($table);
            $table->string('subject')->index();
            $table->enum(AC::COL_CL_TP, CallType::values())->default(CallType::Other->value)->index();
            $table->dateTime(AC::COL_CL_DT)->nullable();
            $table->time(AC::COL_CL_DUR)->nullable();
            $table->string('duration', 20); // * it's not clear why this is a string in legacy code, so keeping it like that for now // ? convert to HH:MM:SS if numeric or accept straight away as string if matching HH:MM:SS format [at boot/saving]
            $table->text('description')->nullable();
            $table->text(AC::COL_CL_RS)->nullable();
            $table->text('notes')->nullable();
            $table->foreign(UC::COL_USER_ID)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->cascadeOnDelete();
            foreach (
                [
                    AC::COL_FRM_ID => DC::TABLE_USERS,
                    AC::COL_TO_ID  => DC::TABLE_USERS,
                ] as $col => $refTable
            )
                $table->foreign($col)
                    ->references('id')
                    ->on($refTable)
                    ->nullOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropLeadColumnForeign($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    UC::COL_USER_ID,
                    AC::COL_FRM_ID,
                    AC::COL_TO_ID,
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
