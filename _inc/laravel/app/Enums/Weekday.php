<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum Weekday: string
{
	case Monday    = 'monday';
	case Tuesday   = 'tuesday';
	case Wednesday = 'wednesday';
	case Thursday  = 'thursday';
	case Friday    = 'friday';
	case Saturday  = 'saturday';
	case Sunday    = 'sunday';

	public static function values(): array
	{
		return [
			self::Monday->value,
			self::Tuesday->value,
			self::Wednesday->value,
			self::Thursday->value,
			self::Friday->value,
			self::Saturday->value,
			self::Sunday->value,
		];
	}

	public static function ordered($startWithMonday = true): array
	{
		$days = [
			self::Monday,
			self::Tuesday,
			self::Wednesday,
			self::Thursday,
			self::Friday,
			self::Saturday,
			self::Sunday,
		];

		if (!$startWithMonday) {
			// Start with Sunday (common in some regions)
			return [
				self::Sunday,
				self::Monday,
				self::Tuesday,
				self::Wednesday,
				self::Thursday,
				self::Friday,
				self::Saturday,
			];
		}

		return $days;
	}

	public static function normalize(string|null|self $value): ?self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return null;
		$v = strtolower(trim($value));
		if ($v === '')
			return null;

		$map = [
			// Monday
			'1'       => self::Monday,
			'01'      => self::Monday,
			'mon'     => self::Monday,
			'monday'  => self::Monday,
			'segunda' => self::Monday,
			'segunda-feira' => self::Monday,
			'lunes'   => self::Monday,
			'lundi'   => self::Monday,
			'montag'  => self::Monday,

			// Tuesday
			'2'       => self::Tuesday,
			'02'      => self::Tuesday,
			'tue'     => self::Tuesday,
			'tues'    => self::Tuesday,
			'tuesday' => self::Tuesday,
			'terça'   => self::Tuesday,
			'terca'   => self::Tuesday,
			'terça-feira' => self::Tuesday,
			'martes'  => self::Tuesday,
			'mardi'   => self::Tuesday,
			'dienstag' => self::Tuesday,

			// Wednesday
			'3'       => self::Wednesday,
			'03'      => self::Wednesday,
			'wed'     => self::Wednesday,
			'wednesday' => self::Wednesday,
			'quarta'  => self::Wednesday,
			'quarta-feira' => self::Wednesday,
			'miércoles' => self::Wednesday,
			'miercoles' => self::Wednesday,
			'mercredi' => self::Wednesday,
			'mittwoch' => self::Wednesday,

			// Thursday
			'4'       => self::Thursday,
			'04'      => self::Thursday,
			'thu'     => self::Thursday,
			'thur'    => self::Thursday,
			'thurs'   => self::Thursday,
			'thursday' => self::Thursday,
			'quinta'  => self::Thursday,
			'quinta-feira' => self::Thursday,
			'jueves'  => self::Thursday,
			'jeudi'   => self::Thursday,
			'donnerstag' => self::Thursday,

			// Friday
			'5'       => self::Friday,
			'05'      => self::Friday,
			'fri'     => self::Friday,
			'friday'  => self::Friday,
			'sexta'   => self::Friday,
			'sexta-feira' => self::Friday,
			'viernes' => self::Friday,
			'vendredi' => self::Friday,
			'freitag' => self::Friday,

			// Saturday
			'6'       => self::Saturday,
			'06'      => self::Saturday,
			'sat'     => self::Saturday,
			'saturday' => self::Saturday,
			'sábado'  => self::Saturday,
			'sabado'  => self::Saturday,
			'samedi'  => self::Saturday,
			'samstag' => self::Saturday,

			// Sunday
			'7'       => self::Sunday,
			'07'      => self::Sunday,
			'sun'     => self::Sunday,
			'sunday'  => self::Sunday,
			'domingo' => self::Sunday,
			'dimanche' => self::Sunday,
			'sonntag' => self::Sunday,
		];

		return $map[$v] ?? null;
	}

	public function isoIndex(): int
	{
		return match ($this) {
			self::Monday    => 1,
			self::Tuesday   => 2,
			self::Wednesday => 3,
			self::Thursday  => 4,
			self::Friday    => 5,
			self::Saturday  => 6,
			self::Sunday    => 7,
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
			self::Monday->value    => 'Segunda-feira',
			self::Tuesday->value   => 'Terça-feira',
			self::Wednesday->value => 'Quarta-feira',
			self::Thursday->value  => 'Quinta-feira',
			self::Friday->value    => 'Sexta-feira',
			self::Saturday->value  => 'Sábado',
			self::Sunday->value    => 'Domingo',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Monday->value    => 'Monday',
			self::Tuesday->value   => 'Tuesday',
			self::Wednesday->value => 'Wednesday',
			self::Thursday->value  => 'Thursday',
			self::Friday->value    => 'Friday',
			self::Saturday->value  => 'Saturday',
			self::Sunday->value    => 'Sunday',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Monday->value    => 'Lunes',
			self::Tuesday->value   => 'Martes',
			self::Wednesday->value => 'Miércoles',
			self::Thursday->value  => 'Jueves',
			self::Friday->value    => 'Viernes',
			self::Saturday->value  => 'Sábado',
			self::Sunday->value    => 'Domingo',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Monday->value    => 'الاثنين',
			self::Tuesday->value   => 'الثلاثاء',
			self::Wednesday->value => 'الأربعاء',
			self::Thursday->value  => 'الخميس',
			self::Friday->value    => 'الجمعة',
			self::Saturday->value  => 'السبت',
			self::Sunday->value    => 'الأحد',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Monday->value    => 'Mandag',
			self::Tuesday->value   => 'Tirsdag',
			self::Wednesday->value => 'Onsdag',
			self::Thursday->value  => 'Torsdag',
			self::Friday->value    => 'Fredag',
			self::Saturday->value  => 'Lørdag',
			self::Sunday->value    => 'Søndag',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Monday->value    => 'Montag',
			self::Tuesday->value   => 'Dienstag',
			self::Wednesday->value => 'Mittwoch',
			self::Thursday->value  => 'Donnerstag',
			self::Friday->value    => 'Freitag',
			self::Saturday->value  => 'Samstag',
			self::Sunday->value    => 'Sonntag',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Monday->value    => 'Lundi',
			self::Tuesday->value   => 'Mardi',
			self::Wednesday->value => 'Mercredi',
			self::Thursday->value  => 'Jeudi',
			self::Friday->value    => 'Vendredi',
			self::Saturday->value  => 'Samedi',
			self::Sunday->value    => 'Dimanche',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Monday->value    => 'יום שני',
			self::Tuesday->value   => 'יום שלישי',
			self::Wednesday->value => 'יום רביעי',
			self::Thursday->value  => 'יום חמישי',
			self::Friday->value    => 'יום שישי',
			self::Saturday->value  => 'יום שבת',
			self::Sunday->value    => 'יום ראשון',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Monday->value    => 'Lunedì',
			self::Tuesday->value   => 'Martedì',
			self::Wednesday->value => 'Mercoledì',
			self::Thursday->value  => 'Giovedì',
			self::Friday->value    => 'Venerdì',
			self::Saturday->value  => 'Sabato',
			self::Sunday->value    => 'Domenica',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Monday->value    => '月曜日',
			self::Tuesday->value   => '火曜日',
			self::Wednesday->value => '水曜日',
			self::Thursday->value  => '木曜日',
			self::Friday->value    => '金曜日',
			self::Saturday->value  => '土曜日',
			self::Sunday->value    => '日曜日',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Monday->value    => 'Maandag',
			self::Tuesday->value   => 'Dinsdag',
			self::Wednesday->value => 'Woensdag',
			self::Thursday->value  => 'Donderdag',
			self::Friday->value    => 'Vrijdag',
			self::Saturday->value  => 'Zaterdag',
			self::Sunday->value    => 'Zondag',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Monday->value    => 'Poniedziałek',
			self::Tuesday->value   => 'Wtorek',
			self::Wednesday->value => 'Środa',
			self::Thursday->value  => 'Czwartek',
			self::Friday->value    => 'Piątek',
			self::Saturday->value  => 'Sobota',
			self::Sunday->value    => 'Niedziela',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Monday->value    => 'Понедельник',
			self::Tuesday->value   => 'Вторник',
			self::Wednesday->value => 'Среда',
			self::Thursday->value  => 'Четверг',
			self::Friday->value    => 'Пятница',
			self::Saturday->value  => 'Суббота',
			self::Sunday->value    => 'Воскресенье',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Monday->value    => 'Pazartesi',
			self::Tuesday->value   => 'Salı',
			self::Wednesday->value => 'Çarşamba',
			self::Thursday->value  => 'Perşembe',
			self::Friday->value    => 'Cuma',
			self::Saturday->value  => 'Cumartesi',
			self::Sunday->value    => 'Pazar',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Monday->value    => '星期一',
			self::Tuesday->value   => '星期二',
			self::Wednesday->value => '星期三',
			self::Thursday->value  => '星期四',
			self::Friday->value    => '星期五',
			self::Saturday->value  => '星期六',
			self::Sunday->value    => '星期日',
		];
	}
}
