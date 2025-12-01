<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum EventRole: string
{
	case Responsible = 'responsible';
	case Organizer = 'organizer';
	case Sponsor = 'sponsor';
	case Speaker = 'speaker';
	case Attendee = 'attendee';
	case Volunteer = 'volunteer';

	public static function normalize(?string $value): self
	{
		if ($value === null)
			return self::Attendee;
		$v = strtolower(trim($value));
		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;
		$map = [
			// Responsible
			'responsavel'     => self::Responsible,
			'responsável'     => self::Responsible,
			'owner'           => self::Responsible,
			'manager'         => self::Responsible,
			'admin'           => self::Responsible,
			'administrator'   => self::Responsible,

			// Organizer
			'organizador'     => self::Organizer,
			'organizadora'    => self::Organizer,
			'coordinator'     => self::Organizer,
			'coordenador'     => self::Organizer,
			'coordenadora'    => self::Organizer,

			// Sponsor
			'sponsor'         => self::Sponsor,
			'patrocinador'    => self::Sponsor,
			'patrocinadora'   => self::Sponsor,
			'financiador'     => self::Sponsor,
			'financiadora'    => self::Sponsor,

			// Speaker
			'speaker'         => self::Speaker,
			'palestrante'     => self::Speaker,
			'presenter'       => self::Speaker,
			'presentador'     => self::Speaker,
			'presentadora'    => self::Speaker,
			'host'            => self::Speaker,

			// Attendee
			'attendee'        => self::Attendee,
			'participant'     => self::Attendee,
			'participante'    => self::Attendee,
			'guest'           => self::Attendee,
			'convidado'       => self::Attendee,
			'convidada'       => self::Attendee,

			// Volunteer
			'volunteer'       => self::Volunteer,
			'voluntario'      => self::Volunteer,
			'voluntária'      => self::Volunteer,
			'helper'          => self::Volunteer,
			'ajudante'        => self::Volunteer,
		];

		return $map[$v] ?? self::Attendee;
	}

	public static function values(): array
	{
		return array_map(fn(EventRole $role) => $role->value, self::cases());
	}

	public function label(): string
	{
		return match ($this) {
			self::Responsible => 'Responsible',
			self::Organizer   => 'Organizer',
			self::Sponsor     => 'Sponsor',
			self::Speaker     => 'Speaker',
			self::Attendee    => 'Attendee',
			self::Volunteer   => 'Volunteer',
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
			self::Responsible->value => 'Responsável',
			self::Organizer->value   => 'Organizador',
			self::Sponsor->value     => 'Patrocinador',
			self::Speaker->value     => 'Palestrante',
			self::Attendee->value    => 'Participante',
			self::Volunteer->value   => 'Voluntário',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Responsible->value => 'Responsible',
			self::Organizer->value   => 'Organizer',
			self::Sponsor->value     => 'Sponsor',
			self::Speaker->value     => 'Speaker',
			self::Attendee->value    => 'Attendee',
			self::Volunteer->value   => 'Volunteer',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Responsible->value => 'Responsable',
			self::Organizer->value   => 'Organizador',
			self::Sponsor->value     => 'Patrocinador',
			self::Speaker->value     => 'Ponente',
			self::Attendee->value    => 'Asistente',
			self::Volunteer->value   => 'Voluntario',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Responsible->value => 'مسؤول',
			self::Organizer->value   => 'منظم',
			self::Sponsor->value     => 'راعي',
			self::Speaker->value     => 'متحدث',
			self::Attendee->value    => 'حاضر',
			self::Volunteer->value   => 'متطوع',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Responsible->value => 'Ansvarlig',
			self::Organizer->value   => 'Arrangør',
			self::Sponsor->value     => 'Sponsor',
			self::Speaker->value     => 'Taler',
			self::Attendee->value    => 'Deltager',
			self::Volunteer->value   => 'Frivillig',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Responsible->value => 'Verantwortlich',
			self::Organizer->value   => 'Organisator',
			self::Sponsor->value     => 'Sponsor',
			self::Speaker->value     => 'Redner',
			self::Attendee->value    => 'Teilnehmer',
			self::Volunteer->value   => 'Freiwilliger',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Responsible->value => 'Responsable',
			self::Organizer->value   => 'Organisateur',
			self::Sponsor->value     => 'Sponsor',
			self::Speaker->value     => 'Conférencier',
			self::Attendee->value    => 'Participant',
			self::Volunteer->value   => 'Bénévole',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Responsible->value => 'אחראי',
			self::Organizer->value   => 'מארגן',
			self::Sponsor->value     => 'נותן חסות',
			self::Speaker->value     => 'דובר',
			self::Attendee->value    => 'משתתף',
			self::Volunteer->value   => 'מתנדב',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Responsible->value => 'Responsabile',
			self::Organizer->value   => 'Organizzatore',
			self::Sponsor->value     => 'Sponsor',
			self::Speaker->value     => 'Relatore',
			self::Attendee->value    => 'Partecipante',
			self::Volunteer->value   => 'Volontario',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Responsible->value => '責任者',
			self::Organizer->value   => '主催者',
			self::Sponsor->value     => 'スポンサー',
			self::Speaker->value     => 'スピーカー',
			self::Attendee->value    => '参加者',
			self::Volunteer->value   => 'ボランティア',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Responsible->value => 'Verantwoordelijke',
			self::Organizer->value   => 'Organisator',
			self::Sponsor->value     => 'Sponsor',
			self::Speaker->value     => 'Spreker',
			self::Attendee->value    => 'Deelnemer',
			self::Volunteer->value   => 'Vrijwilliger',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Responsible->value => 'Odpowiedzialny',
			self::Organizer->value   => 'Organizator',
			self::Sponsor->value     => 'Sponsor',
			self::Speaker->value     => 'Prelegent',
			self::Attendee->value    => 'Uczestnik',
			self::Volunteer->value   => 'Wolontariusz',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Responsible->value => 'Ответственный',
			self::Organizer->value   => 'Организатор',
			self::Sponsor->value     => 'Спонсор',
			self::Speaker->value     => 'Докладчик',
			self::Attendee->value    => 'Участник',
			self::Volunteer->value   => 'Волонтер',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Responsible->value => 'Sorumlu',
			self::Organizer->value   => 'Organizatör',
			self::Sponsor->value     => 'Sponsor',
			self::Speaker->value     => 'Konuşmacı',
			self::Attendee->value    => 'Katılımcı',
			self::Volunteer->value   => 'Gönüllü',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Responsible->value => '负责人',
			self::Organizer->value   => '组织者',
			self::Sponsor->value     => '赞助商',
			self::Speaker->value     => '演讲者',
			self::Attendee->value    => '参与者',
			self::Volunteer->value   => '志愿者',
		];
	}

	public function isOrganizational(): bool
	{
		return match ($this) {
			self::Responsible, self::Organizer, self::Sponsor => true,
			default => false,
		};
	}

	public function isParticipant(): bool
	{
		return match ($this) {
			self::Speaker, self::Attendee, self::Volunteer => true,
			default => false,
		};
	}

	public function isStaff(): bool
	{
		return match ($this) {
			self::Responsible, self::Organizer, self::Volunteer => true,
			default => false,
		};
	}

	public function getEventMapping(): string
	{
		return match ($this) {
			self::Speaker, self::Attendee, self::Volunteer => 'participant',
			self::Responsible, self::Organizer => 'host',
			self::Sponsor => 'sponsor',
		};
	}

	public function getPriority(): int
	{
		return match ($this) {
			self::Responsible => 1,
			self::Organizer   => 2,
			self::Sponsor     => 3,
			self::Speaker     => 4,
			self::Volunteer   => 5,
			self::Attendee    => 6,
		};
	}
}
