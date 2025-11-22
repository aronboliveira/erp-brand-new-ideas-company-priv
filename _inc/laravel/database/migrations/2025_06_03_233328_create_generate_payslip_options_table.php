<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

// TODO VERIFICAR ESSE MODELO
class CreateGeneratePayslipOptionsTable extends Migration
{
    private const TABLE_NAME = 'generate_payslip_options';
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            $table->foreign(DatabaseConstants::TABLE_CREATOR)
                ->references('id')
                ->on(DatabaseConstants::TABLE_USERS)
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE_NAME, DatabaseConstants::TABLE_CREATOR) &&
                    $table->dropForeign([DatabaseConstants::TABLE_CREATOR]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DatabaseConstants::TABLE_CREATOR
                        . ' on table '
                        . self::TABLE_NAME
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
