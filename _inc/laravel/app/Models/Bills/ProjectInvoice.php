<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\PaymentStatus;
use App\Traits\{ExtendsInvoiceTable, HasAuditFields, UsesUuids};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, SoftDeletes};
use Illuminate\Support\Facades\{DB, Log};

class ProjectInvoice extends Model
{
	use UsesUuids;
	use HasAuditFields;
	use SoftDeletes;
	use ExtendsInvoiceTable;

	protected $table = DC::TABLE_PRJ_INV;

	protected $guarded = [
		'id',
		DC::COL_TABLE_CREATOR,
	];

	protected $fillable = [
		BC::COL_INV_ID,
		PJC::COL_PJ_ID,
		BC::COL_BL_ID,
		BC::COL_TAX_ID,
		BC::COL_DUE_DT,
		'status',
		DC::COL_TABLE_UPDATER,
	];

	protected $casts = [
		BC::COL_INV_ID => 'string',
		PJC::COL_PJ_ID => 'string',
		BC::COL_BL_ID  => 'string',
		BC::COL_TAX_ID => 'string',
		BC::COL_DUE_DT => 'date',
		'status'       => 'integer',
		'deleted_at'   => 'datetime',
	];

	protected $with = [
		'invoice',
		'project',
	];

	protected $appends = [
		'status_enum',
		'is_completed',
		'is_failed',
		'is_cancelled',
		'is_overdue',
		'due_in_days',
	];

	private static array $cache = [
		'invoice_row' => [],
	];

	protected static function booted(): void
	{
		static::saving(function (self $m): void {
			try {
				$m->normalizeUuids();
				$m->syncDueDateFromInvoiceOrFail();
				$m->normalizeStatus();
				$m->enforceUniquePairOrFail();
			} catch (\Throwable $e) {
				Log::error(self::class . ' saving failed', [
					'id'         => (string) ($m->getAttribute('id') ?? ''),
					'invoice_id'  => (string) ($m->getAttribute(BC::COL_INV_ID) ?? ''),
					'project_id'  => (string) ($m->getAttribute(PJC::COL_PJ_ID) ?? ''),
					'error'       => $e->getMessage(),
				]);
				throw $e;
			}
		});
	}

	public function invoice(): BelongsTo
	{
		return $this->belongsTo(Invoice::class, BC::COL_INV_ID, 'id');
	}

	public function project(): BelongsTo
	{
		return $this->belongsTo(Project::class, PJC::COL_PJ_ID, 'id');
	}

	public function bill(): BelongsTo
	{
		return $this->belongsTo(Bill::class, BC::COL_BL_ID, 'id');
	}

	public function tax(): BelongsTo
	{
		return $this->belongsTo(Tax::class, BC::COL_TAX_ID, 'id');
	}

	public function createdBy(): BelongsTo
	{
		return $this->belongsTo(User::class, DC::COL_TABLE_CREATOR, 'id');
	}

	public function updatedBy(): BelongsTo
	{
		return $this->belongsTo(User::class, DC::COL_TABLE_UPDATER, 'id');
	}

	public function getStatusEnumAttribute(): PaymentStatus
	{
		$idx = (int) ($this->getAttribute('status') ?? 1);
		return $this->statusIndexToEnum($idx);
	}

	public function getIsCompletedAttribute(): bool
	{
		return $this->getStatusEnumAttribute() === PaymentStatus::Completed;
	}

	public function getIsFailedAttribute(): bool
	{
		return $this->getStatusEnumAttribute() === PaymentStatus::Failed;
	}

	public function getIsCancelledAttribute(): bool
	{
		return $this->getStatusEnumAttribute() === PaymentStatus::Cancelled;
	}

