<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum AttendanceStatus: string
{
	case Present = 'present';
	case Absent = 'absent';
	case Leave = 'leave';
	case Remote = 'remote';

	public static function normalize(?string $value): self
	{
		if ($value === null)
			return self::Absent;
		$v = strtolower(trim($value));
		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;
		$map = [
			// Present
			'present'       => self::Present,
			'presente'      => self::Present,
			'presente'      => self::Present,
			'attended'      => self::Present,
			'participated'  => self::Present,
			'yes'           => self::Present,
			'sim'           => self::Present,
			'sí'            => self::Present,
			'1'             => self::Present,
			'true'          => self::Present,
			'atendido'      => self::Present,
			'asistió'       => self::Present,

			// Absent
			'absent'        => self::Absent,
			'ausente'       => self::Absent,
			'faltou'        => self::Absent,
			'falta'         => self::Absent,
			'missing'       => self::Absent,
			'no'            => self::Absent,
			'não'           => self::Absent,
			'no'            => self::Absent,
			'0'             => self::Absent,
			'false'         => self::Absent,
			'not attended'  => self::Absent,
			'no asistió'    => self::Absent,

			// Leave
			'leave'         => self::Leave,
			'licença'       => self::Leave,
			'licencia'      => self::Leave,
			'férias'        => self::Leave,
			'vacaciones'    => self::Leave,
			'vacation'      => self::Leave,
			'holiday'       => self::Leave,
			'day off'       => self::Leave,
			'permission'    => self::Leave,
			'permissão'     => self::Leave,
			'permiso'       => self::Leave,
			'sick'          => self::Leave,
			'doente'        => self::Leave,
			'enfermo'       => self::Leave,

			// Remote
			'remote'        => self::Remote,
			'remoto'        => self::Remote,
			'remota'        => self::Remote,
			'home office'   => self::Remote,
			'teletrabalho'  => self::Remote,
			'teletrabajo'   => self::Remote,
			'work from home' => self::Remote,
			'wfh'           => self::Remote,
			'online'        => self::Remote,
			'virtual'       => self::Remote,
		];

		return $map[$v] ?? self::Absent;
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public function label(): string
	{
		return match ($this) {
			self::Present => 'Present',
			self::Absent  => 'Absent',
			self::Leave   => 'Leave',
			self::Remote  => 'Remote',
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
			self::Present->value => 'Presente',
			self::Absent->value  => 'Ausente',
			self::Leave->value   => 'Licença',
			self::Remote->value  => 'Remoto',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Present->value => 'Present',
			self::Absent->value  => 'Absent',
			self::Leave->value   => 'Leave',
			self::Remote->value  => 'Remote',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Present->value => 'Presente',
			self::Absent->value  => 'Ausente',
			self::Leave->value   => 'Licencia',
			self::Remote->value  => 'Remoto',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Present->value => 'حاضر',
			self::Absent->value  => 'غائب',
			self::Leave->value   => 'إجازة',
			self::Remote->value  => 'عن بُعد',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Present->value => 'Tilstede',
			self::Absent->value  => 'Fraværende',
			self::Leave->value   => 'Orlov',
			self::Remote->value  => 'Fjern',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Present->value => 'Anwesend',
			self::Absent->value  => 'Abwesend',
			self::Leave->value   => 'Urlaub',
			self::Remote->value  => 'Remote',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Present->value => 'Présent',
			self::Absent->value  => 'Absent',
			self::Leave->value   => 'Congé',
			self::Remote->value  => 'À distance',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Present->value => 'נוכח',
			self::Absent->value  => 'נעדר',
			self::Leave->value   => 'חופשה',
			self::Remote->value  => 'מרוחק',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Present->value => 'Presente',
			self::Absent->value  => 'Assente',
			self::Leave->value   => 'Permesso',
			self::Remote->value  => 'Remoto',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Present->value => '出席',
			self::Absent->value  => '欠席',
			self::Leave->value   => '休暇',
			self::Remote->value  => 'リモート',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Present->value => 'Aanwezig',
			self::Absent->value  => 'Afwezig',
			self::Leave->value   => 'Verlof',
			self::Remote->value  => 'Remote',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Present->value => 'Obecny',
			self::Absent->value  => 'Nieobecny',
			self::Leave->value   => 'Urlop',
			self::Remote->value  => 'Zdalny',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Present->value => 'Присутствует',
			self::Absent->value  => 'Отсутствует',
			self::Leave->value   => 'Отпуск',
			self::Remote->value  => 'Удаленно',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Present->value => 'Mevcut',
			self::Absent->value  => 'Yok',
			self::Leave->value   => 'İzin',
			self::Remote->value  => 'Uzaktan',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Present->value => '出席',
			self::Absent->value  => '缺席',
			self::Leave->value   => '休假',
			self::Remote->value  => '远程',
		];
	}

	// Helper methods for business logic
	public function isPresent(): bool
	{
		return match ($this) {
			self::Present, self::Remote => true,
			default => false,
		};
	}

	public function isAbsent(): bool
	{
		return match ($this) {
			self::Absent, self::Leave => true,
			default => false,
		};
	}

	public function isAuthorizedAbsence(): bool
	{
		return match ($this) {
			self::Leave, self::Remote => true,
			default => false,
		};
	}

	public function isPhysical(): bool
	{
		return $this === self::Present;
	}

	public function isVirtual(): bool
	{
		return $this === self::Remote;
	}

	public function getColor(): string
	{
		return match ($this) {
			self::Present => 'green',
			self::Absent  => 'red',
			self::Leave   => 'orange',
			self::Remote  => 'blue',
		};
	}

	public function getIcon(): string
	{
		return match ($this) {
			self::Present => 'check-circle',
			self::Absent  => 'x-circle',
			self::Leave   => 'calendar',
			self::Remote  => 'wifi',
		};
	}
}
