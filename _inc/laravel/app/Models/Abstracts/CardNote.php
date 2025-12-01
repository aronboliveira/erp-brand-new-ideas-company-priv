<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\{MonthName, PaymentMethod, PaymentStatus, TransferType};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{
	Factories\HasFactory,
	Model,
	Relations\BelongsTo
};
use RuntimeException;

abstract class CardNote extends Model
{
	use HasAuditFields;
	use HasFactory;
	use UsesUuids;


	protected const BASE_FILLABLE = [
		BC::COL_CST_ID,
		'invoice',
		BC::COL_BL_ID,
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
		'customer',
		'invoice',
		'bill',
		'bankAccount',
		'category',
	];

	protected $with = self::BASE_WITH;

	protected static function booted(): void
	{
		static::creating(function (self $model): void {
			$model->{BC::COL_CURR_N_INTR} = $model->normalizedCurrentInstallment();
		});

		static::saving(function (self $model): void {
			$model->status = PaymentStatus::normalize($model->status ?? null)->value;

			if (!$model->{BC::COL_BL_ID} && !$model->{'invoice'})
				throw new RuntimeException('Card notes must be linked to a bill or an invoice.');

			$model->{BC::COL_CURR_N_INTR} = $model->normalizedCurrentInstallment();

			$model->normalizeCardExpiration();
			$model->normalizeCardDigits();
		});
	}

	abstract protected function monetarySign(): int;

	public function customer(): BelongsTo
	{
		return $this->belongsTo(Customer::class, BC::COL_CST_ID);
	}

	public function invoice(): BelongsTo
	{
		return $this->belongsTo(Invoice::class, 'invoice', 'id');
	}

	public function bill(): BelongsTo
	{
		return $this->belongsTo(Bill::class, BC::COL_BL_ID);
	}

	public function bankAccount(): BelongsTo
	{
		return $this->belongsTo(BankAccount::class, BC::COL_BACC_ID);
	}

	public function category(): BelongsTo
	{
		return $this->belongsTo(ProductServiceCategory::class, BC::COL_CAT_ID);
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
		return in_array(
			$this->paymentStatus(),
			[PaymentStatus::Refunded, PaymentStatus::PartiallyRefunded],
			true
		);
	}

	public function isLinkedToInvoice(): bool
	{
		return (bool) $this->{'invoice'};
	}

	public function isLinkedToBill(): bool
	{
		return (bool) $this->{BC::COL_BL_ID};
	}

	public function getEffectiveDocumentType(): string
	{
		if ($this->isLinkedToInvoice()) return 'invoice';
		if ($this->isLinkedToBill()) return 'bill';

		return 'unlinked';
	}

	public function getEffectiveDocumentId(): ?string
	{
		if ($this->isLinkedToInvoice()) return $this->{'invoice'};
		if ($this->isLinkedToBill()) return $this->{BC::COL_BL_ID};

		return null;
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
		if ($value === null || $value === '') {
			$this->attributes[BC::COL_CD_NB] = null;
			$this->attributes[BC::COL_CD_DG] = null;

			return;
		}

		$digits = preg_replace('/\D+/', '', $value) ?: null;
		$this->attributes[BC::COL_CD_NB] = $digits;
		$this->attributes[BC::COL_CD_DG] = $digits ? substr($digits, -4) : null;
	}

	public function setCardDigitsAttribute(?string $value): void
	{
		if (!$value) {
			$this->attributes[BC::COL_CD_DG] = null;

			return;
		}

		$digits = preg_replace('/\D+/', '', $value) ?: null;
		$this->attributes[BC::COL_CD_DG] = $digits ? substr($digits, -4) : null;
	}

	protected function normalizedCurrentInstallment(): int
	{
		$current = (int) ($this->{BC::COL_CURR_N_INTR} ?? 1);
		if ($current < 1) $current = 1;

		return $current;
	}

	protected function normalizeCardExpiration(): void
	{
		$year = (int) ($this->{BC::COL_CD_EX_Y} ?? 0);
		if ($year <= 0) {
			$this->{BC::COL_CD_EX_Y} = null;
			$this->{BC::COL_CD_EX_M} = null;

			return;
		}

		$now          = now();
		$currentYear  = (int) $now->format('Y');
		$currentMonth = (int) $now->format('n');

		if ($year < $currentYear) $year = $currentYear;
		$this->{BC::COL_CD_EX_Y} = (string) $year;

		$rawMonth = $this->{BC::COL_CD_EX_M};
		if (!$rawMonth) return;

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
		if ($month === null) return;

		if ($year === $currentYear && $month < $currentMonth) {
			foreach ($map as $name => $num)
				if ($num === $currentMonth) {
					$this->{BC::COL_CD_EX_M} = $name;

					break;
				}
		}
	}

	protected function normalizeCardDigits(): void
	{
		if (!($this->attributes[BC::COL_CD_NB] ?? null)) return;

		$digits = preg_replace('/\D+/', '', $this->attributes[BC::COL_CD_NB]) ?: null;
		$this->attributes[BC::COL_CD_DG] = $digits ? substr($digits, -4) : null;
	}
}
