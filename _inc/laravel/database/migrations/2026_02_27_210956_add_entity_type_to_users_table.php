<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds entity_type column to users table (referenced in User model $fillable as COL_ENT_TP).
     * Stores the type of entity identifier (e.g., 'cpf', 'cnpj', 'passport').
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'entity_type')) {
                $table->string('entity_type', 50)->nullable()->after('entity_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'entity_type')) {
                $table->dropColumn('entity_type');
            }
        });
    }
};
