<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

class CreateZoomMeetingsTable extends Migration
{
    private const TABLE = 'zoom_meetings';
    private const U = 'url';
    private const COL_PROJ = 'project_id';
    private const COL_USER = 'user_id';
    private const COL_CLIENT = 'client_id';
    private const COL_MEETING = 'meeting_id';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->uuid('id')->primary();                // ! CHANGED
            $table->string('title')->nullable();
            $table->uuid(self::COL_MEETING)->default(DatabaseConstants::DEFAULT_UUID); // ! CHANGED
            $table->uuid(self::COL_PROJ);                   // ! CHANGED
            $table->uuid(self::COL_USER)->nullable(); // ! CHANGED
            $table->uuid(self::COL_CLIENT);                    // ! CHANGED
            $table->string('password')->nullable();
            $table->timestamp('start_date')
                ->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->integer('duration')->default(0);
            $table->text('start_' . self::U)->nullable();
            $table->string('join_' . self::U)->nullable();
            $table->string('status')->default('waiting')->nullable();
            $table->uuid(DatabaseConstants::TABLE_CREATOR);                   // ! CHANGED
            $table->timestamps();
            foreach ([
                self::COL_MEETING              => DatabaseConstants::TABLE_MEETINGS,
                self::COL_PROJ                 => DatabaseConstants::TABLE_PROJECTS,
                self::COL_USER                 => DatabaseConstants::TABLE_USERS,
                self::COL_CLIENT               => DatabaseConstants::TABLE_USERS,
                DatabaseConstants::TABLE_CREATOR => DatabaseConstants::TABLE_USERS,
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
                self::COL_MEETING,
                self::COL_PROJ,
                self::COL_USER,
                self::COL_CLIENT,
                DatabaseConstants::TABLE_CREATOR,
            ] as $column) {
                try {
                    Schema::hasColumn(self::TABLE, $column)
                        && $table->dropForeign([$column]);
                } catch (\Exception $e) {
                    Log::warning(
                        'Failed to execute down for '
                            . $column
                            . ' foreign key column: '
                            . $e->getMessage()
                    );
                }
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
