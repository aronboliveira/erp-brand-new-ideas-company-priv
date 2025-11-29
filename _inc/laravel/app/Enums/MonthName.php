<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum MonthName: string
{
	case January   = 'january';
	case February  = 'february';
	case March     = 'march';
	case April     = 'april';
	case May       = 'may';
	case June      = 'june';
	case July      = 'july';
	case August    = 'august';
	case September = 'september';
	case October   = 'october';
	case November  = 'november';
	case December  = 'december';

	public static function normalize(string|null|self $value): ?self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return null;
		$v = strtolower(trim((string) $value));

		if ($v === '')
			return self::January;

		$map = [
			'1'   => self::January,
			'01'  => self::January,
			'jan' => self::January,
			'january' => self::January,
			'janeiro' => self::January,

			'2'   => self::February,
			'02'  => self::February,
			'feb' => self::February,
			'fev' => self::February,
			'february' => self::February,
			'fevereiro' => self::February,

			'3'   => self::March,
			'03'  => self::March,
			'mar' => self::March,
			'march' => self::March,
			'março' => self::March,
			'marco' => self::March,

			'4'   => self::April,
			'04'  => self::April,
			'apr' => self::April,
			'april' => self::April,
			'abril' => self::April,

			'5'   => self::May,
			'05'  => self::May,
			'may' => self::May,
			'maio' => self::May,

			'6'   => self::June,
			'06'  => self::June,
			'jun' => self::June,
			'june' => self::June,
			'junho' => self::June,

			'7'   => self::July,
			'07'  => self::July,
			'jul' => self::July,
			'july' => self::July,
			'julho' => self::July,

			'8'   => self::August,
			'08'  => self::August,
			'aug' => self::August,
			'august' => self::August,
			'agosto' => self::August,

			'9'   => self::September,
			'09'  => self::September,
			'sep' => self::September,
			'sept' => self::September,
			'september' => self::September,
			'setembro' => self::September,

			'10'  => self::October,
			'oct' => self::October,
			'october' => self::October,
			'outubro' => self::October,

			'11'  => self::November,
			'nov' => self::November,
			'november' => self::November,
			'novembro' => self::November,

			'12'  => self::December,
			'dec' => self::December,
			'dez' => self::December,
			'december' => self::December,
			'dezembro' => self::December,
		];

		return $map[$v] ?? self::January;
	}

	public static function ordered(): array
	{
		return [
			self::January,
			self::February,
			self::March,
			self::April,
			self::May,
			self::June,
			self::July,
			self::August,
			self::September,
			self::October,
			self::November,
			self::December,
		];
	}

	public function isoIndex(): int
	{
		return match ($this) {
			self::January   => 1,
			self::February  => 2,
			self::March     => 3,
			self::April     => 4,
			self::May       => 5,
			self::June      => 6,
			self::July      => 7,
			self::August    => 8,
			self::September => 9,
			self::October   => 10,
			self::November  => 11,
			self::December  => 12,
		};
	}

	public function label(): string
	{
		return match ($this) {
			self::January   => 'January',
			self::February  => 'February',
			self::March     => 'March',
			self::April     => 'April',
			self::May       => 'May',
			self::June      => 'June',
			self::July      => 'July',
			self::August    => 'August',
			self::September => 'September',
			self::October   => 'October',
			self::November  => 'November',
			self::December  => 'December',
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
			self::January->value   => 'Janeiro',
			self::February->value  => 'Fevereiro',
			self::March->value     => 'Março',
			self::April->value     => 'Abril',
			self::May->value       => 'Maio',
			self::June->value      => 'Junho',
			self::July->value      => 'Julho',
			self::August->value    => 'Agosto',
			self::September->value => 'Setembro',
			self::October->value   => 'Outubro',
			self::November->value  => 'Novembro',
			self::December->value  => 'Dezembro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::January->value   => 'January',
			self::February->value  => 'February',
			self::March->value     => 'March',
			self::April->value     => 'April',
			self::May->value       => 'May',
			self::June->value      => 'June',
			self::July->value      => 'July',
			self::August->value    => 'August',
			self::September->value => 'September',
			self::October->value   => 'October',
			self::November->value  => 'November',
			self::December->value  => 'December',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::January->value   => 'Enero',
			self::February->value  => 'Febrero',
			self::March->value     => 'Marzo',
			self::April->value     => 'Abril',
			self::May->value       => 'Mayo',
			self::June->value      => 'Junio',
			self::July->value      => 'Julio',
			self::August->value    => 'Agosto',
			self::September->value => 'Septiembre',
			self::October->value   => 'Octubre',
			self::November->value  => 'Noviembre',
			self::December->value  => 'Diciembre',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::January->value   => 'يناير',
			self::February->value  => 'فبراير',
			self::March->value     => 'مارس',
			self::April->value     => 'أبريل',
			self::May->value       => 'مايو',
			self::June->value      => 'يونيو',
			self::July->value      => 'يوليو',
			self::August->value    => 'أغسطس',
			self::September->value => 'سبتمبر',
			self::October->value   => 'أكتوبر',
			self::November->value  => 'نوفمبر',
			self::December->value  => 'ديسمبر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::January->value   => 'Januar',
			self::February->value  => 'Februar',
			self::March->value     => 'Marts',
			self::April->value     => 'April',
			self::May->value       => 'Maj',
			self::June->value      => 'Juni',
			self::July->value      => 'Juli',
			self::August->value    => 'August',
			self::September->value => 'September',
			self::October->value   => 'Oktober',
			self::November->value  => 'November',
			self::December->value  => 'December',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::January->value   => 'Januar',
			self::February->value  => 'Februar',
			self::March->value     => 'März',
			self::April->value     => 'April',
			self::May->value       => 'Mai',
			self::June->value      => 'Juni',
			self::July->value      => 'Juli',
			self::August->value    => 'August',
			self::September->value => 'September',
			self::October->value   => 'Oktober',
			self::November->value  => 'November',
			self::December->value  => 'Dezember',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::January->value   => 'Janvier',
			self::February->value  => 'Février',
			self::March->value     => 'Mars',
			self::April->value     => 'Avril',
			self::May->value       => 'Mai',
			self::June->value      => 'Juin',
			self::July->value      => 'Juillet',
			self::August->value    => 'Août',
			self::September->value => 'Septembre',
			self::October->value   => 'Octobre',
			self::November->value  => 'Novembre',
			self::December->value  => 'Décembre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::January->value   => 'ינואר',
			self::February->value  => 'פברואר',
			self::March->value     => 'מרץ',
			self::April->value     => 'אפריל',
			self::May->value       => 'מאי',
			self::June->value      => 'יוני',
			self::July->value      => 'יולי',
			self::August->value    => 'אוגוסט',
			self::September->value => 'ספטמבר',
			self::October->value   => 'אוקטובר',
			self::November->value  => 'נובמבר',
			self::December->value  => 'דצמבר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::January->value   => 'Gennaio',
			self::February->value  => 'Febbraio',
			self::March->value     => 'Marzo',
			self::April->value     => 'Aprile',
			self::May->value       => 'Maggio',
			self::June->value      => 'Giugno',
			self::July->value      => 'Luglio',
			self::August->value    => 'Agosto',
			self::September->value => 'Settembre',
			self::October->value   => 'Ottobre',
			self::November->value  => 'Novembre',
			self::December->value  => 'Dicembre',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::January->value   => '1月',
			self::February->value  => '2月',
			self::March->value     => '3月',
			self::April->value     => '4月',
			self::May->value       => '5月',
			self::June->value      => '6月',
			self::July->value      => '7月',
			self::August->value    => '8月',
			self::September->value => '9月',
			self::October->value   => '10月',
			self::November->value  => '11月',
			self::December->value  => '12月',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::January->value   => 'Januari',
			self::February->value  => 'Februari',
			self::March->value     => 'Maart',
			self::April->value     => 'April',
			self::May->value       => 'Mei',
			self::June->value      => 'Juni',
			self::July->value      => 'Juli',
			self::August->value    => 'Augustus',
			self::September->value => 'September',
			self::October->value   => 'Oktober',
			self::November->value  => 'November',
			self::December->value  => 'December',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::January->value   => 'Styczeń',
			self::February->value  => 'Luty',
			self::March->value     => 'Marzec',
			self::April->value     => 'Kwiecień',
			self::May->value       => 'Maj',
			self::June->value      => 'Czerwiec',
			self::July->value      => 'Lipiec',
			self::August->value    => 'Sierpień',
			self::September->value => 'Wrzesień',
			self::October->value   => 'Październik',
			self::November->value  => 'Listopad',
			self::December->value  => 'Grudzień',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::January->value   => 'Январь',
			self::February->value  => 'Февраль',
			self::March->value     => 'Март',
			self::April->value     => 'Апрель',
			self::May->value       => 'Май',
			self::June->value      => 'Июнь',
			self::July->value      => 'Июль',
			self::August->value    => 'Август',
			self::September->value => 'Сентябрь',
			self::October->value   => 'Октябрь',
			self::November->value  => 'Ноябрь',
			self::December->value  => 'Декабрь',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::January->value   => 'Ocak',
			self::February->value  => 'Şubat',
			self::March->value     => 'Mart',
			self::April->value     => 'Nisan',
			self::May->value       => 'Mayıs',
			self::June->value      => 'Haziran',
			self::July->value      => 'Temmuz',
			self::August->value    => 'Ağustos',
			self::September->value => 'Eylül',
			self::October->value   => 'Ekim',
			self::November->value  => 'Kasım',
			self::December->value  => 'Aralık',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::January->value   => '一月',
			self::February->value  => '二月',
			self::March->value     => '三月',
			self::April->value     => '四月',
			self::May->value       => '五月',
			self::June->value      => '六月',
			self::July->value      => '七月',
			self::August->value    => '八月',
			self::September->value => '九月',
			self::October->value   => '十月',
			self::November->value  => '十一月',
			self::December->value  => '十二月',
		];
	}

	public static function values($slice = 11): array
	{
		return array_slice([
			MonthName::January->value,
			MonthName::February->value,
			MonthName::March->value,
			MonthName::April->value,
			MonthName::May->value,
			MonthName::June->value,
			MonthName::July->value,
			MonthName::August->value,
			MonthName::September->value,
			MonthName::October->value,
			MonthName::November->value,
			MonthName::December->value,
		], 0, $slice);
	}
}