	public function getIsOverdueAttribute(): bool
	{
	    try {
    		$due = $this->getAttribute(BC::COL_DUE_DT);
    		if (!$due) return false;

    		try {
    			$d = $due instanceof Carbon ? $due : Carbon::parse((string) $due);
    			return $d->isPast() && !$this->getIsCompletedAttribute();
    		} catch (\Throwable) {
    			return false;
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::getIsOverdueAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return false;
	    }
	}

	public function getDueInDaysAttribute(): ?int
	{
	    try {
    		$due = $this->getAttribute(BC::COL_DUE_DT);
    		if (!$due) return null;

    		try {
    			$d = $due instanceof Carbon ? $due : Carbon::parse((string) $due);
    			return Carbon::now()->startOfDay()->diffInDays($d->startOfDay(), false);
    		} catch (\Throwable) {
    			return null;
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::getDueInDaysAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return 0;
	    }
	}

	private function normalizeUuids(): void
	{
	    try {
    		foreach ([BC::COL_INV_ID, PJC::COL_PJ_ID, BC::COL_BL_ID, BC::COL_TAX_ID] as $k) {
    			$raw = $this->getAttribute($k);
    			if ($raw === null) continue;

    			if (!is_string($raw)) {
    				Log::warning(self::class . " non-string {$k}", [
    					'id'    => (string) ($this->getAttribute('id') ?? ''),
    					'type'  => gettype($raw),
    				]);
    				$this->setAttribute($k, null);
    				continue;
    			}

    			$v = trim($raw);
    			if ($v === '') $this->setAttribute($k, null);
    			elseif (method_exists(Utility::class, 'looksLikeUuid') && !Utility::looksLikeUuid($v))
    				$this->setAttribute($k, null);
    			else $this->setAttribute($k, $v);
    		}

    		$inv = (string) ($this->getAttribute(BC::COL_INV_ID) ?? '');
    		$prj = (string) ($this->getAttribute(PJC::COL_PJ_ID) ?? '');

    		if ($inv === '') throw new \InvalidArgumentException('ProjectInvoice requires invoice_id.');
    		if ($prj === '') throw new \InvalidArgumentException('ProjectInvoice requires project_id.');
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeUuids — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	private function syncDueDateFromInvoiceOrFail(): void
	{
	    try {
    		$invId = (string) ($this->getAttribute(BC::COL_INV_ID) ?? '');
    		if ($invId === '') throw new \InvalidArgumentException('ProjectInvoice requires invoice_id before syncing due_date.');

    		$invoice = $this->getInvoiceRowCached($invId);

    		// “Invoice é a fonte de verdade” para due_date (se existir no invoice)
    		$invDueRaw = $invoice[BC::COL_DUE_DT] ?? ($invoice['due_date'] ?? null);

    		try {
    			if ($invDueRaw) {
    				$invDue = $invDueRaw instanceof Carbon ? $invDueRaw : Carbon::parse((string) $invDueRaw);
    				$this->setAttribute(BC::COL_DUE_DT, $invDue->toDateString());
    				return;
    			}
    		} catch (\Throwable $e) {
    			Log::debug(self::class . ' failed parsing invoice due_date', [
    				'invoice_id' => $invId,
    				'error'      => $e->getMessage(),
    			]);
    		}

    		// Sem due_date no invoice => esta linha precisa ter due_date (coluna NOT NULL)
    		$cur = $this->getAttribute(BC::COL_DUE_DT);
    		if (!$cur) throw new \InvalidArgumentException('ProjectInvoice requires due_date (or invoice must have due_date).');
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::syncDueDateFromInvoiceOrFail — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	private function normalizeStatus(): void
	{
	    try {
    		$raw = $this->getAttribute('status');

    		// aceita: int, string numérica, string PaymentStatus, enum PaymentStatus
    		try {
    			if ($raw instanceof PaymentStatus) {
    				$this->setAttribute('status', PaymentStatus::getIndex($raw->value));
    				return;
    			}

    			if (is_string($raw) && $raw !== '' && ctype_digit($raw)) {
    				$idx = (int) $raw;
    				$this->setAttribute('status', $this->statusIndexIsValid($idx) ? $idx : 1);
    				return;
    			}

    			if (is_int($raw)) {
    				$this->setAttribute('status', $this->statusIndexIsValid($raw) ? $raw : 1);
    				return;
    			}

    			if (is_string($raw) && trim($raw) !== '') {
    				$enum = PaymentStatus::normalize($raw);
    				$this->setAttribute('status', PaymentStatus::getIndex($enum->value));
    				return;
    			}
    		} catch (\Throwable $e) {
    			Log::debug(self::class . ' failed normalizing status', [
    				'error' => $e->getMessage(),
    			]);
    		}

    		$this->setAttribute('status', 1);
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::normalizeStatus — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	private function enforceUniquePairOrFail(): void
	{
	    try {
    		$id  = (string) ($this->getAttribute('id') ?? '');
    		$inv = (string) ($this->getAttribute(BC::COL_INV_ID) ?? '');
    		$prj = (string) ($this->getAttribute(PJC::COL_PJ_ID) ?? '');

    		if ($inv === '' || $prj === '') return;

    		$q = self::query()
    			->where(BC::COL_INV_ID, $inv)
    			->where(PJC::COL_PJ_ID, $prj);

    		if ($id !== '') $q->where('id', '!=', $id);

    		if ($q->exists())
    			throw new \InvalidArgumentException('ProjectInvoice already exists for (invoice_id, project_id).');
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::enforceUniquePairOrFail — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	private function statusIndexToEnum(int $idx): PaymentStatus
	{
	    try {
    		return match ($idx) {
    			0  => PaymentStatus::Pending,
    			1  => PaymentStatus::Processing,
    			2  => PaymentStatus::Authorized,
    			3  => PaymentStatus::Completed,
    			4  => PaymentStatus::Failed,
    			5  => PaymentStatus::Cancelled,
    			6  => PaymentStatus::Refunded,
    			7  => PaymentStatus::PartiallyRefunded,
    			8  => PaymentStatus::Expired,
    			9  => PaymentStatus::Declined,
    			10 => PaymentStatus::Disputed,
    			11 => PaymentStatus::Undefined,
    			default => PaymentStatus::Processing,
    		};
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::statusIndexToEnum — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return null;
	    }
	}

	private function statusIndexIsValid(int $idx): bool
	{
		return in_array($idx, PaymentStatus::getAllIndexes(), true);
	}

	private function getInvoiceRowCached(string $invoiceId): array
	{
	    try {
    		$key = trim($invoiceId);
    		if ($key === '') return [];

    		if (array_key_exists($key, self::$cache['invoice_row']))
    			return (array) (self::$cache['invoice_row'][$key] ?? []);

    		try {
    			$row = (array) (DB::table(DC::TABLE_INVS)->where('id', $key)->first() ?? []);
    			self::$cache['invoice_row'][$key] = $row;
    			return $row;
    		} catch (\Throwable $e) {
    			Log::debug(self::class . ' failed fetching invoice row', [
    				'invoice_id' => $key,
    				'error'      => $e->getMessage(),
    			]);
    			self::$cache['invoice_row'][$key] = [];
    			return [];
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::getInvoiceRowCached — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return [];
	    }
	}
}
