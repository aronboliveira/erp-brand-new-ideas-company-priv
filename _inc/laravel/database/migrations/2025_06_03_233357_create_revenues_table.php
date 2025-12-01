<?php

use App\Config\Constants\{DatabaseConstants as DC};
use App\Traits\{CustomerConnected, HasNullableAuditColumns, HasPaymentColumns, HasPaymentConclusionColumns};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateRevenuesTable extends Migration
{
    use CustomerConnected, HasNullableAuditColumns, HasPaymentColumns, HasPaymentConclusionColumns;
    private const TABLE = DC::TABLE_RVN;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('company')->index()->nullable(); // ? nullable for testing purposes
            $table->uuid('user')->index()->nullable(); // ? nullable for testing purposes
            $this->addCustomerColumns($table, nullable: false, unique: false, onDelete: 'restrict');
            $this->addPaymentColumns($table);
            $this->addPaymentConclusionColumns($table, nullableAcc: false, nullableCat: true, onDeleteAcc: 'restrict', onDeleteCat: 'set null');
            $this->addAuditColumns($table);
            foreach (
                [
                    'company' => DC::TABLE_USERS,
                    'user'    => DC::TABLE_USERS,
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
            $this->dropCustomerColumnForeigns($table, self::TABLE);
            $this->dropPaymentColumnForeigns($table, self::TABLE);
            $this->dropPaymentConclusionColumnForeigns($table, self::TABLE);
            $this->dropAuditColumnForeigns($table, self::TABLE);
            foreach (
                [
                    'company',
                    'user',
                ] as $col
            ) {
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
