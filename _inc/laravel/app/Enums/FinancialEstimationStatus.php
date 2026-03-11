<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum FinancialEstimationStatus: string
{
	case Draft           = 'draft';
	case Open            = 'open';
	case NotPaid         = 'not_paid';
	case PartiallyPaid   = 'partially_paid';
	case Paid            = 'paid';
	case Cancelled       = 'cancelled';

	public static function normalize(string|null|self $value): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Open;

		$v = mb_strtolower(trim($value));
		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// Open
			'open'                      => self::Open,
			'opened'                    => self::Open,
			'pending'                   => self::Open,
			'in_progress'               => self::Open,
			'in progress'               => self::Open,
			'in-progress'               => self::Open,
			'aberto'                    => self::Open,
			'pendente'                  => self::Open,
			'em_aberto'                 => self::Open,
			'abierto'                   => self::Open,
			'pendiente'                 => self::Open,

			// Not Paid
			'not_paid'                  => self::NotPaid,
			'not paid'                  => self::NotPaid,
			'not-paid'                  => self::NotPaid,
			'unpaid'                    => self::NotPaid,
			'outstanding'               => self::NotPaid,
			'due'                       => self::NotPaid,
			'overdue'                   => self::NotPaid,
			'não_pago'                  => self::NotPaid,
			'não pago'                  => self::NotPaid,
			'no pagado'                 => self::NotPaid,
			'impayé'                    => self::NotPaid,
			'nicht bezahlt'             => self::NotPaid,
			'non pagato'                => self::NotPaid,

			// Partially Paid
			'partially_paid'            => self::PartiallyPaid,
			'partially paid'            => self::PartiallyPaid,
			'partially-paid'            => self::PartiallyPaid,
			'partial'                   => self::PartiallyPaid,
			'partial_payment'           => self::PartiallyPaid,
			'partial payment'           => self::PartiallyPaid,
			'partial-payment'           => self::PartiallyPaid,
			'partially'                 => self::PartiallyPaid,
			'parcialmente_pago'         => self::PartiallyPaid,
			'parcialmente pago'         => self::PartiallyPaid,
			'parcial'                   => self::PartiallyPaid,
			'pagamento_parcial'         => self::PartiallyPaid,
			'pagamento parcial'         => self::PartiallyPaid,
			'partiellement_payé'        => self::PartiallyPaid,
			'teilweise_bezahlt'         => self::PartiallyPaid,
			'parzialmente_pagato'       => self::PartiallyPaid,

			// Paid
			'paid'                      => self::Paid,
			'completed'                 => self::Paid,
			'settled'                   => self::Paid,
			'finished'                  => self::Paid,
			'done'                      => self::Paid,
			'closed'                    => self::Paid,
			'finalized'                 => self::Paid,
			'pago'                      => self::Paid,
			'pagado'                    => self::Paid,
			'payé'                      => self::Paid,
			'bezahlt'                   => self::Paid,
			'pagato'                    => self::Paid,
			'支払済み'                  => self::Paid,

			// Cancelled
			'cancelled'                 => self::Cancelled,
			'canceled'                  => self::Cancelled,
			'cancel'                    => self::Cancelled,
			'void'                      => self::Cancelled,
			'voided'                    => self::Cancelled,
			'rejected'                  => self::Cancelled,
			'denied'                    => self::Cancelled,
			'declined'                  => self::Cancelled,
			'refused'                   => self::Cancelled,
			'cancelado'                 => self::Cancelled,
			'annulé'                    => self::Cancelled,
			'annullato'                 => self::Cancelled,
			'storniert'                 => self::Cancelled,
			'取り消し'                  => self::Cancelled,
			'取消'                      => self::Cancelled,
		];

		return $map[$v] ?? self::Open;
	}

	public static function isValidValue(string $value): bool
	{
		return self::tryFrom($value) !== null;
	}

	public static function values(): array
	{
		return array_column(self::cases(), 'value');
	}

	public static function names(): array
	{
		return array_column(self::cases(), 'name');
	}

	public function label(): string
	{
		return match ($this) {
			self::Draft         => 'Draft',
			self::Open          => 'Open',
			self::NotPaid       => 'Not Paid',
			self::PartiallyPaid => 'Partially Paid',
			self::Paid          => 'Paid',
			self::Cancelled     => 'Cancelled',
		};
	}

	public static function labels($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		return match ($lang) {
			'pt-br', 'pt' => self::labelsPtBr(),
			'es', 'es-es' => self::labelsEs(),
			'ar', 'ar-sa' => self::labelsAr(),
			'da', 'da-dk' => self::labelsDa(),
			'de', 'de-de' => self::labelsDe(),
			'fr', 'fr-fr' => self::labelsFr(),
			'he', 'he-il' => self::labelsHe(),
			'it', 'it-it' => self::labelsIt(),
			'ja', 'ja-jp' => self::labelsJa(),
			'nl', 'nl-nl' => self::labelsNl(),
			'pl', 'pl-pl' => self::labelsPl(),
			'ru', 'ru-ru' => self::labelsRu(),
			'tr', 'tr-tr' => self::labelsTr(),
			'zh', 'zh-cn' => self::labelsZh(),
			default => self::labelsEn(),
		};
	}

	public static function labelsPtBr(): array
	{
		return [
			self::Open->value          => 'Aberto',
			self::NotPaid->value       => 'Não Pago',
			self::PartiallyPaid->value => 'Parcialmente Pago',
			self::Paid->value          => 'Pago',
			self::Cancelled->value     => 'Cancelado',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Open->value          => 'Open',
			self::NotPaid->value       => 'Not Paid',
			self::PartiallyPaid->value => 'Partially Paid',
			self::Paid->value          => 'Paid',
			self::Cancelled->value     => 'Cancelled',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Open->value          => 'Abierto',
			self::NotPaid->value       => 'No Pagado',
			self::PartiallyPaid->value => 'Parcialmente Pagado',
			self::Paid->value          => 'Pagado',
			self::Cancelled->value     => 'Cancelado',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Open->value          => 'مفتوح',
			self::NotPaid->value       => 'غير مدفوع',
			self::PartiallyPaid->value => 'مدفوع جزئياً',
			self::Paid->value          => 'مدفوع',
			self::Cancelled->value     => 'ملغي',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Open->value          => 'Åben',
			self::NotPaid->value       => 'Ikke Betalt',
			self::PartiallyPaid->value => 'Delvist Betalt',
			self::Paid->value          => 'Betalt',
			self::Cancelled->value     => 'Annulleret',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Open->value          => 'Offen',
			self::NotPaid->value       => 'Nicht Bezahlt',
			self::PartiallyPaid->value => 'Teilweise Bezahlt',
			self::Paid->value          => 'Bezahlt',
			self::Cancelled->value     => 'Storniert',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Open->value          => 'Ouvert',
			self::NotPaid->value       => 'Non Payé',
			self::PartiallyPaid->value => 'Partiellement Payé',
			self::Paid->value          => 'Payé',
			self::Cancelled->value     => 'Annulé',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Open->value          => 'פתוח',
			self::NotPaid->value       => 'לא שולם',
			self::PartiallyPaid->value => 'שולם חלקית',
			self::Paid->value          => 'שולם',
			self::Cancelled->value     => 'מבוטל',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Open->value          => 'Aperto',
			self::NotPaid->value       => 'Non Pagato',
			self::PartiallyPaid->value => 'Parzialmente Pagato',
			self::Paid->value          => 'Pagato',
			self::Cancelled->value     => 'Annullato',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Open->value          => '未処理',
			self::NotPaid->value       => '未払い',
			self::PartiallyPaid->value => '一部支払済み',
			self::Paid->value          => '支払済み',
			self::Cancelled->value     => 'キャンセル済み',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Open->value          => 'Open',
			self::NotPaid->value       => 'Niet Betaald',
			self::PartiallyPaid->value => 'Gedeeltelijk Betaald',
			self::Paid->value          => 'Betaald',
			self::Cancelled->value     => 'Geannuleerd',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Open->value          => 'Otwarty',
			self::NotPaid->value       => 'Nie Zapłacony',
			self::PartiallyPaid->value => 'Częściowo Zapłacony',
			self::Paid->value          => 'Zapłacony',
			self::Cancelled->value     => 'Anulowany',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Open->value          => 'Открыто',
			self::NotPaid->value       => 'Не Оплачено',
			self::PartiallyPaid->value => 'Частично Оплачено',
			self::Paid->value          => 'Оплачено',
			self::Cancelled->value     => 'Отменено',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Open->value          => 'Açık',
			self::NotPaid->value       => 'Ödenmemiş',
			self::PartiallyPaid->value => 'Kısmen Ödenmiş',
			self::Paid->value          => 'Ödenmiş',
			self::Cancelled->value     => 'İptal Edilmiş',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Open->value          => '未处理',
			self::NotPaid->value       => '未支付',
			self::PartiallyPaid->value => '部分支付',
			self::Paid->value          => '已支付',
			self::Cancelled->value     => '已取消',
		];
	}

	// Helper methods for business logic
	public function isOpen(): bool
	{
		return match ($this) {
			self::Open, self::NotPaid, self::PartiallyPaid => true,
			default => false,
		};
	}

	public function isClosed(): bool
	{
		return match ($this) {
			self::Paid, self::Cancelled => true,
			default => false,
		};
	}

	public function isPendingPayment(): bool
	{
		return match ($this) {
			self::Open, self::NotPaid, self::PartiallyPaid => true,
			default => false,
		};
	}

	public function isPaid(): bool
	{
		return $this === self::Paid;
	}

	public function isCancelled(): bool
	{
		return $this === self::Cancelled;
	}

	public function isPartiallyPaid(): bool
	{
		return $this === self::PartiallyPaid;
	}

	public function isNotPaid(): bool
	{
		return $this === self::NotPaid;
	}

	public function getIcon(): string
	{
		return match ($this) {
			self::Draft         => 'file-text',
			self::Open          => 'clock',
			self::NotPaid       => 'alert-circle',
			self::PartiallyPaid => 'pie-chart',
			self::Paid          => 'check-circle',
			self::Cancelled     => 'x-circle',
		};
	}

	public function getColor(): string
	{
		return match ($this) {
			self::Draft         => 'blue',
			self::Open          => 'yellow',
			self::NotPaid       => 'red',
			self::PartiallyPaid => 'orange',
			self::Paid          => 'green',
			self::Cancelled     => 'gray',
		};
	}

	public function getWeight(): int
	{
		return match ($this) {
			self::Draft         => -1,
			self::Open          => 1,
			self::NotPaid       => 2,
			self::PartiallyPaid => 3,
			self::Paid          => 4,
			self::Cancelled     => 0,
		};
	}

	public static function fromWeight(int $weight): self
	{
		return match ($weight) {
			-1 => self::Draft,
			0 => self::Cancelled,
			1 => self::Open,
			2 => self::NotPaid,
			3 => self::PartiallyPaid,
			4 => self::Paid,
			default => self::Open,
		};
	}

	public function allowsEditing(): bool
	{
		return match ($this) {
			self::Open, self::NotPaid, self::PartiallyPaid => true,
			default => false,
		};
	}

	public function allowsPayment(): bool
	{
		return match ($this) {
			self::Open, self::NotPaid, self::PartiallyPaid => true,
			default => false,
		};
	}

	public function nextStatus(): ?self
	{
		return match ($this) {
			self::Draft         => self::Open,
			self::Open          => self::NotPaid,
			self::NotPaid       => self::PartiallyPaid,
			self::PartiallyPaid => self::Paid,
			self::Paid          => null,
			self::Cancelled     => null,
		};
	}

	public function previousStatus(): ?self
	{
		return match ($this) {
			self::Draft         => null,
			self::Open          => null,
			self::NotPaid       => self::Open,
			self::PartiallyPaid => self::NotPaid,
			self::Paid          => self::PartiallyPaid,
			self::Cancelled     => null,
		};
	}
}
