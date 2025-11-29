<?php

namespace App\Traits;

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\Schema\Blueprint;

trait HasDocumentColumns
{
	use HasNullableAuditColumns;
	protected function addDocumentColumns(Blueprint $table): void
	{
		$table->string(DC::COL_FL_PT)->nullable();
		$table->string('extension')->nullable();
		$table->string(DC::COL_MM_TP)->nullable();
		$table->timestamp(DC::COL_LA)->nullable();
		$table->string('type')->nullable();
		$table->unsignedBigInteger('size')->nullable();
		$table->text('description')->nullable();
		$table->text('notes')->nullable();
		$table->decimal(DC::COL_DL_TP, 10, 0)->default(0);
		$table->datetime(DC::COL_EXP_DT)->nullable();
		$table->string(DC::COL_PERM_RLS)->default('776444')->nullable(); // * FOR NOW NULLABLE -> COMPANY, ADMIN, ACCOUNTANT, CLIENT, VENDOR, USER OCTAL NOTATION
		$table->text('executors')->nullable(); // * UUIDS OF USERS WHO CAN EXECUTE
		$table->text('editors')->nullable(); // * UUIDS OF USERS ALLOWED TO EDIT
		$table->text('viewers')->nullable(); // * UUIDS OF USERS ALLOWED TO VIEW
	}
}
