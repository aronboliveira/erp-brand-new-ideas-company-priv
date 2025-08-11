<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Support\Facades\{Log, Schema};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

class CreateDefaultMessagesTable extends Migration
{
    private const TABLE_NAME = 'messages';
    private const COL_IDF = 'id';
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid(self::COL_IDF)->primary(); // ! CHANGED
            $table->string('type');
            $table->uuid('from_' . self::COL_IDF); // ! CHANGED
            $table->uuid('to_' . self::COL_IDF); // ! CHANGED
            $table->string('body', 5000)->nullable();
            $table->string('attachment')->nullable();
            $table->boolean('seen')->default(false);
            $table->timestamps();
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->nullable();
            // $table->primary('id'); // ? REDUNDANT
            foreach ([
                'from_' . self::COL_IDF               => DatabaseConstants::TABLE_USERS,
                'to_' . self::COL_IDF                 => DatabaseConstants::TABLE_USERS,
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
        Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
            foreach ([
                'from_' . self::COL_IDF,
                'to_' . self::COL_IDF,
                DatabaseConstants::TABLE_CREATOR,
            ] as $column) {
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
