<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateBankTransfersTable extends Migration
{
    private const TABLE = 'bank_transfers';
    private const AC = '_account';
    private const FROM_AC = 'from' . self::AC;
    private const TO_AC = 'to' . self::AC;
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary();            // ! CHANGED
            $table->uuid(self::FROM_AC);             // ! CHANGED
            $table->uuid(self::TO_AC);               // ! CHANGED
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('date');
            $table->integer('payment_method')->default(0);
            $table->string('reference')->nullable();
            $table->text('description');
            $table->uuid(DatabaseConstants::TABLE_CREATOR);               // ! CHANGED
            $table->timestamps();
            foreach ([
                self::FROM_AC                         => DatabaseConstants::TABLE_BANK_ACC,
                self::TO_AC                           => DatabaseConstants::TABLE_BANK_ACC,
                DatabaseConstants::TABLE_CREATOR      => DatabaseConstants::TABLE_USERS,
            ] as $column => $referencedTable)
                $table->foreign($column)
                    ->references('id')
                    ->on($referencedTable)
                    ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            foreach ([
                self::FROM_AC,
                self::TO_AC,
                DatabaseConstants::TABLE_CREATOR,
            ] as $col) {
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
