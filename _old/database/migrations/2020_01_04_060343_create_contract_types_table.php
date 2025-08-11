<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractTypesTable extends Migration
{
    public function up(): void
    {
        Schema::create('contract_types', function (Blueprint $table) {
            $table->uuid('id')->primary();            // ! CHANGED
            $table->string('name');
            $table->uuid(DatabaseConstants::TABLE_CREATOR)->index();      // ! CHANGED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_types');
    }
}
