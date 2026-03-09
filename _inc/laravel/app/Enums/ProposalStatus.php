<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum ProposalStatus: string
{
	case Draft     = 'draft';
	case Open      = 'open';
	case Accepted  = 'accepted';
	case Declined  = 'declined';
	case Close     = 'close';

	public static function normalize(string|null|self $value): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Draft;

		$v = mb_strtolower(trim($value));
		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// Draft
			'draft'                     => self::Draft,
			'drafts'                    => self::Draft,
			'preparation'               => self::Draft,
			'preparatory'               => self::Draft,
			'in_preparation'            => self::Draft,
			'in preparation'            => self::Draft,
			'in-preparation'            => self::Draft,
			'preparing'                 => self::Draft,
			'unpublished'               => self::Draft,
			'pending_review'            => self::Draft,
			'pending review'            => self::Draft,
			'pending-review'            => self::Draft,
			'under_review'              => self::Draft,
			'under review'              => self::Draft,
			'under-review'              => self::Draft,
			'rascunho'                  => self::Draft,
			'borrador'                  => self::Draft,
			'ébauche'                   => self::Draft,
			'entwurf'                   => self::Draft,
			'bozza'                     => self::Draft,
			'下書き'                    => self::Draft,
			'草案'                      => self::Draft,

			// Open
			'open'                      => self::Open,
			'opened'                    => self::Open,
			'sent'                      => self::Open,
			'submitted'                 => self::Open,
			'issued'                    => self::Open,
			'published'                 => self::Open,
			'active'                    => self::Open,
			'pending'                   => self::Open,
			'waiting'                   => self::Open,
			'under_consideration'       => self::Open,
			'under consideration'       => self::Open,
			'under-consideration'       => self::Open,
			'in_progress'               => self::Open,
			'in progress'               => self::Open,
			'in-progress'               => self::Open,
			'reviewing'                 => self::Open,
			'aberto'                    => self::Open,
			'enviado'                   => self::Open,
			'enviado'                   => self::Open,
			'envoyé'                    => self::Open,
			'offen'                     => self::Open,
			'aperto'                    => self::Open,
			'オープン'                  => self::Open,

			// Accepted
			'accepted'                  => self::Accepted,
			'approved'                  => self::Accepted,
			'approved'                  => self::Accepted,
			'confirmed'                 => self::Accepted,
			'confirmed'                 => self::Accepted,
			'signed'                    => self::Accepted,
			'contracted'                => self::Accepted,
			'won'                       => self::Accepted,
			'successful'                => self::Accepted,
			'aceito'                    => self::Accepted,
			'aprovado'                  => self::Accepted,
			'aceptado'                  => self::Accepted,
			'accepté'                   => self::Accepted,
			'akzeptiert'                => self::Accepted,
			'accettato'                 => self::Accepted,
			'承認済み'                  => self::Accepted,

			// Declined
			'declined'                  => self::Declined,
			'rejected'                  => self::Declined,
			'denied'                    => self::Declined,
			'refused'                   => self::Declined,
			'not_accepted'              => self::Declined,
			'not accepted'              => self::Declined,
			'not-accepted'              => self::Declined,
			'unaccepted'                => self::Declined,
			'unsuccessful'              => self::Declined,
			'lost'                      => self::Declined,
			'recusado'                  => self::Declined,
			'rejeitado'                 => self::Declined,
			'rechazado'                 => self::Declined,
			'refusé'                    => self::Declined,
			'abgelehnt'                 => self::Declined,
			'rifiutato'                 => self::Declined,
			'辞退'                      => self::Declined,
			'拒否'                      => self::Declined,

			// Close
			'close'                     => self::Close,
			'closed'                    => self::Close,
			'completed'                 => self::Close,
			'finished'                  => self::Close,
			'done'                      => self::Close,
			'finalized'                 => self::Close,
			'terminated'                => self::Close,
			'expired'                   => self::Close,
			'archived'                  => self::Close,
			'fechado'                   => self::Close,
			'cerrado'                   => self::Close,
			'terminado'                 => self::Close,
			'completado'                => self::Close,
			'fermé'                     => self::Close,
			'terminé'                   => self::Close,
			'geschlossen'               => self::Close,
			'chiuso'                    => self::Close,
			'完了'                      => self::Close,
			'終了'                      => self::Close,
		];

		return $map[$v] ?? self::Draft;
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
			self::Draft    => 'Draft',
			self::Open     => 'Open',
			self::Accepted => 'Accepted',
			self::Declined => 'Declined',
			self::Close    => 'Close',
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
			self::Draft->value    => 'Rascunho',
			self::Open->value     => 'Aberto',
			self::Accepted->value => 'Aceito',
			self::Declined->value => 'Recusado',
			self::Close->value    => 'Fechado',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Draft->value    => 'Draft',
			self::Open->value     => 'Open',
			self::Accepted->value => 'Accepted',
			self::Declined->value => 'Declined',
			self::Close->value    => 'Close',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Draft->value    => 'Borrador',
			self::Open->value     => 'Abierto',
			self::Accepted->value => 'Aceptado',
			self::Declined->value => 'Rechazado',
			self::Close->value    => 'Cerrado',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Draft->value    => 'مسودة',
			self::Open->value     => 'مفتوح',
			self::Accepted->value => 'مقبول',
			self::Declined->value => 'مرفوض',
			self::Close->value    => 'مغلق',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Draft->value    => 'Udkast',
			self::Open->value     => 'Åben',
			self::Accepted->value => 'Accepteret',
			self::Declined->value => 'Afvist',
			self::Close->value    => 'Lukket',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Draft->value    => 'Entwurf',
			self::Open->value     => 'Offen',
			self::Accepted->value => 'Akzeptiert',
			self::Declined->value => 'Abgelehnt',
			self::Close->value    => 'Geschlossen',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Draft->value    => 'Brouillon',
			self::Open->value     => 'Ouvert',
			self::Accepted->value => 'Accepté',
			self::Declined->value => 'Refusé',
			self::Close->value    => 'Fermé',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Draft->value    => 'טיוטה',
			self::Open->value     => 'פתוח',
			self::Accepted->value => 'מקובל',
			self::Declined->value => 'נדחה',
			self::Close->value    => 'סגור',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Draft->value    => 'Bozza',
			self::Open->value     => 'Aperto',
			self::Accepted->value => 'Accettato',
			self::Declined->value => 'Rifiutato',
			self::Close->value    => 'Chiuso',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Draft->value    => '下書き',
			self::Open->value     => 'オープン',
			self::Accepted->value => '承認済み',
			self::Declined->value => '辞退',
			self::Close->value    => '終了',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Draft->value    => 'Concept',
			self::Open->value     => 'Open',
			self::Accepted->value => 'Geaccepteerd',
			self::Declined->value => 'Afgewezen',
			self::Close->value    => 'Gesloten',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Draft->value    => 'Szkic',
			self::Open->value     => 'Otwarty',
			self::Accepted->value => 'Zaakceptowany',
			self::Declined->value => 'Odrzucony',
			self::Close->value    => 'Zamknięty',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Draft->value    => 'Черновик',
			self::Open->value     => 'Открыто',
			self::Accepted->value => 'Принято',
			self::Declined->value => 'Отклонено',
			self::Close->value    => 'Закрыто',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Draft->value    => 'Taslak',
			self::Open->value     => 'Açık',
			self::Accepted->value => 'Kabul Edildi',
			self::Declined->value => 'Reddedildi',
			self::Close->value    => 'Kapalı',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Draft->value    => '草稿',
			self::Open->value     => '开放',
			self::Accepted->value => '已接受',
			self::Declined->value => '已拒绝',
			self::Close->value    => '关闭',
		];
	}

	// Helper methods for business logic
	public function isActive(): bool
	{
		return match ($this) {
			self::Draft, self::Open => true,
			default => false,
		};
	}

	public function isFinalized(): bool
	{
		return match ($this) {
			self::Accepted, self::Declined, self::Close => true,
			default => false,
		};
	}

	public function isPositive(): bool
	{
		return $this === self::Accepted;
	}

	public function isNegative(): bool
	{
		return match ($this) {
			self::Declined, self::Close => true,
			default => false,
		};
	}

	public function isPending(): bool
	{
		return match ($this) {
			self::Draft, self::Open => true,
			default => false,
		};
	}

	public function isEditable(): bool
	{
		return match ($this) {
			self::Draft => true,
			default => false,
		};
	}

	public function isSendable(): bool
	{
		return match ($this) {
			self::Draft, self::Open => true,
			default => false,
		};
	}

	public function isRespondable(): bool
	{
		return $this === self::Open;
	}

	public function getIcon(): string
	{
		return match ($this) {
			self::Draft    => 'file-text',
			self::Open     => 'mail',
			self::Accepted => 'check-circle',
			self::Declined => 'x-circle',
			self::Close    => 'archive',
		};
	}

	public function getColor(): string
	{
		return match ($this) {
			self::Draft    => 'gray',
			self::Open     => 'blue',
			self::Accepted => 'green',
			self::Declined => 'red',
			self::Close    => 'purple',
		};
	}

	public function getWeight(): int
	{
		return match ($this) {
			self::Draft    => 1,
			self::Open     => 2,
			self::Accepted => 3,
			self::Declined => 4,
			self::Close    => 5,
		};
	}

	public static function fromWeight(int $weight): self
	{
		return match ($weight) {
			1 => self::Draft,
			2 => self::Open,
			3 => self::Accepted,
			4 => self::Declined,
			5 => self::Close,
			default => self::Draft,
		};
	}

	public function getPriority(): int
	{
		return match ($this) {
			self::Open     => 3, // Highest priority - needs attention
			self::Draft    => 2, // Medium priority - needs completion
			self::Accepted => 1, // Low priority - done
			self::Declined => 0, // No priority - rejected
			self::Close    => 0, // No priority - closed
		};
	}

	public function canBeConvertedToInvoice(): bool
	{
		return $this === self::Accepted;
	}

	public function nextStatus(): ?self
	{
		return match ($this) {
			self::Draft    => self::Open,
			self::Open     => self::Accepted,
			self::Accepted => self::Close,
			self::Declined => self::Close,
			self::Close    => null,
		};
	}

	public function previousStatus(): ?self
	{
		return match ($this) {
			self::Draft    => null,
			self::Open     => self::Draft,
			self::Accepted => self::Open,
			self::Declined => self::Open,
			self::Close    => self::Accepted,
		};
	}

	public function getLifecycleStage(): string
	{
		return match ($this) {
			self::Draft    => 'preparation',
			self::Open     => 'submission',
			self::Accepted => 'win',
			self::Declined => 'loss',
			self::Close    => 'archival',
		};
	}

	public function allowsRevision(): bool
	{
		return match ($this) {
			self::Draft, self::Declined => true,
			default => false,
		};
	}

	public function getActionRequired(): string
	{
		return match ($this) {
			self::Draft    => 'Complete and send proposal',
			self::Open     => 'Awaiting client response',
			self::Accepted => 'Convert to invoice or contract',
			self::Declined => 'Follow up or revise',
			self::Close    => 'No action required',
		};
	}
}
