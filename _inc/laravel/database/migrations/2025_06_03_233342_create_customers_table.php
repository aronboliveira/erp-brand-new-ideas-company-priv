<?php

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

class CreateCustomersTable extends Migration
{
    private const TABLE = DatabaseConstants::TABLE_CUSTOMERS;
    private const B = 'bill';
    private const S = 'shipping';
    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE))
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->uuid('id')->primary();                           // ! CHANGED
                $table->uuid('customer_id')->nullable();                  // ! CHANGED
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('tax_number')->nullable();                // ! matches fillable
                $table->string('contact')->nullable();
                $table->string('avatar', 100)->default('');
                $table->uuid(DatabaseConstants::TABLE_CREATOR)
                    ->default(DatabaseConstants::DEFAULT_UUID);                 // ! CHANGED
                $table->boolean('is_active')->default(true);              // ! CHANGED
                $table->timestamp('email_verified_at')->nullable();
                $table->string(self::B . '_name')->nullable();
                $table->string(self::B . '_country')->nullable();
                $table->string(self::B . '_state')->nullable();
                $table->string(self::B . '_city')->nullable();
                $table->string(self::B . '_phone')->nullable();
                $table->string(self::B . '_zip')->nullable();
                $table->text(self::B . '_address')->nullable();
                $table->string(self::S . '_name')->nullable();
                $table->string(self::S . '_country')->nullable();
                $table->string(self::S . '_state')->nullable();
                $table->string(self::S . '_city')->nullable();
                $table->string(self::S . '_phone')->nullable();
                $table->string(self::S . '_zip')->nullable();
                $table->text(self::S . '_address')->nullable();
                $table->string('lang')->default(DatabaseConstants::DEFAULT_LANG);
                $table->float('balance')->default(0.00);
                $table->rememberToken();
                $table->timestamps();
                $table->foreign(DatabaseConstants::TABLE_CREATOR)
                    ->references('id')
                    ->on(DatabaseConstants::TABLE_USERS)
                    ->onDelete('cascade');                              // * consider FK
            });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table): void {
            try {
                Schema::hasColumn(self::TABLE, DatabaseConstants::TABLE_CREATOR)
                    && $table->dropForeign([DatabaseConstants::TABLE_CREATOR]);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop foreign key for '
                        . DatabaseConstants::TABLE_CREATOR
                        . ': '
                        . $e->getMessage()
                );
            }
        });
        Schema::dropIfExists(self::TABLE);
    }
}
