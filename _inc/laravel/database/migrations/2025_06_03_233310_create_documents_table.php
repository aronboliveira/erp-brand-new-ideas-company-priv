<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateDocumentsTable extends Migration
{
    private const TABLE = DC::TABLE_DOCS;

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('is_required')->default('false');
                $table->boolean('is_private')->default(false)->nullable();
                $table->string('file_path')->nullable();
                $table->string('extension')->nullable();
                $table->string('mime_type')->nullable();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->text('description')->nullable();
                $table->text('notes')->nullable();
                $table->uuid(DC::TABLE_CREATOR)->nullable();
                $table->uuid(DC::TABLE_UPDATER)->nullable();
                $table->timestamps();
                $table->date('expiration_date')->nullable();
                $table->date('last_accessed')->nullable();
                $table->decimal('download_count', 10, 0)->default(0);
                $table->string('permission_rules')->default('776444')->nullable(); // * FOR NOW NULLABLE -> COMPANY, ADMIN, ACCOUNTANT, CLIENT, VENDOR, USER OCTAL NOTATION
                $table->text('executors')->nullable(); // * UUIDS OF USERS WHO CAN EXECUTE
                $table->text('editors')->nullable(); // * UUIDS OF USERS ALLOWED TO EDIT
                $table->text('viewers')->nullable(); // * UUIDS OF USERS ALLOWED TO VIEW
                foreach ([DC::TABLE_CREATOR, DC::TABLE_UPDATER] as $col)
                    $table->foreign($col)
                        ->references('id')
                        ->on(DC::TABLE_USERS)
                        ->nullOnDelete();
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                foreach ([DC::TABLE_CREATOR, DC::TABLE_UPDATER] as $col)
                    Schema::hasColumn(self::TABLE, $col)
                        && $table->dropForeign([$col]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DC::TABLE_CREATOR
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
