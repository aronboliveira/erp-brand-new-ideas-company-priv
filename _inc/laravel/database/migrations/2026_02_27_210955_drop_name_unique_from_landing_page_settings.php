<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * The original migration defined query_key as the unique key, not name.
     * The name column allows multiple rows per key (e.g., menubar_page can have many items).
     */
    public function up(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            // Drop incorrect unique constraint on name — multiple rows can share the same name
            // (e.g., multiple menubar_page entries, each with a distinct query_key)
            if (collect(\Illuminate\Support\Facades\Schema::getIndexListing('landing_page_settings'))
                ->contains('landing_page_settings_name_unique')) {
                $table->dropUnique(['name']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_page_settings', function (Blueprint $table) {
            if (!collect(\Illuminate\Support\Facades\Schema::getIndexListing('landing_page_settings'))
                ->contains('landing_page_settings_name_unique')) {
                $table->unique('name');
            }
        });
    }
};
