<?php

namespace App\Models;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\UserType;
use App\Traits\{HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, Relations\HasMany};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class Comment extends Model
{
	use UsesUuids, HasAuditFields, NormalizesArrays;

	protected const JSON_FIELDS = [
		'attachments',
		'tags',
		'reactions',
		'replies',
		'edits',
		'metadata',
	];

	protected const BASE_FILLABLE = [
		'time',
		'comment',
		'reference',
		UC::COL_USER_ID,
		UC::COL_U_TP,
		AC::COL_IS_EDT,
		AC::COL_IS_DEL,
		'deleter',
		AC::COL_DEL_AT,
		AC::COL_EDT_CNT,
		'flagged',
		'thread',
		AC::COL_IS_RPL,
		AC::COL_RPL_CNT,
		'order',
		'depth',
		'parent',
		'attachments',
		'tags',
		'reactions',
		'replies',
		'edits',
		'metadata',
	];

	protected $guarded = [
		'id',
		DC::COL_TABLE_CREATOR,
		DC::COL_TABLE_UPDATER,
	];

	protected $casts = [
		'time'           => 'datetime',
		AC::COL_DEL_AT   => 'datetime',
		AC::COL_IS_EDT   => 'boolean',
		AC::COL_IS_DEL   => 'boolean',
		AC::COL_IS_RPL   => 'boolean',
		'flagged'        => 'boolean',
		AC::COL_EDT_CNT  => 'integer',
		AC::COL_RPL_CNT  => 'integer',
		'order'          => 'integer',
		'depth'          => 'integer',
		'attachments'    => 'array',
		'tags'           => 'array',
		'reactions'      => 'array',
		'replies'        => 'array',
		'edits'          => 'array',
		'metadata'       => 'array',
	];

	protected $appends = [
		'reaction_total',
	];

	public function getFillable(): array
	{
		return static::fillableFields();
	}

	protected static function fillableFields(): array
	{
		return self::BASE_FILLABLE;
	}

	public function getWith(): array
	{
		return static::withRelations();
	}

	protected static function withRelations(): array
	{
		return ['author'];
	}

	protected static function booted(): void
	{
		is_callable('parent::booted') && parent::booted();
		static::saving(function (self $model): void {
			try {
				$model->enforceCommentInvariants();
			} catch (\Throwable $e) {
				Log::warning(static::class . ' saving invariant failure', [
					'id'    => $model->getAttribute('id'),
					'error' => $e->getMessage(),
				]);
				throw $e;
			}
		});
	}

	protected static function defaultUserType(): UserType
	{
		return UserType::Customer;
	}

	protected function enforceCommentInvariants(): void
	{
		$raw = $this->getAttribute('comment');
		$sanitized = $this->sanitizeCommentBody($raw);

		if ($sanitized === '') {
			Log::warning(static::class . ' empty comment blocked', [
				'id' => $this->getAttribute('id'),
			]);
			throw new \InvalidArgumentException('Comment cannot be empty');
		}

		$this->setAttribute('comment', $sanitized);

		$ref = $this->getAttribute('reference');
		if (is_string($ref)) {
			$ref = trim($ref);
			if ($ref === '') $ref = null;
			if ($ref !== null && strlen($ref) > 254) {
				Log::debug(static::class . ' reference truncated', [
					'id'  => $this->getAttribute('id'),
					'len' => strlen($ref),
				]);
				$ref = substr($ref, 0, 254);
			}
			$this->setAttribute('reference', $ref);
		} elseif ($ref !== null) {
			$this->setAttribute('reference', (string) $ref);
		}

		$rawUserType = $this->getAttribute(UC::COL_U_TP);

		if ($rawUserType instanceof UserType)
			$userType = $rawUserType;
		else {
			$s = is_string($rawUserType) ? trim($rawUserType) : trim((string) ($rawUserType ?? ''));
			if ($s === '')
				$userType = static::defaultUserType();
			else {
				$userType = UserType::normalize($s);
				if ($userType === UserType::Customer && static::defaultUserType() !== UserType::Customer && UserType::tryFrom($s) === null)
					$userType = static::defaultUserType();
			}
		}

		$this->setAttribute(UC::COL_U_TP, $userType->value);

		foreach ([AC::COL_IS_EDT, AC::COL_IS_DEL, AC::COL_IS_RPL, 'flagged'] as $b) {
			$v = $this->getAttribute($b);
			$this->setAttribute($b, filter_var($v, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false);
		}

		foreach ([AC::COL_EDT_CNT, AC::COL_RPL_CNT, 'order', 'depth'] as $n) {
			$v = $this->getAttribute($n);
			$i = is_numeric($v) ? (int) $v : 0;
			if ($i < 0) $i = 0;
			$this->setAttribute($n, $i);
		}

		$isDeleted = (bool) $this->getAttribute(AC::COL_IS_DEL);
		if (!$isDeleted) {
			$this->setAttribute('deleter', null);
			$this->setAttribute(AC::COL_DEL_AT, null);
		} else {
			if ($this->getAttribute(AC::COL_DEL_AT) === null)
				$this->setAttribute(AC::COL_DEL_AT, now());
			if ($this->getAttribute('deleter') !== null && !Str::isUuid((string) $this->getAttribute('deleter'))) {
				Log::warning(static::class . ' invalid deleter uuid nullified', [
					'id'      => $this->getAttribute('id'),
					'deleter' => $this->getAttribute('deleter'),
				]);
				$this->setAttribute('deleter', null);
			}
		}

		$isEdited = (bool) $this->getAttribute(AC::COL_IS_EDT);
		if (!$isEdited) {
			$this->setAttribute(AC::COL_EDT_CNT, 0);
			$this->setAttribute('edits', null);
		}

		$isReply = (bool) $this->getAttribute(AC::COL_IS_RPL);
		if (!$isReply) {
			$this->setAttribute('parent', null);
			$this->setAttribute('depth', 0);
		}

		$this->ensureJsonAttributesAreEncoded(self::JSON_FIELDS);
	}

	protected function sanitizeCommentBody(mixed $value): string
	{
		$s = is_string($value) ? $value : (string) ($value ?? '');
		$s = trim($s);
		if ($s === '') return '';

		$s = strip_tags($s);
		$s = preg_replace("/[ \t]+/", ' ', $s);
		$s = preg_replace("/\r\n|\r/", "\n", $s);

		return trim($s);
	}

	public function author(): BelongsTo
	{
		return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
	}

	public function deleterUser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'deleter', 'id');
	}

	public function parentComment(): BelongsTo
	{
		return $this->belongsTo(static::class, 'parent', 'id');
	}

	public function childComments(): HasMany
	{
		return $this->hasMany(static::class, 'parent', 'id')->orderBy('order');
	}

	public function getReactionTotalAttribute(): int
	{
		$reactions = self::normalizeArrayField($this->getAttribute('reactions'));
		$total = 0;

		foreach ($reactions as $r) {
			if (!is_array($r)) continue;
			$count = $r['count'] ?? 0;
			if (!is_numeric($count)) continue;
			$c = (int) $count;
			if ($c < 0) $c = 0;
			$total += $c;
		}

		return $total;
	}

	public function markDeleted(?string $deleterId = null): void
	{
		$this->setAttribute(AC::COL_IS_DEL, true);
		$this->setAttribute(AC::COL_DEL_AT, $this->getAttribute(AC::COL_DEL_AT) ?? now());

		if ($deleterId !== null && Str::isUuid($deleterId))
			$this->setAttribute('deleter', $deleterId);
	}

	public function addEdit(string $newComment, ?string $editorId = null): void
	{
		$newBody = $this->sanitizeCommentBody($newComment);
		if ($newBody === '') throw new \InvalidArgumentException('Comment cannot be empty');

		$edits = self::normalizeArrayField($this->getAttribute('edits'));
		$edits[] = [
			'at'         => now()->toISOString(),
			'previous'   => (string) $this->getAttribute('comment'),
			'editor_id'  => (is_string($editorId) && Str::isUuid($editorId)) ? $editorId : null,
			'updater_id' => $this->getAttribute(DC::COL_TABLE_UPDATER),
		];

		$this->setAttribute('comment', $newBody);
		$this->setAttribute(AC::COL_IS_EDT, true);
		$this->setAttribute(AC::COL_EDT_CNT, (int) ($this->getAttribute(AC::COL_EDT_CNT) ?? 0) + 1);
		$this->setAttribute('edits', $edits);
	}

	public function scopeNotDeleted($query)
	{
		return $query->where(AC::COL_IS_DEL, false);
	}
}
