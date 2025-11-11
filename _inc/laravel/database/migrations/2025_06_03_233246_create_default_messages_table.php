<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Support\Facades\{Log, Schema};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

class CreateDefaultMessagesTable extends Migration
{
    private const TABLE_NAME = 'messages';
    private const COL_IDF = 'id';
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid(self::COL_IDF)->primary();
            $table->string('type');
            $table->uuid('from_' . self::COL_IDF)->nullable()->index();
            $table->uuid('to_' . self::COL_IDF)->nullable()->index();
            $table->text('body')->nullable();
            $table->string('attachment')->nullable();
            $table->boolean('seen')->default(false);
            $table->timestamps();
            $table->uuid(DC::TABLE_CREATOR)->nullable();
            $table->uuid(DC::TABLE_UPDATER)->nullable();
            // $table->primary('id'); // ? REDUNDANT
            foreach (
                [
                    'from_' . self::COL_IDF               => DC::TABLE_USERS,
                    'to_' . self::COL_IDF                 => DC::TABLE_USERS,
                    DC::TABLE_CREATOR      => DC::TABLE_USERS,
                    DC::TABLE_UPDATER      => DC::TABLE_USERS,
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
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            foreach (
                [
                    'from_' . self::COL_IDF,
                    'to_' . self::COL_IDF,
                    DC::TABLE_CREATOR,
                    DC::TABLE_UPDATER,
                ] as $column
            ) {
                try {
                    Schema::hasColumn(self::TABLE_NAME, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to drop foreign key for '
                            . $column
                            . ' on table '
                            . self::TABLE_NAME
                            . ': '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
