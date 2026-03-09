<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{MonthName, PaymentMethod, PaymentStatus, TransferType, UserType};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
	Factories\HasFactory,
	Model,
	Relations\BelongsTo
};
use Illuminate\Support\Facades\{DB, Log, Schema};
use RuntimeException;

abstract class CardNote extends Model
{
	use HasAuditFields;
	use HasFactory;
	use UsesUuids;

	protected const BASE_FILLABLE = [
		BC::COL_CST_ID,
		'amount',
		'discount',
		BC::COL_CUR_ID,
		'reference',
		'description',
		'notes',
		'attachments',
		BC::COL_TC,
		'status',
		BC::COL_N_INTR,
		BC::COL_CURR_N_INTR,
		'date',
		BC::COL_BACC_ID,
		BC::COL_CAT_ID,
		BC::COL_ADD_RCP,
		BC::COL_PPS_CD,
		BC::COL_TRF_TP,
		BC::COL_TXS_LST,
		BC::COL_PAY_MTD,
		BC::COL_PAY_MTD_LB,
		BC::COL_SVC_FEE,
		BC::COL_TXS_FEE,
		BC::COL_CD_FLG,
		BC::COL_CD_NB,
		BC::COL_CD_DG,
		BC::COL_CD_HNM,
		BC::COL_CD_EX_M,
		BC::COL_CD_EX_Y,
	];

	protected $fillable = self::BASE_FILLABLE;

	protected $guarded = [
		'id',
		DC::COL_TABLE_CREATOR,
		DC::COL_TABLE_UPDATER,
		BC::COL_RCC_BY,
		BC::COL_RCC_AT,
	];

	protected const BASE_CASTS = [
		'date'        => 'date',
		'amount'              => 'decimal:2',
		'discount'            => 'float',
		BC::COL_SVC_FEE       => 'decimal:2',
		BC::COL_TXS_FEE       => 'decimal:2',
		BC::COL_CUR_ID        => 'string',
		'status'              => PaymentStatus::class,
		BC::COL_PAY_MTD_LB    => PaymentMethod::class,
		BC::COL_TRF_TP        => TransferType::class,
		BC::COL_N_INTR        => 'integer',
		BC::COL_CURR_N_INTR   => 'integer',
		BC::COL_TXS_LST       => 'array',
		'attachments'         => 'array',
		BC::COL_TC            => 'array',
		BC::COL_RCP_MD        => 'array',
		BC::COL_RCC_RL        => 'array',
		BC::COL_AUTORCC       => 'bool',
		BC::COL_BACC_ID       => 'string',
		BC::COL_CAT_ID        => 'string',
		BC::COL_CD_EX_M       => MonthName::class,
	];

	protected $casts = self::BASE_CASTS;

	protected const BASE_WITH = [
		'bankAccount',
		'productServiceCategory',
	];

	protected $with = self::BASE_WITH;

	protected static function booted(): void
	{
		parent::booted();
		static::creating(function (self $model): void {
			$model->setAttribute(BC::COL_CURR_N_INTR, $model->normalizedCurrentInstallment());
		});
		static::saving(function (self $model): void {
			$model->setAttribute('status', PaymentStatus::normalize($model->getAttribute('status') ?? null)->value);
			$billColumn    = Schema::hasColumn($model->getTable(), BC::COL_BL_ID)
				? BC::COL_BL_ID
				: 'bill';
			$invoiceColumn = Schema::hasColumn($model->getTable(), BC::COL_INV_ID)
				? BC::COL_INV_ID
				: 'invoice';
			// Use raw attributes to avoid triggering relation resolution
			// when the FK column name matches a relation method name
			$billId    = $model->getAttributes()[$billColumn] ?? null;
			$invoiceId = $model->getAttributes()[$invoiceColumn] ?? null;
			if (!$billId && !$invoiceId)
				throw new RuntimeException('Card notes must be linked to a bill or an invoice.');
			$model->setAttribute(BC::COL_CURR_N_INTR, $model->normalizedCurrentInstallment());
			$model->normalizeCardExpiration();
			$model->normalizeCardDigits();
		});
	}

