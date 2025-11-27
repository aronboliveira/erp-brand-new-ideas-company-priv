<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum CountryName: string
{
	case Brazil        = 'Brazil';
	case UnitedStates  = 'United States';
	case Canada        = 'Canada';
	case UnitedKingdom = 'United Kingdom';
	case Germany       = 'Germany';
	case France        = 'France';
	case Spain         = 'Spain';
	case Portugal      = 'Portugal';
	case Italy         = 'Italy';
	case Argentina     = 'Argentina';
	case Chile         = 'Chile';
	case Mexico        = 'Mexico';
	case Japan         = 'Japan';
	case China         = 'China';
	case India         = 'India';
	case Australia     = 'Australia';
	case SouthAfrica   = 'South Africa';

	public static function normalize(?string $value): ?self
	{
		$v = strtolower(trim((string) $value));
		if ($v === '')
			return null;

		$map = [
			'br'                         => self::Brazil,
			'bra'                        => self::Brazil,
			'brazil'                     => self::Brazil,
			'brasil'                     => self::Brazil,

			'us'                         => self::UnitedStates,
			'usa'                        => self::UnitedStates,
			'united states'              => self::UnitedStates,
			'united states of america'   => self::UnitedStates,

			'uk'                         => self::UnitedKingdom,
			'gb'                         => self::UnitedKingdom,
			'great britain'              => self::UnitedKingdom,
			'united kingdom'             => self::UnitedKingdom,

			'ca'                         => self::Canada,
			'can'                        => self::Canada,
			'canada'                     => self::Canada,

			'de'                         => self::Germany,
			'deu'                        => self::Germany,
			'germany'                    => self::Germany,
			'alemanha'                   => self::Germany,
			'alemania'                   => self::Germany,

			'fr'                         => self::France,
			'fra'                        => self::France,
			'france'                     => self::France,
			'frança'                     => self::France,
			'francia'                    => self::France,

			'es'                         => self::Spain,
			'esp'                        => self::Spain,
			'spain'                      => self::Spain,
			'espanha'                    => self::Spain,
			'españa'                     => self::Spain,

			'pt'                         => self::Portugal,
			'prt'                        => self::Portugal,
			'portugal'                   => self::Portugal,

			'it'                         => self::Italy,
			'ita'                        => self::Italy,
			'italy'                      => self::Italy,
			'itália'                     => self::Italy,
			'italia'                     => self::Italy,

			'ar'                         => self::Argentina,
			'arg'                        => self::Argentina,
			'argentina'                  => self::Argentina,

			'cl'                         => self::Chile,
			'chl'                        => self::Chile,
			'chile'                      => self::Chile,

			'mx'                         => self::Mexico,
			'mex'                        => self::Mexico,
			'mexico'                     => self::Mexico,
			'méxico'                     => self::Mexico,

			'jp'                         => self::Japan,
			'jpn'                        => self::Japan,
			'japan'                      => self::Japan,
			'japão'                      => self::Japan,
			'japón'                      => self::Japan,

			'cn'                         => self::China,
			'chn'                        => self::China,
			'china'                      => self::China,
			'china'                      => self::China,

			'in'                         => self::India,
			'ind'                        => self::India,
			'india'                      => self::India,

			'au'                         => self::Australia,
			'aus'                        => self::Australia,
			'australia'                  => self::Australia,
			'austrália'                  => self::Australia,
			'australia'                  => self::Australia,

			'za'                         => self::SouthAfrica,
			'zaf'                        => self::SouthAfrica,
			'south africa'               => self::SouthAfrica,
			'áfrica do sul'              => self::SouthAfrica,
			'sudáfrica'                  => self::SouthAfrica,
		];

		if (isset($map[$v]))
			return $map[$v];

		foreach (self::cases() as $case)
			if (strtolower($case->value) === $v)
				return $case;

		return null;
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
			self::Brazil->value        => 'Brasil',
			self::UnitedStates->value  => 'Estados Unidos',
			self::Canada->value        => 'Canadá',
			self::UnitedKingdom->value => 'Reino Unido',
			self::Germany->value       => 'Alemanha',
			self::France->value        => 'França',
			self::Spain->value         => 'Espanha',
			self::Portugal->value      => 'Portugal',
			self::Italy->value         => 'Itália',
			self::Argentina->value     => 'Argentina',
			self::Chile->value         => 'Chile',
			self::Mexico->value        => 'México',
			self::Japan->value         => 'Japão',
			self::China->value         => 'China',
			self::India->value         => 'Índia',
			self::Australia->value     => 'Austrália',
			self::SouthAfrica->value   => 'África do Sul',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Brazil->value        => 'Brazil',
			self::UnitedStates->value  => 'United States',
			self::Canada->value        => 'Canada',
			self::UnitedKingdom->value => 'United Kingdom',
			self::Germany->value       => 'Germany',
			self::France->value        => 'France',
			self::Spain->value         => 'Spain',
			self::Portugal->value      => 'Portugal',
			self::Italy->value         => 'Italy',
			self::Argentina->value     => 'Argentina',
			self::Chile->value         => 'Chile',
			self::Mexico->value        => 'Mexico',
			self::Japan->value         => 'Japan',
			self::China->value         => 'China',
			self::India->value         => 'India',
			self::Australia->value     => 'Australia',
			self::SouthAfrica->value   => 'South Africa',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Brazil->value        => 'Brasil',
			self::UnitedStates->value  => 'Estados Unidos',
			self::Canada->value        => 'Canadá',
			self::UnitedKingdom->value => 'Reino Unido',
			self::Germany->value       => 'Alemania',
			self::France->value        => 'Francia',
			self::Spain->value         => 'España',
			self::Portugal->value      => 'Portugal',
			self::Italy->value         => 'Italia',
			self::Argentina->value     => 'Argentina',
			self::Chile->value         => 'Chile',
			self::Mexico->value        => 'México',
			self::Japan->value         => 'Japón',
			self::China->value         => 'China',
			self::India->value         => 'India',
			self::Australia->value     => 'Australia',
			self::SouthAfrica->value   => 'Sudáfrica',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Brazil->value        => 'البرازيل',
			self::UnitedStates->value  => 'الولايات المتحدة',
			self::Canada->value        => 'كندا',
			self::UnitedKingdom->value => 'المملكة المتحدة',
			self::Germany->value       => 'ألمانيا',
			self::France->value        => 'فرنسا',
			self::Spain->value         => 'إسبانيا',
			self::Portugal->value      => 'البرتغال',
			self::Italy->value         => 'إيطاليا',
			self::Argentina->value     => 'الأرجنتين',
			self::Chile->value         => 'تشيلي',
			self::Mexico->value        => 'المكسيك',
			self::Japan->value         => 'اليابان',
			self::China->value         => 'الصين',
			self::India->value         => 'الهند',
			self::Australia->value     => 'أستراليا',
			self::SouthAfrica->value   => 'جنوب أفريقيا',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Brazil->value        => 'Brasilien',
			self::UnitedStates->value  => 'USA',
			self::Canada->value        => 'Canada',
			self::UnitedKingdom->value => 'Storbritannien',
			self::Germany->value       => 'Tyskland',
			self::France->value        => 'Frankrig',
			self::Spain->value         => 'Spanien',
			self::Portugal->value      => 'Portugal',
			self::Italy->value         => 'Italien',
			self::Argentina->value     => 'Argentina',
			self::Chile->value         => 'Chile',
			self::Mexico->value        => 'Mexico',
			self::Japan->value         => 'Japan',
			self::China->value         => 'Kina',
			self::India->value         => 'Indien',
			self::Australia->value     => 'Australien',
			self::SouthAfrica->value   => 'Sydafrika',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Brazil->value        => 'Brasilien',
			self::UnitedStates->value  => 'Vereinigte Staaten',
			self::Canada->value        => 'Kanada',
			self::UnitedKingdom->value => 'Vereinigtes Königreich',
			self::Germany->value       => 'Deutschland',
			self::France->value        => 'Frankreich',
			self::Spain->value         => 'Spanien',
			self::Portugal->value      => 'Portugal',
			self::Italy->value         => 'Italien',
			self::Argentina->value     => 'Argentinien',
			self::Chile->value         => 'Chile',
			self::Mexico->value        => 'Mexiko',
			self::Japan->value         => 'Japan',
			self::China->value         => 'China',
			self::India->value         => 'Indien',
			self::Australia->value     => 'Australien',
			self::SouthAfrica->value   => 'Südafrika',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Brazil->value        => 'Brésil',
			self::UnitedStates->value  => 'États-Unis',
			self::Canada->value        => 'Canada',
			self::UnitedKingdom->value => 'Royaume-Uni',
			self::Germany->value       => 'Allemagne',
			self::France->value        => 'France',
			self::Spain->value         => 'Espagne',
			self::Portugal->value      => 'Portugal',
			self::Italy->value         => 'Italie',
			self::Argentina->value     => 'Argentine',
			self::Chile->value         => 'Chili',
			self::Mexico->value        => 'Mexique',
			self::Japan->value         => 'Japon',
			self::China->value         => 'Chine',
			self::India->value         => 'Inde',
			self::Australia->value     => 'Australie',
			self::SouthAfrica->value   => 'Afrique du Sud',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Brazil->value        => 'ברזיל',
			self::UnitedStates->value  => 'ארצות הברית',
			self::Canada->value        => 'קנדה',
			self::UnitedKingdom->value => 'הממלכה המאוחדת',
			self::Germany->value       => 'גרמניה',
			self::France->value        => 'צרפת',
			self::Spain->value         => 'ספרד',
			self::Portugal->value      => 'פורטוגל',
			self::Italy->value         => 'איטליה',
			self::Argentina->value     => 'ארגנטינה',
			self::Chile->value         => 'צ\'ילה',
			self::Mexico->value        => 'מקסיקו',
			self::Japan->value         => 'יפן',
			self::China->value         => 'סין',
			self::India->value         => 'הודו',
			self::Australia->value     => 'אוסטרליה',
			self::SouthAfrica->value   => 'דרום אפריקה',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Brazil->value        => 'Brasile',
			self::UnitedStates->value  => 'Stati Uniti',
			self::Canada->value        => 'Canada',
			self::UnitedKingdom->value => 'Regno Unito',
			self::Germany->value       => 'Germania',
			self::France->value        => 'Francia',
			self::Spain->value         => 'Spagna',
			self::Portugal->value      => 'Portogallo',
			self::Italy->value         => 'Italia',
			self::Argentina->value     => 'Argentina',
			self::Chile->value         => 'Cile',
			self::Mexico->value        => 'Messico',
			self::Japan->value         => 'Giappone',
			self::China->value         => 'Cina',
			self::India->value         => 'India',
			self::Australia->value     => 'Australia',
			self::SouthAfrica->value   => 'Sudafrica',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Brazil->value        => 'ブラジル',
			self::UnitedStates->value  => 'アメリカ',
			self::Canada->value        => 'カナダ',
			self::UnitedKingdom->value => 'イギリス',
			self::Germany->value       => 'ドイツ',
			self::France->value        => 'フランス',
			self::Spain->value         => 'スペイン',
			self::Portugal->value      => 'ポルトガル',
			self::Italy->value         => 'イタリア',
			self::Argentina->value     => 'アルゼンチン',
			self::Chile->value         => 'チリ',
			self::Mexico->value        => 'メキシコ',
			self::Japan->value         => '日本',
			self::China->value         => '中国',
			self::India->value         => 'インド',
			self::Australia->value     => 'オーストラリア',
			self::SouthAfrica->value   => '南アフリカ',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Brazil->value        => 'Brazilië',
			self::UnitedStates->value  => 'Verenigde Staten',
			self::Canada->value        => 'Canada',
			self::UnitedKingdom->value => 'Verenigd Koninkrijk',
			self::Germany->value       => 'Duitsland',
			self::France->value        => 'Frankrijk',
			self::Spain->value         => 'Spanje',
			self::Portugal->value      => 'Portugal',
			self::Italy->value         => 'Italië',
			self::Argentina->value     => 'Argentinië',
			self::Chile->value         => 'Chili',
			self::Mexico->value        => 'Mexico',
			self::Japan->value         => 'Japan',
			self::China->value         => 'China',
			self::India->value         => 'India',
			self::Australia->value     => 'Australië',
			self::SouthAfrica->value   => 'Zuid-Afrika',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Brazil->value        => 'Brazylia',
			self::UnitedStates->value  => 'Stany Zjednoczone',
			self::Canada->value        => 'Kanada',
			self::UnitedKingdom->value => 'Wielka Brytania',
			self::Germany->value       => 'Niemcy',
			self::France->value        => 'Francja',
			self::Spain->value         => 'Hiszpania',
			self::Portugal->value      => 'Portugalia',
			self::Italy->value         => 'Włochy',
			self::Argentina->value     => 'Argentyna',
			self::Chile->value         => 'Chile',
			self::Mexico->value        => 'Meksyk',
			self::Japan->value         => 'Japonia',
			self::China->value         => 'Chiny',
			self::India->value         => 'Indie',
			self::Australia->value     => 'Australia',
			self::SouthAfrica->value   => 'Południowa Afryka',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Brazil->value        => 'Бразилия',
			self::UnitedStates->value  => 'Соединенные Штаты',
			self::Canada->value        => 'Канада',
			self::UnitedKingdom->value => 'Великобритания',
			self::Germany->value       => 'Германия',
			self::France->value        => 'Франция',
			self::Spain->value         => 'Испания',
			self::Portugal->value      => 'Португалия',
			self::Italy->value         => 'Италия',
			self::Argentina->value     => 'Аргентина',
			self::Chile->value         => 'Чили',
			self::Mexico->value        => 'Мексика',
			self::Japan->value         => 'Япония',
			self::China->value         => 'Китай',
			self::India->value         => 'Индия',
			self::Australia->value     => 'Австралия',
			self::SouthAfrica->value   => 'Южная Африка',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Brazil->value        => 'Brezilya',
			self::UnitedStates->value  => 'Amerika Birleşik Devletleri',
			self::Canada->value        => 'Kanada',
			self::UnitedKingdom->value => 'Birleşik Krallık',
			self::Germany->value       => 'Almanya',
			self::France->value        => 'Fransa',
			self::Spain->value         => 'İspanya',
			self::Portugal->value      => 'Portekiz',
			self::Italy->value         => 'İtalya',
			self::Argentina->value     => 'Arjantin',
			self::Chile->value         => 'Şili',
			self::Mexico->value        => 'Meksika',
			self::Japan->value         => 'Japonya',
			self::China->value         => 'Çin',
			self::India->value         => 'Hindistan',
			self::Australia->value     => 'Avustralya',
			self::SouthAfrica->value   => 'Güney Afrika',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Brazil->value        => '巴西',
			self::UnitedStates->value  => '美国',
			self::Canada->value        => '加拿大',
			self::UnitedKingdom->value => '英国',
			self::Germany->value       => '德国',
			self::France->value        => '法国',
			self::Spain->value         => '西班牙',
			self::Portugal->value      => '葡萄牙',
			self::Italy->value         => '意大利',
			self::Argentina->value     => '阿根廷',
			self::Chile->value         => '智利',
			self::Mexico->value        => '墨西哥',
			self::Japan->value         => '日本',
			self::China->value         => '中国',
			self::India->value         => '印度',
			self::Australia->value     => '澳大利亚',
			self::SouthAfrica->value   => '南非',
		];
	}
}
