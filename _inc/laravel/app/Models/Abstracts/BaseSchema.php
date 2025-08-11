<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};
use Illuminate\Support\Facades\Schema;

abstract class BaseSchema extends Migration
{

	public function up(): void
	{
		Schema::table($this->tableName(), function (Blueprint $table): void {
			$this->addFields($table);
		});
	}

	public function down(): void
	{
		Schema::table($this->tableName(), function (Blueprint $table): void {
			$this->dropFields($table);
		});
	}

	abstract protected function tableName(): string;
	abstract protected function addFields(Blueprint $table): void;
	abstract protected function dropFields(Blueprint $table): void;
}
