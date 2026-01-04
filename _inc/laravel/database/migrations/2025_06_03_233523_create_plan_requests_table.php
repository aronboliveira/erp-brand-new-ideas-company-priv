<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\Frequency;
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

// todo this will be changed only after decisions by the product team
class CreatePlanRequestsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE = DC::TABLE_PLAN_REQUESTS;

    public function up(): void
    {
        if (!Schema::hasColumn(DC::TABLE_USERS, UC::COL_RQ_PLN)) {
            Schema::table(DC::TABLE_USERS, function (Blueprint $table): void {
                $table->uuid(UC::COL_RQ_PLN)
                    ->nullable()
                    ->after('plan_expire_date');
                $table->foreign(UC::COL_RQ_PLN)
                    ->references('id')
                    ->on(DC::TABLE_PLANS)
                    ->onDelete('set null');
            });
        }
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid(UC::COL_USER_ID)->index();
            $table->uuid(UC::COL_PLAN_ID)->index();
            $table->enum('duration', array_column(Frequency::cases(), 'value'))->default(Frequency::Monthly->value);
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();
            foreach (
                [
                    UC::COL_USER_ID                  => DC::TABLE_USERS,
                    UC::COL_PLAN_ID                  => DC::TABLE_PLANS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->onDelete($column === UC::COL_USER_ID || $column === UC::COL_PLAN_ID ? 'cascade' : 'set null');
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    UC::COL_USER_ID,
                    UC::COL_PLAN_ID,
                ] as $col
            ) {
                try {
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $col
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
