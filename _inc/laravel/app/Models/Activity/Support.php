<?php

namespace App\Models;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	MessagesConstants as MC,
	ProjectsConstants as PJC,
	SupportsConstants as SC,
	UsersConstants as UC
};
use App\Enums\{AppModuleType, CaseStatus, PriorityLevel, Visibility};
use App\Traits\{
	ChecksLogin,
	FiltersSecureAttachments,
	HasAuditFields,
	NormalizesArrays,
	PlansByHierarchy,
	UsesUuids
};
use Illuminate\Database\Eloquent\{
	Factories\HasFactory,
	Model,
	Relations\BelongsTo
};
use Illuminate\Support\Facades\{Auth, DB, Log, Schema};
use Illuminate\Support\Str;

class Support extends Model
{
	use ChecksLogin, FiltersSecureAttachments, HasAuditFields, HasFactory, NormalizesArrays, PlansByHierarchy, UsesUuids;

	protected $table = DC::TABLE_SUPPORTS;

	protected $guarded = ['id', DC::COL_TABLE_CREATOR];

	protected $fillable = [
		SC::COL_SBJ,
		'module',
		SC::COL_TKT_CR,
		SC::COL_TKT_CD,
		SC::COL_USR,
		'client',
		'priority',
		'description',
		'visibility',
		PJC::COL_S_DT,
		PJC::COL_E_DT,
		'status',
		SC::COL_STT_LB,
		SC::COL_REOPEN_CT,
		SC::COL_SVD_AT,
		PJC::COL_SBM_BY,
		PJC::COL_SBM_AT,
		SC::COL_ASG_BY,
		SC::COL_ASG_TO,
		SC::COL_ASG_AT,
		SC::COL_CLSD_BY,
		SC::COL_CLSD_AT,
		AC::COL_TTL_TIME,
		'email',
		'notification',
		'task',
		'bug',
		SC::COL_ATC,
		SC::COL_OTHER_ATTACHMENTS,
		SC::COL_OTHER_VENDORS,
	];

	protected $casts = [
		'module' => AppModuleType::class,
		'priority' => PriorityLevel::class,
		'visibility' => Visibility::class,
		SC::COL_STT_LB => CaseStatus::class,

		PJC::COL_S_DT => 'date',
		PJC::COL_E_DT => 'date',
		SC::COL_SVD_AT => 'date',
		PJC::COL_SBM_AT => 'date',
		SC::COL_ASG_AT => 'date',
		SC::COL_CLSD_AT => 'date',

		SC::COL_OTHER_ATTACHMENTS => 'array',
		SC::COL_OTHER_VENDORS => 'array',

		AC::COL_TTL_TIME => 'integer',
		SC::COL_REOPEN_CT => 'integer',
	];

	protected $with = ['requester', 'clientRel', 'assignedToRel'];

	protected $appends = [
		'status_index',
		'status_value',
		'is_active_case',
		'is_terminal_case',
		'priority_weight',
		'has_attachments',
		'unread_replies_count',
	];

	public static array $priority = ['Low', 'Medium', 'High', 'Critical', 'Urgent', 'Blocker', 'Immediate'];

	public static array $status = [
		'New' => 'New',
		'Open' => 'Open',
		'Closed' => 'Closed',
		'On Hold' => 'On Hold',
		'Waiting Customer' => 'Waiting Customer',
		'Waiting Vendor' => 'Waiting Vendor',
		'Waiting Third Party' => 'Waiting Third Party',
		'Escalated' => 'Escalated',
		'Resolved' => 'Resolved',
		'Reopened' => 'Reopened',
		'Cancelled' => 'Cancelled',
		'Duplicate' => 'Duplicate',
		'Archived' => 'Archived',
		'Deleted' => 'Deleted',
		'Under Review' => 'Under Review',
		'Pending Approval' => 'Pending Approval',
		'Scheduled' => 'Scheduled',
		'In Development' => 'In Development',
		'Testing' => 'Testing',
		'Deferred' => 'Deferred',
		'Merged' => 'Merged',
		'Awaiting Feedback' => 'Awaiting Feedback',
		'Failed' => 'Failed',
		'Blocked' => 'Blocked',
	];

	protected static function booted(): void
	{
		static::saving(function (self $m): void {
			$m->applyDefaults();
			$m->syncLegacyStatusFields();
			$m->applySolvedAtPolicy();
			$m->applyReopenCountPolicy();
			$m->normalizeTotalTime();
			$m->ensureTicketCode();
			$m->normalizeJsonFields();
		});
	}

