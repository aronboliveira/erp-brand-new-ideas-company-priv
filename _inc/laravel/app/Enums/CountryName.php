<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;
use Illuminate\Support\Facades\Log;

enum CountryName: string
{
	case Brazil        = 'Brazil';
	case UnitedStates  = 'United States';
		// todo add more countries state enums as needed
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
	case Denmark       = 'Denmark';
	case Netherlands   = 'Netherlands';
	case Poland        = 'Poland';
	case SaudiArabia   = 'Saudi Arabia';
	case Turkey        = 'Turkey';
	case Israel        = 'Israel';
	case Russia        = 'Russia';
	case Switzerland   = 'Switzerland';
	case Belgium       = 'Belgium';
	case Austria       = 'Austria';
	case Taiwan        = 'Taiwan';
	case Colombia      = 'Colombia';
	case Peru          = 'Peru';
	case Venezuela     = 'Venezuela';
	case Norway        = 'Norway';
	case Sweden        = 'Sweden';
	case Finland       = 'Finland';
	case Greece        = 'Greece';
	case CzechRepublic = 'Czech Republic';
	case Hungary       = 'Hungary';
	case Romania       = 'Romania';
		// South American countries
	case Bolivia       = 'Bolivia';
	case Ecuador       = 'Ecuador';
	case Guyana        = 'Guyana';
	case Paraguay      = 'Paraguay';
	case Suriname      = 'Suriname';
	case Uruguay       = 'Uruguay';
	case FrenchGuiana  = 'French Guiana';

	public static function normalize(string|null|self $value): ?self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return null;
		$v = strtolower(trim(str_replace(
			[':', '—', '–', '_', '|', '/', '\\', ',', ';', '"'],
			' ',
			$value
		)));
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

			'in'                         => self::India,
			'ind'                        => self::India,
			'india'                      => self::India,

			'au'                         => self::Australia,
			'aus'                        => self::Australia,
			'australia'                  => self::Australia,
			'austrália'                  => self::Australia,

			'za'                         => self::SouthAfrica,
			'zaf'                        => self::SouthAfrica,
			'south africa'               => self::SouthAfrica,
			'áfrica do sul'              => self::SouthAfrica,
			'sudáfrica'                  => self::SouthAfrica,

			'dk'                         => self::Denmark,
			'dnk'                        => self::Denmark,
			'denmark'                    => self::Denmark,
			'dinamarca'                  => self::Denmark,

			'nl'                         => self::Netherlands,
			'nld'                        => self::Netherlands,
			'netherlands'                => self::Netherlands,
			'holanda'                    => self::Netherlands,
			'países bajos'               => self::Netherlands,

			'pl'                         => self::Poland,
			'pol'                        => self::Poland,
			'poland'                     => self::Poland,
			'polônia'                    => self::Poland,
			'polonia'                    => self::Poland,

			'sa'                         => self::SaudiArabia,
			'sau'                        => self::SaudiArabia,
			'saudi arabia'               => self::SaudiArabia,
			'arabia saudita'             => self::SaudiArabia,
			'arabia saudí'               => self::SaudiArabia,

			'tr'                         => self::Turkey,
			'tur'                        => self::Turkey,
			'turkey'                     => self::Turkey,
			'turquia'                    => self::Turkey,

			'il'                         => self::Israel,
			'isr'                        => self::Israel,
			'israel'                     => self::Israel,

			'ru'                         => self::Russia,
			'rus'                        => self::Russia,
			'russia'                     => self::Russia,
			'rússia'                     => self::Russia,
			'rusia'                      => self::Russia,

			'ch'                         => self::Switzerland,
			'che'                        => self::Switzerland,
			'switzerland'                => self::Switzerland,
			'suíça'                      => self::Switzerland,
			'suiza'                      => self::Switzerland,

			'be'                         => self::Belgium,
			'bel'                        => self::Belgium,
			'belgium'                    => self::Belgium,
			'bélgica'                    => self::Belgium,

			'at'                         => self::Austria,
			'aut'                        => self::Austria,
			'austria'                    => self::Austria,
			'áustria'                    => self::Austria,

