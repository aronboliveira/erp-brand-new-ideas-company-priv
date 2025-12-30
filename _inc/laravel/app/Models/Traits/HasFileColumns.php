<?php

namespace App\Traits;

use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\MimeType;
use Illuminate\Database\Schema\Blueprint;

trait HasFileColumns
{
	protected function addFileColumns(Blueprint $table): void
	{
		$table->string(DC::COL_FL_PT)->nullable();
		$table->string('url', 254)->nullable()->index();
		$table->string('name', 1024)->nullable()->index(); // ? it null, then generate randomly as "FILE_{Str::UUID}_{timestamp}"
		$table->string('extension')->nullable()->index();
		$table->enum(DC::COL_MM_TP, array_column(MimeType::cases(), 'value'))->default(MimeType::OTHER->value)->nullable(); // * boot/saving should be constrained by MimeType enum
		$table->timestamp(DC::COL_LA)->nullable();
		$table->unsignedBigInteger('size')->nullable();
		$table->text('description')->nullable();
		$table->text('notes')->nullable();
		$table->decimal(DC::COL_DL_CT, 10, 0)->default(0)->nullable();
		$table->decimal(DC::COL_FL_SZ, 16, 4)->default(0.0000)->nullable(); // * file size in bytes, nullable for testing purposes
		$table->string(DC::COL_PERM_RLS)->default('776444')->nullable(); // * FOR NOW NULLABLE -> COMPANY, ADMIN, ACCOUNTANT, CLIENT, VENDOR, USER OCTAL NOTATION
		$table->text('executors')->nullable(); // * UUIDS OF USERS WHO CAN EXECUTE
		$table->text('editors')->nullable(); // * UUIDS OF USERS ALLOWED TO EDIT
		$table->text('viewers')->nullable(); // * UUIDS OF USERS ALLOWED TO VIEW
		$table->datetime(DC::COL_EXP_DT)->nullable();
		$table->string('type')->nullable(); // ? boot/saving should be constrained by DocumentKind enum, nullified when not a document through the isDocument method
	}
}
