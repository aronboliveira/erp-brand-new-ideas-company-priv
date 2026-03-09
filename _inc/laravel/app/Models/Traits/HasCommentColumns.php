<?php

namespace App\Traits;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{UserType};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait HasCommentColumns
{
	protected function addCommentColumns(
		Blueprint $table,
		array $unnullify = [],
		?array $userTypeValues = null,
		UserType|string|null $defaultUserType = null
	): void {
	    try {
    		$userTypeValues ??= array_column(UserType::cases(), 'value');

    		$defaultEnum = null;
    		try {
    			$defaultEnum = $defaultUserType === null
    				? UserType::Customer
    				: ($defaultUserType instanceof UserType
    					? $defaultUserType
    					: UserType::normalize((string) $defaultUserType));
    		} catch (\Throwable $e) {
    			Log::warning(static::class . ' invalid default user type for comment columns', [
    				'default' => $defaultUserType,
    				'error'   => $e->getMessage(),
    			]);
    			$defaultEnum = UserType::Customer;
    		}

    		if (!in_array($defaultEnum->value, $userTypeValues, true))
    			$defaultEnum = UserType::Customer;

    		$isRequired = fn(string $col): bool => in_array($col, $unnullify, true);

    		$time = $table->timestamp('time')->useCurrent();
    		if (!$isRequired('time')) $time->nullable();
    		$time->index();

    		$table->text('comment');

    		$reference = $table->string('reference', 254);
    		if (!$isRequired('reference')) $reference->nullable();
    		$reference->index();

    		$userId = $table->uuid(UC::COL_USER_ID);
    		if (!$isRequired(UC::COL_USER_ID)) $userId->nullable();
    		$userId->index();

    		$userType = $table->enum(UC::COL_U_TP, $userTypeValues)->default($defaultEnum->value);
    		if (!$isRequired(UC::COL_U_TP)) $userType->nullable();

    		$isEdited = $table->boolean(AC::COL_IS_EDT)->default(false);
    		if (!$isRequired(AC::COL_IS_EDT)) $isEdited->nullable();
    		$isEdited->index();

    		$isDeleted = $table->boolean(AC::COL_IS_DEL)->default(false);
    		if (!$isRequired(AC::COL_IS_DEL)) $isDeleted->nullable();
    		$isDeleted->index();

    		$deleter = $table->uuid('deleter');
    		if (!$isRequired('deleter')) $deleter->nullable();

    		$deletedAt = $table->dateTime(AC::COL_DEL_AT);
    		if (!$isRequired(AC::COL_DEL_AT)) $deletedAt->nullable();
    		$deletedAt->index();

    		$editCount = $table->unsignedSmallInteger(AC::COL_EDT_CNT)->default(0);
    		if (!$isRequired(AC::COL_EDT_CNT)) $editCount->nullable();
    		$editCount->index();

    		$flagged = $table->boolean('flagged')->default(false);
    		if (!$isRequired('flagged')) $flagged->nullable();
    		$flagged->index();

    		$thread = $table->uuid('thread');
    		if (!$isRequired('thread')) $thread->nullable();
    		$thread->index();

    		$isReply = $table->boolean(AC::COL_IS_RPL)->default(false);
    		if (!$isRequired(AC::COL_IS_RPL)) $isReply->nullable();
    		$isReply->index();

    		$replyCount = $table->unsignedInteger(AC::COL_RPL_CNT)->default(0);
    		if (!$isRequired(AC::COL_RPL_CNT)) $replyCount->nullable();
    		$replyCount->index();

    		$order = $table->unsignedSmallInteger('order')->default(0);
    		if (!$isRequired('order')) $order->nullable();
    		$order->index();

    		$depth = $table->unsignedTinyInteger('depth')->default(0);
    		if (!$isRequired('depth')) $depth->nullable();
    		$depth->index();

    		$parent = $table->uuid('parent')->nullable();
    		$parent->index();

    		$attachments = $table->json('attachments');
    		if (!$isRequired('attachments')) $attachments->nullable();

    		$tags = $table->json('tags');
    		if (!$isRequired('tags')) $tags->nullable();

    		$reactions = $table->json('reactions');
    		if (!$isRequired('reactions')) $reactions->nullable();

    		$replies = $table->json('replies');
    		if (!$isRequired('replies')) $replies->nullable();

    		$edits = $table->json('edits');
    		if (!$isRequired('edits')) $edits->nullable();

    		$metadata = $table->json('metadata');
    		if (!$isRequired('metadata')) $metadata->nullable();

    		$table->foreign(UC::COL_USER_ID)
    			->references('id')->on(DC::TABLE_USERS)
    			->cascadeOnDelete();
    		$table->foreign('deleter')
    			->references('id')->on(DC::TABLE_USERS)
    			->nullOnDelete();
    		$table->foreign('parent')
    			->references('id')->on($table->getTable())
    			->nullOnDelete();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addCommentColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	protected function dropCommentColumnForeigns(
		Blueprint $table,
		string $tableName,
		array $extraColumns = []
	): void {
	    try {
    		$cols = array_values(array_unique(array_merge([UC::COL_USER_ID, 'deleter'], [$table->getTable(), 'parent'], $extraColumns)));

    		foreach ($cols as $col) {
    			try {
    				Schema::hasColumn($tableName, $col) &&
    					$table->dropForeign([$col]);
    			} catch (\Exception $e) {
    				Log::warning(
    					'Failed to drop foreign key for '
    						. $col
    						. ' on table '
    						. $tableName
    						. ': '
    						. $e->getMessage()
    				);
    			}
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::dropCommentColumnForeigns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