			'tw'                         => self::Taiwan,
			'twn'                        => self::Taiwan,
			'taiwan'                     => self::Taiwan,

			'co'                         => self::Colombia,
			'col'                        => self::Colombia,
			'colombia'                   => self::Colombia,

			'pe'                         => self::Peru,
			'per'                        => self::Peru,
			'peru'                       => self::Peru,
			'perú'                       => self::Peru,

			've'                         => self::Venezuela,
			'ven'                        => self::Venezuela,
			'venezuela'                  => self::Venezuela,

			'no'                         => self::Norway,
			'nor'                        => self::Norway,
			'norway'                     => self::Norway,
			'noruega'                    => self::Norway,

			'se'                         => self::Sweden,
			'swe'                        => self::Sweden,
			'sweden'                     => self::Sweden,
			'suécia'                     => self::Sweden,
			'suecia'                     => self::Sweden,

			'fi'                         => self::Finland,
			'fin'                        => self::Finland,
			'finland'                    => self::Finland,
			'finlândia'                  => self::Finland,
			'finlandia'                  => self::Finland,

			'gr'                         => self::Greece,
			'grc'                        => self::Greece,
			'greece'                     => self::Greece,
			'grécia'                     => self::Greece,
			'grecia'                     => self::Greece,

			'cz'                         => self::CzechRepublic,
			'cze'                        => self::CzechRepublic,
			'czech republic'             => self::CzechRepublic,
			'república checa'            => self::CzechRepublic,

			'hu'                         => self::Hungary,
			'hun'                        => self::Hungary,
			'hungary'                    => self::Hungary,
			'hungria'                    => self::Hungary,

			'ro'                         => self::Romania,
			'rou'                        => self::Romania,
			'romania'                    => self::Romania,
			'romênia'                    => self::Romania,
			'rumania'                    => self::Romania,

			// South American countries
			'bo'                         => self::Bolivia,
			'bol'                        => self::Bolivia,
			'bolivia'                    => self::Bolivia,
			'bolívia'                    => self::Bolivia,

			'ec'                         => self::Ecuador,
			'ecu'                        => self::Ecuador,
			'ecuador'                    => self::Ecuador,
			'equador'                    => self::Ecuador,

			'gy'                         => self::Guyana,
			'guy'                        => self::Guyana,
			'guyana'                     => self::Guyana,
			'guiana'                     => self::Guyana,
			'guayana'                    => self::Guyana,

			'py'                         => self::Paraguay,
			'pry'                        => self::Paraguay,
			'paraguay'                   => self::Paraguay,
			'paraguai'                   => self::Paraguay,

			'sr'                         => self::Suriname,
			'sur'                        => self::Suriname,
			'suriname'                   => self::Suriname,
			'surinam'                    => self::Suriname,

			'uy'                         => self::Uruguay,
			'ury'                        => self::Uruguay,
			'uruguay'                    => self::Uruguay,
			'uruguai'                    => self::Uruguay,