	abstract protected function monetarySign(): int;

	public function customer(): ?BelongsTo
	{
		return Utility::getCustomer($this);
	}

	public function invoice(): ?BelongsTo
	{
		$fk = Schema::hasColumn($this->getTable(), BC::COL_INV_ID)
			? BC::COL_INV_ID
			: 'invoice';
		// Guard: when FK name equals 'invoice' (same as this method),
		// ensure the attribute exists to prevent infinite recursion
		// in BelongsTo::addConstraints → $this->child->{foreignKey}
		if ($fk === 'invoice' && !array_key_exists('invoice', $this->getAttributes())) {
			$this->setAttribute('invoice', null);
		}
		return $this->belongsTo(Invoice::class, $fk, 'id');
	}

	public function bill(): ?BelongsTo
	{
		$fk = Schema::hasColumn($this->getTable(), BC::COL_BL_ID)
			? BC::COL_BL_ID
			: 'bill';
		if ($fk === 'bill' && !array_key_exists('bill', $this->getAttributes())) {
			$this->setAttribute('bill', null);
		}
		return $this->belongsTo(Bill::class, $fk, 'id');
	}

	public function bankAccount(): ?BelongsTo
	{
		return $this->belongsTo(BankAccount::class, BC::COL_BACC_ID);
	}

	public function productServiceCategory(): ?BelongsTo
	{
		return $this->belongsTo(ProductServiceCategory::class, BC::COL_CAT_ID, 'id');
	}

	public function productCategory(): ?BelongsTo
	{
		return $this->belongsTo(ProductCategory::class, BC::COL_CAT_ID, 'id');
	}

	public function category(): ?BelongsTo
	{
		return Utility::getCategory($this);
	}


	public function paymentStatus(): PaymentStatus
	{
		return PaymentStatus::normalize($this->status ?? null);
	}

	public function isPending(): bool
	{
		return $this->paymentStatus() === PaymentStatus::Pending;
	}

	public function isCompleted(): bool
	{
		return $this->paymentStatus() === PaymentStatus::Completed;
	}

