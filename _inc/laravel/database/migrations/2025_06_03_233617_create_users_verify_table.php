<?php

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

// todo this will change after meeting with DevOps and Sec team
class CreateUsersVerifyTable extends Migration
{
    private const TABLE = DC::TABLE_USR_VRF;
    private const TABLE_USERS = 'users';
    private const COL_VERIFIED = 'is_email_verified';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('token');
            $table->timestamps();
        });
        Schema::table(self::TABLE_USERS, function (Blueprint $table) {
            $table->boolean(self::COL_VERIFIED)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE_USERS, function (Blueprint $table): void {
            try {
                if (Schema::hasColumn(self::TABLE_USERS, self::COL_VERIFIED))
                    $table->dropColumn(self::COL_VERIFIED);
            } catch (\Exception $e) {
                Log::warning(
                    'Failed to drop column ' . self::COL_VERIFIED . ' from users table: '
                        . $e->getMessage()
                );
            }
        });

        Schema::dropIfExists(self::TABLE);
    }
};
