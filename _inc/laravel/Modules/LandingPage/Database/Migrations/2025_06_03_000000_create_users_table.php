<?php

use App\Config\Constants\{DatabaseConstants, UsersConstants};
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    private const TABLE_NAME = DatabaseConstants::TABLE_USERS;
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED: switch to UUID primary key
            $table->string(UsersConstants::COL_NM)->nullable();
            $table->string(UsersConstants::COL_EM)->unique();
            $table->timestamp(UsersConstants::COL_EM_V_AT)->nullable();
            $table->string(UsersConstants::COL_PW)->nullable();
            $table->date(UsersConstants::COL_PED)->nullable();
            $table->string(UsersConstants::COL_TP, 100)->nullable();
            $table->float(UsersConstants::COL_SL)->default(0.00);
            $table->string(UsersConstants::COL_AV)->default(config('chatify.user_avatar.default'));
            $table->string(UsersConstants::COL_MC)->default('#2180f3'); // * NOT IN MODEL FILLABLE
            $table->string(UsersConstants::COL_LG, 100)->default(DatabaseConstants::DEFAULT_LANG);
            $table->boolean(UsersConstants::COL_A_ST)->default(0);        // * NOT IN MODEL FILLABLE
            $table->integer(UsersConstants::COL_D_ST)->default(1);
            $table->string(UsersConstants::COL_MD, 10)->default('light');
            $table->boolean(UsersConstants::COL_DM)->default(0);            // * NOT IN MODEL FILLABLE
            $table->integer(UsersConstants::COL_IA)->default(1);
            $table->integer(UsersConstants::COL_IB)->default(0);
            $table->datetime(UsersConstants::COL_LLA)->nullable();
            $table->uuid(UsersConstants::COL_DPL)->default(DatabaseConstants::DEFAULT_PIPELINE);
            $table->uuid(UsersConstants::COL_PL)->default(DatabaseConstants::DEFAULT_PLAN);
            $table->uuid(UsersConstants::COL_RP)->default(DatabaseConstants::DEFAULT_PLAN);       // * ADDED
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)->default(DatabaseConstants::DEFAULT_UUID);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