	public function isRefunded(): bool
	{
		try {
			return in_array(
				$this->paymentStatus(),
				[PaymentStatus::Refunded, PaymentStatus::PartiallyRefunded],
				true
			);
		} catch (\Throwable $e) {
			Log::error(static::class . '::isRefunded — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return false;
		}
	}

	protected function billColumn(): string
	{
		try {
			return Schema::hasColumn($this->getTable(), BC::COL_BL_ID)
				? BC::COL_BL_ID
				: 'bill';
		} catch (\Throwable $e) {
			Log::error(static::class . '::billColumn — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return '';
		}
	}

	protected function invoiceColumn(): string
	{
		try {
			return Schema::hasColumn($this->getTable(), BC::COL_INV_ID)
				? BC::COL_INV_ID
				: 'invoice';
		} catch (\Throwable $e) {
			Log::error(static::class . '::invoiceColumn — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return '';
		}
	}


	public function isLinkedToInvoice(): bool
	{
		return (bool) $this->getAttribute($this->invoiceColumn());
	}

	public function isLinkedToBill(): bool
	{
		return (bool) $this->getAttribute($this->billColumn());
	}

	public function getEffectiveDocumentType(): string
	{
		try {
			if ($this->isLinkedToInvoice()) return 'invoice';
			if ($this->isLinkedToBill()) return 'bill';

			return 'unlinked';
		} catch (\Throwable $e) {
			Log::error(static::class . '::getEffectiveDocumentType — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return '';
		}
	}

	public function getEffectiveDocumentId(): ?string
	{
		try {
			if ($this->isLinkedToInvoice())
				return (string) $this->getAttribute($this->invoiceColumn());
			if ($this->isLinkedToBill())
				return (string) $this->getAttribute($this->billColumn());
			return null;
		} catch (\Throwable $e) {
			Log::error(static::class . '::getEffectiveDocumentId — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return '';
		}
	}

	public function getMonetarySign(): int
	{
		return $this->monetarySign();
	}

	public function normalizedAmount(): float
	{
		$amount = abs((float) ($this->amount ?? 0));

		return round($amount, 2);
	}

	public function appliesTo(float $baseAmount): float
	{
		$delta = $this->normalizedAmount() * $this->getMonetarySign();

		return round($baseAmount + $delta, 2);
	}

	public function setCardNumberAttribute(?string $value): void
	{
		try {
			if ($value === null || $value === '') {
				$this->attributes[BC::COL_CD_NB] = null;
				$this->attributes[BC::COL_CD_DG] = null;

				return;
			}

			$digits = preg_replace('/\D+/', '', $value) ?: null;
			$this->attributes[BC::COL_CD_NB] = $digits;
			$this->attributes[BC::COL_CD_DG] = $digits ? substr($digits, -4) : null;
		} catch (\Throwable $e) {
			Log::error(static::class . '::setCardNumberAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
	}

	public function setCardDigitsAttribute(?string $value): void
	{
		try {
			if (!$value) {
				$this->attributes[BC::COL_CD_DG] = null;

				return;
			}

			$digits = preg_replace('/\D+/', '', $value) ?: null;
			$this->attributes[BC::COL_CD_DG] = $digits ? substr($digits, -4) : null;
		} catch (\Throwable $e) {
			Log::error(static::class . '::setCardDigitsAttribute — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
	}

	protected function normalizedCurrentInstallment(): int
	{
		try {
			$current = (int) ($this->getAttribute(BC::COL_CURR_N_INTR) ?? 1);
			if ($current < 1)
				$current = 1;
			return $current;
		} catch (\Throwable $e) {
			Log::error(static::class . '::normalizedCurrentInstallment — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
			return 0;
		}
	}

	protected function normalizeCardExpiration(): void
	{
		try {
			$rawYear = $this->getAttribute(BC::COL_CD_EX_Y);
			$year    = (int) ($rawYear ?? 0);
			if ($year <= 0) {
				$this->setAttribute(BC::COL_CD_EX_Y, null);
				$this->setAttribute(BC::COL_CD_EX_M, null);
				return;
			}
			$now          = now();
			$currentYear  = (int) $now->format('Y');
			$currentMonth = (int) $now->format('n');
			if ($year < $currentYear)
				$year = $currentYear;
			$this->setAttribute(BC::COL_CD_EX_Y, (string) $year);
			$rawMonth = $this->getAttribute(BC::COL_CD_EX_M);
			if (!$rawMonth)
				return;
			$monthName = $rawMonth instanceof MonthName
				? strtolower($rawMonth->value)
				: strtolower((string) $rawMonth);
			$map = [
				'january'   => 1,
				'february'  => 2,
				'march'     => 3,
				'april'     => 4,
				'may'       => 5,
				'june'      => 6,
				'july'      => 7,
				'august'    => 8,
				'september' => 9,
				'october'   => 10,
				'november'  => 11,
				'december'  => 12,
			];
			$month = $map[$monthName] ?? null;
			if ($month === null)
				return;
			if ($year === $currentYear && $month < $currentMonth)
				foreach ($map as $name => $num)
					if ($num === $currentMonth) {
						$this->setAttribute(BC::COL_CD_EX_M, $name);
						break;
					}
		} catch (\Throwable $e) {
			Log::error(static::class . '::normalizeCardExpiration — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
	}


	protected function normalizeCardDigits(): void
	{
		try {
			if (!($this->attributes[BC::COL_CD_NB] ?? null)) return;

			$digits = preg_replace('/\D+/', '', $this->attributes[BC::COL_CD_NB]) ?: null;
			$this->attributes[BC::COL_CD_DG] = $digits ? substr($digits, -4) : null;
		} catch (\Throwable $e) {
			Log::error(static::class . '::normalizeCardDigits — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		}
	}
}
