<?php

namespace App\Traits;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Enums\CallType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait IsBusinessContact
{
	protected function addBasicBusinessContactColumns(Blueprint $table, bool $nullableUser = false, bool $cascade = true): void
	{
		$nullableUser ? $table->uuid(UC::COL_USER_ID)->index()->nullable() : $table->uuid(UC::COL_USER_ID)->index()->nullable();
		$table->string('from', 254)->index(); // * boot/save should normalize as email address or phone number
		$table->uuid(AC::COL_TO_ID, 254)->index()->nullable();
		$table->string('to', 254)->index(); // * boot/save should normalize as email address or phone number
		$table->uuid(AC::COL_FRM_ID)->index()->nullable();
		$table->string('subject', 255)->index();
		$table->text('description')->nullable();
		$table->text('notes')->nullable();
		$nullableUser ? $table->foreign(UC::COL_USER_ID)
			->references('id')
			->on(DC::TABLE_USERS)
			->nullOnDelete() : ($cascade ? $table->foreign(UC::COL_USER_ID)->references('id')->on(DC::TABLE_USERS)->cascadeOnDelete() : $table->foreign(UC::COL_USER_ID)->references('id')->on(DC::TABLE_USERS)->restrictOnDelete());
		foreach (
			[
				AC::COL_FRM_ID => DC::TABLE_USERS,
				AC::COL_TO_ID  => DC::TABLE_USERS,
			] as $col => $refTable
		)
			$table->foreign($col)
				->references('id')
				->on($refTable)
				->nullOnDelete();
	}
	protected function dropBasicBusinessContactColumnForeigns(Blueprint $table): void
	{
		foreach (
			[
				UC::COL_USER_ID,
				AC::COL_FRM_ID,
				AC::COL_TO_ID,
			] as $col
		) {
			try {
				Schema::hasColumn($table->getTable(), $col)
					&&
					$table->dropForeign([$col]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to drop foreign key for '
						. $col
						. ': '
						. $e->getMessage()
				);
			}
		}
	}
	protected function addBusinessCallColumns(Blueprint $table): void
	{
		$table->enum(AC::COL_CL_TP, CallType::values())->default(CallType::Other->value)->index();
		$table->string('phone')->nullable();
		$table->dateTime(AC::COL_CL_DT)->nullable();
		$table->time(AC::COL_CL_DUR)->nullable();
		$table->text(AC::COL_CL_RS)->nullable();
		$table->string('duration', 20); // * it's not clear why this is a string in legacy code, so keeping it like that for now // ? convert to HH:MM:SS if numeric or accept straight away as string if matching HH:MM:SS format [at boot/saving]
	}
	protected function addBussinessEmailColumns(Blueprint $table): void
	{
		$table->unsignedInteger('counter')->default(1)->nullable(); // ? nullable for testing purposes // ? how many messages have been exchanged between the from and to addresses regarding this lead
		$table->boolean(PJC::COL_IS_FUP)->default(false)->nullable(); // ? nullable for testing purposes // * is this email a follow-up message
		$table->json('attachments')->nullable();
		$table->json(PJC::COL_ATC_FRULES)->nullable(); // * at boot/saving, this should not for expected keys of filtering rules and match with expected fields from the attachment objects/subarrays, then filter accordingly
	}
}
