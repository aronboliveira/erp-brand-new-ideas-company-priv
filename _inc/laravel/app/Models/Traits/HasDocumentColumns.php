<?php

namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;

trait HasDocumentColumns
{
	use HasNullableAuditColumns;
	protected function addDocumentColumns(Blueprint $table): void
	{
		$table->string('file_path')->nullable();
		$table->string('extension')->nullable();
		$table->string('mime_type')->nullable();
		$table->string('type')->nullable();
		$table->unsignedBigInteger('size')->nullable();
		$table->text('description')->nullable();
		$table->text('notes')->nullable();
		$table->decimal('download_count', 10, 0)->default(0);
		$table->string('permission_rules')->default('776444')->nullable(); // * FOR NOW NULLABLE -> COMPANY, ADMIN, ACCOUNTANT, CLIENT, VENDOR, USER OCTAL NOTATION
		$table->text('executors')->nullable(); // * UUIDS OF USERS WHO CAN EXECUTE
		$table->text('editors')->nullable(); // * UUIDS OF USERS ALLOWED TO EDIT
		$table->text('viewers')->nullable(); // * UUIDS OF USERS ALLOWED TO VIEW
	}
}
