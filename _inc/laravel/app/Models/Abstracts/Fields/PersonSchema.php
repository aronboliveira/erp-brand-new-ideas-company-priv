<?php

namespace App\Models;

use Illuminate\Database\Schema\Blueprint;

abstract class PersonSchema extends DeliverableSchema
{
	protected function addFields(Blueprint $table): void
	{
		parent::addFields($table);
		$table->string('gender', 20)->nullable();
		$table->date('dob')->nullable();
	}

	protected function dropFields(Blueprint $table): void
	{
		parent::dropFields($table);
		$table->dropColumn(['gender', 'dob']);
	}
}
