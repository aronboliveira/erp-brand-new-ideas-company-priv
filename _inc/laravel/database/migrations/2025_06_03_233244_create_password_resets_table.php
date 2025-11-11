<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreatePasswordResetsTable extends Migration
{
    private const TABLE_NAME = 'password_resets';

    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->string('token')->unique()->primary();
            $table->string('email')->index();
            $table->timestamp('created_at')->nullable();
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->foreign('email')
                ->references(UC::COL_EM)
                ->on(DC::TABLE_USERS)
                ->cascadeOnDelete();
            $table->foreign(DC::TABLE_CREATOR)
                ->references('id')
                ->on(DC::TABLE_USERS)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        try {
            foreach ([DC::TABLE_CREATOR, 'email'] as $column)
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
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
