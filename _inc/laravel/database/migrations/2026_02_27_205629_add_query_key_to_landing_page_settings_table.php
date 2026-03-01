<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('landing_page_settings', 'query_key')) {
                $table->uuid('query_key')->nullable()->unique()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            if (Schema::hasColumn('landing_page_settings', 'query_key')) {
                $table->dropUnique(['query_key']);
                $table->dropColumn('query_key');
            }
        });
    }
};
