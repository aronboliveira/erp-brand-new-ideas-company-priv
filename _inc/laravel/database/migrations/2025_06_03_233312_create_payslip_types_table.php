<?php

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePayslipTypesTable extends Migration
{
    private const TABLE = DC::TABLE_PAY_SLP;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->string('name')->unique()->index();
            $table->text('description')->nullable();
            $table->decimal(BC::COL_MIN_AMT, 15, 2)->default(0.00)->nullable();
            $table->decimal(BC::COL_MAX_AMT, 15, 2)->default(9999999999.99)->nullable();
            $table->text(BC::COL_RL_APL)->nullable(); // ? if null, applies to all roles; else explode and check for the UserType enum matches pairing with the UC::COL_TP
            $table->timestamps();
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            foreach (
                [
                    DC::TABLE_UPDATER    => DC::TABLE_USERS,
                    DC::TABLE_CREATOR    => DC::TABLE_USERS,
                ] as $column => $referencedTable
            )
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                foreach (
                    [
                        DC::TABLE_UPDATER,
                        DC::TABLE_CREATOR,
                    ] as $column
                ) {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                }
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DC::TABLE_CREATOR
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
