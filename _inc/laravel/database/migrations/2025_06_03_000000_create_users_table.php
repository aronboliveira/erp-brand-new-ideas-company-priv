<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\HasBasicUserLikeColumns;
use App\Enums\UserType;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    use HasBasicUserLikeColumns;
    private const TABLE_NAME = DC::TABLE_USERS;
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $this->addUserLikeColumns($table, defaultAvatar: 'chatify.user_avatar.default', hasPassword: true);
            $table->string('phone')->nullable()->unique();
            $table->text('address')->nullable();
            $table->uuid(UC::COL_EMP_ID)->nullable()->index();
            $table->enum(UC::COL_TP, UserType::values())->default(UserType::Client)->nullable();
            $table->float(UC::COL_SL)->default(1024.00);
            $table->string(UC::COL_MC)->default('#2180f3');
            $table->boolean(UC::COL_A_ST)->default(0);
            $table->integer(UC::COL_D_ST)->default(1);
            $table->string(UC::COL_MD, 10)->default('light');
            $table->boolean(UC::COL_DM)->default(0);
            $table->integer(UC::COL_IB)->default(0);
            $table->datetime(UC::COL_LLA)->nullable();
            $table->uuid(UC::COL_DPL)->default(DC::DEFAULT_PIPELINE); // * TOO ABSTRACT
            $table->uuid(UC::COL_RP)->default(DC::DEFAULT_PLAN);
            $table->uuid(UC::COL_PL)->default(DC::DEFAULT_PLAN);
            $table->date(UC::COL_PED)->nullable();
            $table->uuid(DC::COL_TABLE_CREATOR)->default(DC::DEFAULT_UUID);
            $table->uuid(DC::COL_TABLE_UPDATER)->default(DC::DEFAULT_UUID)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
