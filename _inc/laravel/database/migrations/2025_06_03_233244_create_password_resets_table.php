<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\HasNullableAuditColumns;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePasswordResetsTable extends Migration
{
    use HasNullableAuditColumns;
    private const TABLE_NAME = 'password_resets';

    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->string('token')->unique()->primary();
            $table->string('email', 254)->index();
            $table->foreign('email')
                ->references(UC::COL_EM)
                ->on(DC::TABLE_USERS)
                ->cascadeOnDelete();
            $this->addAuditColumns($table);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            try {
                $this->dropAuditColumnForeigns($table, self::TABLE_NAME);
                foreach (['email'] as $column)
                    Schema::hasColumn(self::TABLE_NAME, $column) &&
                        Schema::table(self::TABLE_NAME, function (Blueprint $table) use ($column): void {
                            $table->dropForeign([$column]);
                        });
            } catch (\Exception $e) {
                Log::debug(
                    'Failed to drop foreign keys on table '
                        . self::TABLE_NAME
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