			'gf'                         => self::FrenchGuiana,
			'guf'                        => self::FrenchGuiana,
			'french guiana'              => self::FrenchGuiana,
			'guiana francesa'            => self::FrenchGuiana,
			'guyane française'           => self::FrenchGuiana,
			'guayana francesa'           => self::FrenchGuiana,
		];
		if (isset($map[$v]))
			return $map[$v];
		foreach (self::cases() as $case)
			if (strtolower($case->value) === $v)
				return $case;
		return null;
	}

	public static function IsIsoCoded(?string $isoCode): bool
	{
		if ($isoCode === null)
			return false;

		$iso = strtoupper(trim($isoCode));
		$validIsoCodes = [
			'BR',
			'US',
			'CA',
			'GB',
			'DE',
			'FR',
			'ES',
			'PT',
			'IT',
			'AR',
			'CL',
			'MX',
			'JP',
			'CN',
			'IN',
			'AU',
			'ZA',
			'DK',
			'NL',
			'PL',
			'SA',
			'TR',
			'IL',
			'RU',
			'CH',
			'BE',
			'AT',
			'TW',
			'CO',
			'PE',
			'VE',
			'NO',
			'SE',
			'FI',
			'GR',
			'CZ',
			'HU',
			'RO',
			'BO',
			'EC',
			'GY',
			'PY',
			'SR',
			'UY',
			'GF',
		];

		return in_array($iso, $validIsoCodes, true);
	}

	public static function getIsoCode(?string $countryName): ?string
	{
		$country = self::normalize($countryName);
		if ($country === null)
			return null;
		if ($countryName !== $country->value) Log::debug('Normalized country name to enum case', ['input' => $countryName, 'enum_case' => $country->value]);
		return match ($country) {
			self::Brazil        => 'BR',
			self::UnitedStates  => 'US',
			self::Canada        => 'CA',
			self::UnitedKingdom => 'GB',
			self::Germany       => 'DE',
			self::France        => 'FR',
			self::Spain         => 'ES',
			self::Portugal      => 'PT',
			self::Italy         => 'IT',
			self::Argentina     => 'AR',
			self::Chile         => 'CL',
			self::Mexico        => 'MX',
			self::Japan         => 'JP',
			self::China         => 'CN',
			self::India         => 'IN',
			self::Australia     => 'AU',
			self::SouthAfrica   => 'ZA',
			self::Denmark       => 'DK',
			self::Netherlands   => 'NL',
			self::Poland        => 'PL',
			self::SaudiArabia   => 'SA',
			self::Turkey        => 'TR',
			self::Israel        => 'IL',
			self::Russia        => 'RU',
			self::Switzerland   => 'CH',
			self::Belgium       => 'BE',
			self::Austria       => 'AT',
			self::Taiwan        => 'TW',
			self::Colombia      => 'CO',
			self::Peru          => 'PE',
			self::Venezuela     => 'VE',
			self::Norway        => 'NO',
			self::Sweden        => 'SE',
			self::Finland       => 'FI',
			self::Greece        => 'GR',
			self::CzechRepublic => 'CZ',
			self::Hungary       => 'HU',
			self::Romania       => 'RO',
			self::Bolivia       => 'BO',
			self::Ecuador       => 'EC',
			self::Guyana        => 'GY',
			self::Paraguay      => 'PY',
			self::Suriname      => 'SR',
			self::Uruguay       => 'UY',
			self::FrenchGuiana  => 'GF',
			default => 'BR'
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
			// Original countries
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
			// New countries
			self::Denmark->value       => 'Dinamarca',
			self::Netherlands->value   => 'Holanda',
			self::Poland->value        => 'Polônia',
			self::SaudiArabia->value   => 'Arábia Saudita',
			self::Turkey->value        => 'Turquia',
			self::Israel->value        => 'Israel',
			self::Russia->value        => 'Rússia',
			self::Switzerland->value   => 'Suíça',
			self::Belgium->value       => 'Bélgica',
			self::Austria->value       => 'Áustria',
			self::Taiwan->value        => 'Taiwan',
			self::Colombia->value      => 'Colômbia',
			self::Peru->value          => 'Peru',
			self::Venezuela->value     => 'Venezuela',
			self::Norway->value        => 'Noruega',
			self::Sweden->value        => 'Suécia',
			self::Finland->value       => 'Finlândia',
			self::Greece->value        => 'Grécia',
			self::CzechRepublic->value => 'República Tcheca',
			self::Hungary->value       => 'Hungria',
			self::Romania->value       => 'Romênia',
			// South American countries
			self::Bolivia->value       => 'Bolívia',
			self::Ecuador->value       => 'Equador',
			self::Guyana->value        => 'Guiana',
			self::Paraguay->value      => 'Paraguai',
			self::Suriname->value      => 'Suriname',
			self::Uruguay->value       => 'Uruguai',
			self::FrenchGuiana->value  => 'Guiana Francesa',
		];
	}

	public static function labelsEn(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'Denmark',
			self::Netherlands->value   => 'Netherlands',
			self::Poland->value        => 'Poland',
			self::SaudiArabia->value   => 'Saudi Arabia',
			self::Turkey->value        => 'Turkey',
			self::Israel->value        => 'Israel',
			self::Russia->value        => 'Russia',
			self::Switzerland->value   => 'Switzerland',
			self::Belgium->value       => 'Belgium',
			self::Austria->value       => 'Austria',
			self::Taiwan->value        => 'Taiwan',
			self::Colombia->value      => 'Colombia',
			self::Peru->value          => 'Peru',
			self::Venezuela->value     => 'Venezuela',
			self::Norway->value        => 'Norway',
			self::Sweden->value        => 'Sweden',
			self::Finland->value       => 'Finland',
			self::Greece->value        => 'Greece',
			self::CzechRepublic->value => 'Czech Republic',
			self::Hungary->value       => 'Hungary',
			self::Romania->value       => 'Romania',
			// South American countries
			self::Bolivia->value       => 'Bolivia',
			self::Ecuador->value       => 'Ecuador',
			self::Guyana->value        => 'Guyana',
			self::Paraguay->value      => 'Paraguay',
			self::Suriname->value      => 'Suriname',
			self::Uruguay->value       => 'Uruguay',
			self::FrenchGuiana->value  => 'French Guiana',
		];
	}

	public static function labelsEs(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'Dinamarca',
			self::Netherlands->value   => 'Países Bajos',
			self::Poland->value        => 'Polonia',
			self::SaudiArabia->value   => 'Arabia Saudí',
			self::Turkey->value        => 'Turquía',
			self::Israel->value        => 'Israel',
			self::Russia->value        => 'Rusia',
			self::Switzerland->value   => 'Suiza',
			self::Belgium->value       => 'Bélgica',
			self::Austria->value       => 'Austria',
			self::Taiwan->value        => 'Taiwán',
			self::Colombia->value      => 'Colombia',
			self::Peru->value          => 'Perú',
			self::Venezuela->value     => 'Venezuela',
			self::Norway->value        => 'Noruega',
			self::Sweden->value        => 'Suecia',
			self::Finland->value       => 'Finlandia',
			self::Greece->value        => 'Grecia',
			self::CzechRepublic->value => 'República Checa',
			self::Hungary->value       => 'Hungría',
			self::Romania->value       => 'Rumania',
			// South American countries
			self::Bolivia->value       => 'Bolivia',
			self::Ecuador->value       => 'Ecuador',
			self::Guyana->value        => 'Guyana',
			self::Paraguay->value      => 'Paraguay',
			self::Suriname->value      => 'Surinam',
			self::Uruguay->value       => 'Uruguay',
			self::FrenchGuiana->value  => 'Guayana Francesa',
		];
	}

	public static function labelsAr(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'الدنمارك',
			self::Netherlands->value   => 'هولندا',
			self::Poland->value        => 'بولندا',
			self::SaudiArabia->value   => 'السعودية',
			self::Turkey->value        => 'تركيا',
			self::Israel->value        => 'إسرائيل',
			self::Russia->value        => 'روسيا',
			self::Switzerland->value   => 'سويسرا',
			self::Belgium->value       => 'بلجيكا',
			self::Austria->value       => 'النمسا',
			self::Taiwan->value        => 'تايوان',
			self::Colombia->value      => 'كولومبيا',
			self::Peru->value          => 'بيرو',
			self::Venezuela->value     => 'فنزويلا',
			self::Norway->value        => 'النرويج',
			self::Sweden->value        => 'السويد',
			self::Finland->value       => 'فنلندا',
			self::Greece->value        => 'اليونان',
			self::CzechRepublic->value => 'التشيك',
			self::Hungary->value       => 'المجر',
			self::Romania->value       => 'رومانيا',
			// South American countries
			self::Bolivia->value       => 'بوليفيا',
			self::Ecuador->value       => 'الإكوادور',
			self::Guyana->value        => 'غيانا',
			self::Paraguay->value      => 'باراغواي',
			self::Suriname->value      => 'سورينام',
			self::Uruguay->value       => 'أوروغواي',
			self::FrenchGuiana->value  => 'غويانا الفرنسية',
		];
	}

	public static function labelsDa(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'Danmark',
			self::Netherlands->value   => 'Holland',
			self::Poland->value        => 'Polen',
			self::SaudiArabia->value   => 'Saudi-Arabien',
			self::Turkey->value        => 'Tyrkiet',
			self::Israel->value        => 'Israel',
			self::Russia->value        => 'Rusland',
			self::Switzerland->value   => 'Schweiz',
			self::Belgium->value       => 'Belgien',
			self::Austria->value       => 'Østrig',
			self::Taiwan->value        => 'Taiwan',
			self::Colombia->value      => 'Colombia',
			self::Peru->value          => 'Peru',
			self::Venezuela->value     => 'Venezuela',
			self::Norway->value        => 'Norge',
			self::Sweden->value        => 'Sverige',
			self::Finland->value       => 'Finland',
			self::Greece->value        => 'Grækenland',
			self::CzechRepublic->value => 'Tjekkiet',
			self::Hungary->value       => 'Ungarn',
			self::Romania->value       => 'Rumænien',
			// South American countries
			self::Bolivia->value       => 'Bolivia',
			self::Ecuador->value       => 'Ecuador',
			self::Guyana->value        => 'Guyana',
			self::Paraguay->value      => 'Paraguay',
			self::Suriname->value      => 'Surinam',
			self::Uruguay->value       => 'Uruguay',
			self::FrenchGuiana->value  => 'Fransk Guiana',
		];
	}

	public static function labelsDe(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'Dänemark',
			self::Netherlands->value   => 'Niederlande',
			self::Poland->value        => 'Polen',
			self::SaudiArabia->value   => 'Saudi-Arabien',
			self::Turkey->value        => 'Türkei',
			self::Israel->value        => 'Israel',
			self::Russia->value        => 'Russland',
			self::Switzerland->value   => 'Schweiz',
			self::Belgium->value       => 'Belgien',
			self::Austria->value       => 'Österreich',
			self::Taiwan->value        => 'Taiwan',
			self::Colombia->value      => 'Kolumbien',
			self::Peru->value          => 'Peru',
			self::Venezuela->value     => 'Venezuela',
			self::Norway->value        => 'Norwegen',
			self::Sweden->value        => 'Schweden',
			self::Finland->value       => 'Finnland',
			self::Greece->value        => 'Griechenland',
			self::CzechRepublic->value => 'Tschechien',
			self::Hungary->value       => 'Ungarn',
			self::Romania->value       => 'Rumänien',
			// South American countries
			self::Bolivia->value       => 'Bolivien',
			self::Ecuador->value       => 'Ecuador',
			self::Guyana->value        => 'Guyana',
			self::Paraguay->value      => 'Paraguay',
			self::Suriname->value      => 'Suriname',
			self::Uruguay->value       => 'Uruguay',
			self::FrenchGuiana->value  => 'Französisch-Guayana',
		];
	}

	public static function labelsFr(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'Danemark',
			self::Netherlands->value   => 'Pays-Bas',
			self::Poland->value        => 'Pologne',
			self::SaudiArabia->value   => 'Arabie saoudite',
			self::Turkey->value        => 'Turquie',
			self::Israel->value        => 'Israël',
			self::Russia->value        => 'Russie',
			self::Switzerland->value   => 'Suisse',
			self::Belgium->value       => 'Belgique',
			self::Austria->value       => 'Autriche',
			self::Taiwan->value        => 'Taïwan',
			self::Colombia->value      => 'Colombie',
			self::Peru->value          => 'Pérou',
			self::Venezuela->value     => 'Venezuela',
			self::Norway->value        => 'Norvège',
			self::Sweden->value        => 'Suède',
			self::Finland->value       => 'Finlande',
			self::Greece->value        => 'Grèce',
			self::CzechRepublic->value => 'République tchèque',
			self::Hungary->value       => 'Hongrie',
			self::Romania->value       => 'Roumanie',
			// South American countries
			self::Bolivia->value       => 'Bolivie',
			self::Ecuador->value       => 'Équateur',
			self::Guyana->value        => 'Guyana',
			self::Paraguay->value      => 'Paraguay',
			self::Suriname->value      => 'Suriname',
			self::Uruguay->value       => 'Uruguay',
			self::FrenchGuiana->value  => 'Guyane française',
		];
	}

	public static function labelsHe(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'דנמרק',
			self::Netherlands->value   => 'הולנד',
			self::Poland->value        => 'פולין',
			self::SaudiArabia->value   => 'ערב הסעודית',
			self::Turkey->value        => 'טורקיה',
			self::Israel->value        => 'ישראל',
			self::Russia->value        => 'רוסיה',
			self::Switzerland->value   => 'שווייץ',
			self::Belgium->value       => 'בלגיה',
			self::Austria->value       => 'אוסטריה',
			self::Taiwan->value        => 'טייוואן',
			self::Colombia->value      => 'קולומביה',
			self::Peru->value          => 'פרו',
			self::Venezuela->value     => 'ונצואלה',
			self::Norway->value        => 'נורווגיה',
			self::Sweden->value        => 'שוודיה',
			self::Finland->value       => 'פינלנד',
			self::Greece->value        => 'יוון',
			self::CzechRepublic->value => 'צ\'כיה',
			self::Hungary->value       => 'הונגריה',
			self::Romania->value       => 'רומניה',
			// South American countries
			self::Bolivia->value       => 'בוליביה',
			self::Ecuador->value       => 'אקוודור',
			self::Guyana->value        => 'גיאנה',
			self::Paraguay->value      => 'פרגוואי',
			self::Suriname->value      => 'סורינאם',
			self::Uruguay->value       => 'אורוגוואי',
			self::FrenchGuiana->value  => 'גיאנה הצרפתית',
		];
	}

	public static function labelsIt(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'Danimarca',
			self::Netherlands->value   => 'Paesi Bassi',
			self::Poland->value        => 'Polonia',
			self::SaudiArabia->value   => 'Arabia Saudita',
			self::Turkey->value        => 'Turchia',
			self::Israel->value        => 'Israele',
			self::Russia->value        => 'Russia',
			self::Switzerland->value   => 'Svizzera',
			self::Belgium->value       => 'Belgio',
			self::Austria->value       => 'Austria',
			self::Taiwan->value        => 'Taiwan',
			self::Colombia->value      => 'Colombia',
			self::Peru->value          => 'Perù',
			self::Venezuela->value     => 'Venezuela',
			self::Norway->value        => 'Norvegia',
			self::Sweden->value        => 'Svezia',
			self::Finland->value       => 'Finlandia',
			self::Greece->value        => 'Grecia',
			self::CzechRepublic->value => 'Repubblica Ceca',
			self::Hungary->value       => 'Ungheria',
			self::Romania->value       => 'Romania',
			// South American countries
			self::Bolivia->value       => 'Bolivia',
			self::Ecuador->value       => 'Ecuador',
			self::Guyana->value        => 'Guyana',
			self::Paraguay->value      => 'Paraguay',
			self::Suriname->value      => 'Suriname',
			self::Uruguay->value       => 'Uruguay',
			self::FrenchGuiana->value  => 'Guyana Francese',
		];
	}

	public static function labelsJa(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'デンマーク',
			self::Netherlands->value   => 'オランダ',
			self::Poland->value        => 'ポーランド',
			self::SaudiArabia->value   => 'サウジアラビア',
			self::Turkey->value        => 'トルコ',
			self::Israel->value        => 'イスラエル',
			self::Russia->value        => 'ロシア',
			self::Switzerland->value   => 'スイス',
			self::Belgium->value       => 'ベルギー',
			self::Austria->value       => 'オーストリア',
			self::Taiwan->value        => '台湾',
			self::Colombia->value      => 'コロンビア',
			self::Peru->value          => 'ペルー',
			self::Venezuela->value     => 'ベネズエラ',
			self::Norway->value        => 'ノルウェー',
			self::Sweden->value        => 'スウェーデン',
			self::Finland->value       => 'フィンランド',
			self::Greece->value        => 'ギリシャ',
			self::CzechRepublic->value => 'チェコ',
			self::Hungary->value       => 'ハンガリー',
			self::Romania->value       => 'ルーマニア',
			// South American countries
			self::Bolivia->value       => 'ボリビア',
			self::Ecuador->value       => 'エクアドル',
			self::Guyana->value        => 'ガイアナ',
			self::Paraguay->value      => 'パラグアイ',
			self::Suriname->value      => 'スリナム',
			self::Uruguay->value       => 'ウルグアイ',
			self::FrenchGuiana->value  => 'フランス領ギアナ',
		];
	}

	public static function labelsNl(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'Denemarken',
			self::Netherlands->value   => 'Nederland',
			self::Poland->value        => 'Polen',
			self::SaudiArabia->value   => 'Saoedi-Arabië',
			self::Turkey->value        => 'Turkije',
			self::Israel->value        => 'Israël',
			self::Russia->value        => 'Rusland',
			self::Switzerland->value   => 'Zwitserland',
			self::Belgium->value       => 'België',
			self::Austria->value       => 'Oostenrijk',
			self::Taiwan->value        => 'Taiwan',
			self::Colombia->value      => 'Colombia',
			self::Peru->value          => 'Peru',
			self::Venezuela->value     => 'Venezuela',
			self::Norway->value        => 'Noorwegen',
			self::Sweden->value        => 'Zweden',
			self::Finland->value       => 'Finland',
			self::Greece->value        => 'Griekenland',
			self::CzechRepublic->value => 'Tsjechië',
			self::Hungary->value       => 'Hongarije',
			self::Romania->value       => 'Roemenië',
			// South American countries
			self::Bolivia->value       => 'Bolivia',
			self::Ecuador->value       => 'Ecuador',
			self::Guyana->value        => 'Guyana',
			self::Paraguay->value      => 'Paraguay',
			self::Suriname->value      => 'Suriname',
			self::Uruguay->value       => 'Uruguay',
			self::FrenchGuiana->value  => 'Frans-Guyana',
		];
	}

	public static function labelsPl(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'Dania',
			self::Netherlands->value   => 'Holandia',
			self::Poland->value        => 'Polska',
			self::SaudiArabia->value   => 'Arabia Saudyjska',
			self::Turkey->value        => 'Turcja',
			self::Israel->value        => 'Izrael',
			self::Russia->value        => 'Rosja',
			self::Switzerland->value   => 'Szwajcaria',
			self::Belgium->value       => 'Belgia',
			self::Austria->value       => 'Austria',
			self::Taiwan->value        => 'Tajwan',
			self::Colombia->value      => 'Kolumbia',
			self::Peru->value          => 'Peru',
			self::Venezuela->value     => 'Wenezuela',
			self::Norway->value        => 'Norwegia',
			self::Sweden->value        => 'Szwecja',
			self::Finland->value       => 'Finlandia',
			self::Greece->value        => 'Grecja',
			self::CzechRepublic->value => 'Czechy',
			self::Hungary->value       => 'Węgry',
			self::Romania->value       => 'Rumunia',
			// South American countries
			self::Bolivia->value       => 'Boliwia',
			self::Ecuador->value       => 'Ekwador',
			self::Guyana->value        => 'Gujana',
			self::Paraguay->value      => 'Paragwaj',
			self::Suriname->value      => 'Surinam',
			self::Uruguay->value       => 'Urugwaj',
			self::FrenchGuiana->value  => 'Gujana Francuska',
		];
	}

	public static function labelsRu(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'Дания',
			self::Netherlands->value   => 'Нидерланды',
			self::Poland->value        => 'Польша',
			self::SaudiArabia->value   => 'Саудовская Аравия',
			self::Turkey->value        => 'Турция',
			self::Israel->value        => 'Израиль',
			self::Russia->value        => 'Россия',
			self::Switzerland->value   => 'Швейцария',
			self::Belgium->value       => 'Бельгия',
			self::Austria->value       => 'Австрия',
			self::Taiwan->value        => 'Тайвань',
			self::Colombia->value      => 'Колумбия',
			self::Peru->value          => 'Перу',
			self::Venezuela->value     => 'Венесуэла',
			self::Norway->value        => 'Норвегия',
			self::Sweden->value        => 'Швеция',
			self::Finland->value       => 'Финляндия',
			self::Greece->value        => 'Греция',
			self::CzechRepublic->value => 'Чехия',
			self::Hungary->value       => 'Венгрия',
			self::Romania->value       => 'Румыния',
			// South American countries
			self::Bolivia->value       => 'Боливия',
			self::Ecuador->value       => 'Эквадор',
			self::Guyana->value        => 'Гайана',
			self::Paraguay->value      => 'Парагвай',
			self::Suriname->value      => 'Суринам',
			self::Uruguay->value       => 'Уругвай',
			self::FrenchGuiana->value  => 'Французская Гвиана',
		];
	}

	public static function labelsTr(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => 'Danimarka',
			self::Netherlands->value   => 'Hollanda',
			self::Poland->value        => 'Polonya',
			self::SaudiArabia->value   => 'Suudi Arabistan',
			self::Turkey->value        => 'Türkiye',
			self::Israel->value        => 'İsrail',
			self::Russia->value        => 'Rusya',
			self::Switzerland->value   => 'İsviçre',
			self::Belgium->value       => 'Belçika',
			self::Austria->value       => 'Avusturya',
			self::Taiwan->value        => 'Tayvan',
			self::Colombia->value      => 'Kolombiya',
			self::Peru->value          => 'Peru',
			self::Venezuela->value     => 'Venezuela',
			self::Norway->value        => 'Norveç',
			self::Sweden->value        => 'İsveç',
			self::Finland->value       => 'Finlandiya',
			self::Greece->value        => 'Yunanistan',
			self::CzechRepublic->value => 'Çekya',
			self::Hungary->value       => 'Macaristan',
			self::Romania->value       => 'Romanya',
			// South American countries
			self::Bolivia->value       => 'Bolivya',
			self::Ecuador->value       => 'Ekvador',
			self::Guyana->value        => 'Guyana',
			self::Paraguay->value      => 'Paraguay',
			self::Suriname->value      => 'Surinam',
			self::Uruguay->value       => 'Uruguay',
			self::FrenchGuiana->value  => 'Fransız Guyanası',
		];
	}

	public static function labelsZh(): array
	{
		return [
			// Original countries
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
			// New countries
			self::Denmark->value       => '丹麦',
			self::Netherlands->value   => '荷兰',
			self::Poland->value        => '波兰',
			self::SaudiArabia->value   => '沙特阿拉伯',
			self::Turkey->value        => '土耳其',
			self::Israel->value        => '以色列',
			self::Russia->value        => '俄罗斯',
			self::Switzerland->value   => '瑞士',
			self::Belgium->value       => '比利时',
			self::Austria->value       => '奥地利',
			self::Taiwan->value        => '台湾',
			self::Colombia->value      => '哥伦比亚',
			self::Peru->value          => '秘鲁',
			self::Venezuela->value     => '委内瑞拉',
			self::Norway->value        => '挪威',
			self::Sweden->value        => '瑞典',
			self::Finland->value       => '芬兰',
			self::Greece->value        => '希腊',
			self::CzechRepublic->value => '捷克',
			self::Hungary->value       => '匈牙利',
			self::Romania->value       => '罗马尼亚',
			// South American countries
			self::Bolivia->value       => '玻利维亚',
			self::Ecuador->value       => '厄瓜多尔',
			self::Guyana->value        => '圭亚那',
			self::Paraguay->value      => '巴拉圭',
			self::Suriname->value      => '苏里南',
			self::Uruguay->value       => '乌拉圭',
			self::FrenchGuiana->value  => '法属圭亚那',
		];
	}
}
