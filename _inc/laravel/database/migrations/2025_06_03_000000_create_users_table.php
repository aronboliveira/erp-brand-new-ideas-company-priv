<?php

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\HasBasicUserLikeColumns;
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
            $table->string(UC::COL_TP, 100)->nullable();
            $table->float(UC::COL_SL)->default(1024.00);
            $table->string(UC::COL_MC)->default('#2180f3'); // * NOT IN MODEL FILLABLE
            $table->boolean(UC::COL_A_ST)->default(0);        // * NOT IN MODEL FILLABLE
            $table->integer(UC::COL_D_ST)->default(1);
            $table->string(UC::COL_MD, 10)->default('light');
            $table->boolean(UC::COL_DM)->default(0);            // * NOT IN MODEL FILLABLE
            $table->integer(UC::COL_IB)->default(0);
            $table->datetime(UC::COL_LLA)->nullable();
            $table->uuid(UC::COL_DPL)->default(DC::DEFAULT_PIPELINE); // * TOO ABSTRACT
            $table->uuid(UC::COL_RP)->default(DC::DEFAULT_PLAN);       // * ADDED
            $table->uuid(UC::COL_PL)->default(DC::DEFAULT_PLAN);
            $table->date(UC::COL_PED)->nullable();
            $table->uuid(DC::COL_TABLE_CREATOR)->default(DC::DEFAULT_UUID);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