	protected function applyDefaults(): void
	{
		if (empty($this->getAttribute('module')))
			$this->setAttribute('module', AppModuleType::Support);

		$p = $this->getAttribute('priority');
		if (empty($p) || $p === PriorityLevel::None->value)
			$this->setAttribute('priority', PriorityLevel::Medium);

		if (empty($this->getAttribute('visibility')))
			$this->setAttribute('visibility', Visibility::Private);

		if (empty($this->getAttribute(SC::COL_STT_LB)))
			$this->setAttribute(SC::COL_STT_LB, CaseStatus::New);

		$s = $this->getAttribute('status');
		if ($s === null || $s === '')
			$this->setAttribute('status', '0');
	}

	protected function normalizeJsonFields(): void
	{
		try {
			$table = $this->getTable();
			$jsonFields = [];

			if (Schema::hasColumn($table, SC::COL_OTHER_ATTACHMENTS)) $jsonFields[] = SC::COL_OTHER_ATTACHMENTS;
			if (Schema::hasColumn($table, SC::COL_OTHER_VENDORS)) $jsonFields[] = SC::COL_OTHER_VENDORS;

			if ($jsonFields)
				$this->ensureJsonAttributesAreEncoded($jsonFields);
		} catch (\Throwable $e) {
			Log::error(static::class . ' normalizeJsonFields failed', [
				'table' => $this->getTable(),
				'id' => $this->getKey(),
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
	}

	protected function syncLegacyStatusFields(): void
	{
		$statusLabel = $this->getAttribute(SC::COL_STT_LB);
		$status = (string) $this->getAttribute('status');

		$cases = CaseStatus::cases();
		$maxIdx = max(0, count($cases) - 1);

		if ($statusLabel instanceof CaseStatus) {
			$idx = array_search($statusLabel, $cases, true);
			if ($idx !== false)
				$this->setAttribute('status', (string) $idx);
			return;
		}

		if (ctype_digit($status)) {
			$i = (int) $status;
			$i = $i < 0 ? 0 : ($i > $maxIdx ? $maxIdx : $i);
			$this->setAttribute('status', (string) $i);
			$this->setAttribute(SC::COL_STT_LB, $cases[$i] ?? CaseStatus::New);
			return;
		}

		$this->setAttribute('status', '0');
		$this->setAttribute(SC::COL_STT_LB, CaseStatus::New);
	}

	protected function applySolvedAtPolicy(): void
	{
		$st = $this->getAttribute(SC::COL_STT_LB);
		if (!($st instanceof CaseStatus)) return;

		if ($st === CaseStatus::Resolved || $st === CaseStatus::Closed)
			$this->setAttribute(SC::COL_SVD_AT, $this->getAttribute(SC::COL_SVD_AT) ?: now());
		else
			$this->setAttribute(SC::COL_SVD_AT, null);
	}

	protected function applyReopenCountPolicy(): void
	{
		$st = $this->getAttribute(SC::COL_STT_LB);
		if (!($st instanceof CaseStatus)) return;

		$orig = $this->getOriginal(SC::COL_STT_LB);
		$orig = $orig instanceof CaseStatus ? $orig : CaseStatus::normalize($orig);

		if ($st === CaseStatus::Reopened && $orig && $orig !== CaseStatus::Reopened) {
			$ct = (int) ($this->getAttribute(SC::COL_REOPEN_CT) ?? 0);
			$this->setAttribute(SC::COL_REOPEN_CT, $ct + 1);
		}
	}

	protected function normalizeTotalTime(): void
	{
		$v = $this->getAttribute(AC::COL_TTL_TIME);
		if ($v === null) return;

		$i = (int) $v;
		$this->setAttribute(AC::COL_TTL_TIME, $i < 0 ? 0 : $i);
	}

	protected function ensureTicketCode(): void
	{
		$code = trim((string) $this->getAttribute(SC::COL_TKT_CD));
		if ($code !== '') return;

		$id = (string) ($this->getKey() ?: Str::uuid());
		$this->setAttribute(SC::COL_TKT_CD, 'SUP-TKT-' . strtoupper($id) . '-' . now()->format('Ymd'));
	}

	public function getStatusIndexAttribute(): int
	{
		$s = (string) ($this->getAttribute('status') ?? '0');
		return ctype_digit($s) ? (int) $s : 0;
	}

	public function getStatusValueAttribute(): string
	{
		$st = $this->getAttribute(SC::COL_STT_LB);
		return $st instanceof CaseStatus ? $st->value : CaseStatus::New->value;
	}

	public function getIsActiveCaseAttribute(): bool
	{
		$st = $this->getAttribute(SC::COL_STT_LB);
		return $st instanceof CaseStatus ? $st->isActive() : false;
	}

	public function getIsTerminalCaseAttribute(): bool
	{
		$st = $this->getAttribute(SC::COL_STT_LB);
		return $st instanceof CaseStatus ? $st->isTerminal() : false;
	}

	public function getPriorityWeightAttribute(): int
	{
		$p = $this->getAttribute('priority');
		return $p instanceof PriorityLevel ? $p->weight() : PriorityLevel::Medium->weight();
	}

	public function getHasAttachmentsAttribute(): bool
	{
		$main = $this->getAttribute(SC::COL_ATC);
		if (is_string($main) && trim($main) !== '') return true;

		$others = $this->getAttribute(SC::COL_OTHER_ATTACHMENTS);
		return is_array($others) && count($others) > 0;
	}

	public function getUnreadRepliesCountAttribute(): int
	{
		return $this->replyUnread();
	}

	public static function status(): array
	{
		return [
			'New' => __('New'),
			'Open' => __('Open'),
			'Closed' => __('Closed'),
			'On Hold' => __('On Hold'),
			'Waiting Customer' => __('Waiting Customer'),
			'Waiting Vendor' => __('Waiting Vendor'),
			'Waiting Third Party' => __('Waiting Third Party'),
			'Escalated' => __('Escalated'),
			'Resolved' => __('Resolved'),
			'Reopened' => __('Reopened'),
			'Cancelled' => __('Cancelled'),
			'Duplicate' => __('Duplicate'),
			'Archived' => __('Archived'),
			'Deleted' => __('Deleted'),
			'Under Review' => __('Under Review'),
			'Pending Approval' => __('Pending Approval'),
			'Scheduled' => __('Scheduled'),
			'In Development' => __('In Development'),
			'Testing' => __('Testing'),
			'Deferred' => __('Deferred'),
			'Merged' => __('Merged'),
			'Awaiting Feedback' => __('Awaiting Feedback'),
			'Failed' => __('Failed'),
			'Blocked' => __('Blocked'),
		];
	}

	public function priorityList(): array
	{
		return self::$priority;
	}

	public function statusList(): array
	{
		return self::$status;
	}

	public function priorityEnumOptions(): array
	{
		return array_values(array_filter(PriorityLevel::cases(), fn ($c) => $c !== PriorityLevel::None));
	}

	public function statusEnumOptions(): array
	{
		return CaseStatus::cases();
	}

	public function ticketCreator(): BelongsTo
	{
		return $this->belongsTo(User::class, SC::COL_TKT_CR, 'id');
	}

	public function requester(): BelongsTo
	{
		return $this->belongsTo(User::class, SC::COL_USR, 'id');
	}

	public function clientRel(): BelongsTo
	{
		return $this->belongsTo(Client::class, 'client', 'id');
	}

	public function assignedToRel(): BelongsTo
	{
		return $this->belongsTo(User::class, SC::COL_ASG_TO, 'id');
	}

	public function bugRel(): BelongsTo
	{
		return $this->belongsTo(Bug::class, 'bug', 'id');
	}

	public function replyUnread(): int
	{
		$user = Auth::user();
		if (!$user) return 0;

		$isEmployee = strtolower((string) ($user[UC::COL_TP] ?? '')) === 'employee';

		$q = SupportReply::where(SC::COL_SPT_ID, $this->getKey())
			->where(MC::COL_IS_RD, 0);

		return $isEmployee
			? $q->where('user', '!=', $user->id)->count('id')
			: $q->count('id');
	}

	public function scopeOpenCases($q)
	{
		return $q->whereIn(SC::COL_STT_LB, array_map(fn ($c) => $c->value, array_values(array_filter(CaseStatus::cases(), fn ($c) => $c->isActive()))));
	}

	public function touchClosedByPolicy(?string $userId = null): void
	{
		try {
			$table = $this->getTable();
			if (!Schema::hasColumn($table, SC::COL_CLSD_BY) || !Schema::hasColumn($table, SC::COL_CLSD_AT)) return;

			$st = $this->getAttribute(SC::COL_STT_LB);
			if (!($st instanceof CaseStatus) || !$st->isTerminal()) return;

			$uid = trim((string) ($userId ?? Auth::id() ?? ''));
			if ($uid === '') return;

			$this->setAttribute(SC::COL_CLSD_BY, $uid);
			if (empty($this->getAttribute(SC::COL_CLSD_AT)))
				$this->setAttribute(SC::COL_CLSD_AT, now());
		} catch (\Throwable $e) {
			Log::error(static::class . ' touchClosedByPolicy failed', [
				'id' => $this->getKey(),
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
	}
}
