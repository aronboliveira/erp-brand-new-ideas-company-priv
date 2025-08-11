<?php

use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;

class CreateBasicFavoritesTable extends Migration
{

    private const TABLE = 'basic_favorites';
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->uuid('user_id'); // ! CHANGED
            $table->uuid('favorite_id'); // ! CHANGED
            $table->timestamps();
            // $table->primary('id'); // ? REDUNDANT
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
}
