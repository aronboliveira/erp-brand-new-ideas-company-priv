<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum AvailableLang: string
{
	case Da    = 'da';
	case Es    = 'es';
	case It    = 'it';
	case En    = 'en';
	case Nl    = 'nl';
	case Pt    = 'pt';
	case Pl    = 'pl';
	case Ar    = 'ar';
	case Zh    = 'zh';
	case De    = 'de';
	case Tr    = 'tr';
	case He    = 'he';
	case Ja    = 'ja';
	case PtBr  = 'pt-br';
	case Fr    = 'fr';
	case Ru    = 'ru';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::En; // Default to English

		$normalizedValue = strtolower(trim($value));
		return match ($normalizedValue) {
			'da', 'da-dk' => self::Da,
			'es', 'es-es' => self::Es,
			'it', 'it-it' => self::It,
			'en', 'en-us', 'en-gb' => self::En,
			'nl', 'nl-nl' => self::Nl,
			'pt', 'pt-pt' => self::Pt,
			'pl', 'pl-pl' => self::Pl,
			'ar', 'ar-sa', 'ar-ae' => self::Ar,
			'zh', 'zh-cn', 'zh-tw' => self::Zh,
			'de', 'de-de', 'de-at', 'de-ch' => self::De,
			'tr', 'tr-tr' => self::Tr,
			'he', 'he-il' => self::He,
			'ja', 'ja-jp' => self::Ja,
			'pt-br', 'pt_br', 'ptbr' => self::PtBr,
			'fr', 'fr-fr', 'fr-ca', 'fr-be' => self::Fr,
			'ru', 'ru-ru' => self::Ru,
			default => self::En,
		};
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
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
			self::Da->value    => 'Dinamarquês',
			self::Es->value    => 'Espanhol',
			self::It->value    => 'Italiano',
			self::En->value    => 'Inglês',
			self::Nl->value    => 'Holandês',
			self::Pt->value    => 'Português (Portugal)',
			self::Pl->value    => 'Polonês',
			self::Ar->value    => 'Árabe',
			self::Zh->value    => 'Chinês',
			self::De->value    => 'Alemão',
			self::Tr->value    => 'Turco',
			self::He->value    => 'Hebraico',
			self::Ja->value    => 'Japonês',
			self::PtBr->value  => 'Português (Brasil)',
			self::Fr->value    => 'Francês',
			self::Ru->value    => 'Russo',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Da->value    => 'Danish',
			self::Es->value    => 'Spanish',
			self::It->value    => 'Italian',
			self::En->value    => 'English',
			self::Nl->value    => 'Dutch',
			self::Pt->value    => 'Portuguese (Portugal)',
			self::Pl->value    => 'Polish',
			self::Ar->value    => 'Arabic',
			self::Zh->value    => 'Chinese',
			self::De->value    => 'German',
			self::Tr->value    => 'Turkish',
			self::He->value    => 'Hebrew',
			self::Ja->value    => 'Japanese',
			self::PtBr->value  => 'Portuguese (Brazil)',
			self::Fr->value    => 'French',
			self::Ru->value    => 'Russian',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Da->value    => 'Danés',
			self::Es->value    => 'Español',
			self::It->value    => 'Italiano',
			self::En->value    => 'Inglés',
			self::Nl->value    => 'Neerlandés',
			self::Pt->value    => 'Portugués (Portugal)',
			self::Pl->value    => 'Polaco',
			self::Ar->value    => 'Árabe',
			self::Zh->value    => 'Chino',
			self::De->value    => 'Alemán',
			self::Tr->value    => 'Turco',
			self::He->value    => 'Hebreo',
			self::Ja->value    => 'Japonés',
			self::PtBr->value  => 'Portugués (Brasil)',
			self::Fr->value    => 'Francés',
			self::Ru->value    => 'Ruso',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Da->value    => 'الدنماركية',
			self::Es->value    => 'الإسبانية',
			self::It->value    => 'الإيطالية',
			self::En->value    => 'الإنجليزية',
			self::Nl->value    => 'الهولندية',
			self::Pt->value    => 'البرتغالية (البرتغال)',
			self::Pl->value    => 'البولندية',
			self::Ar->value    => 'العربية',
			self::Zh->value    => 'الصينية',
			self::De->value    => 'الألمانية',
			self::Tr->value    => 'التركية',
			self::He->value    => 'العبرية',
			self::Ja->value    => 'اليابانية',
			self::PtBr->value  => 'البرتغالية (البرازيل)',
			self::Fr->value    => 'الفرنسية',
			self::Ru->value    => 'الروسية',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Da->value    => 'Dansk',
			self::Es->value    => 'Spansk',
			self::It->value    => 'Italiensk',
			self::En->value    => 'Engelsk',
			self::Nl->value    => 'Hollandsk',
			self::Pt->value    => 'Portugisisk (Portugal)',
			self::Pl->value    => 'Polsk',
			self::Ar->value    => 'Arabisk',
			self::Zh->value    => 'Kinesisk',
			self::De->value    => 'Tysk',
			self::Tr->value    => 'Tyrkisk',
			self::He->value    => 'Hebraisk',
			self::Ja->value    => 'Japansk',
			self::PtBr->value  => 'Portugisisk (Brasilien)',
			self::Fr->value    => 'Fransk',
			self::Ru->value    => 'Russisk',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Da->value    => 'Dänisch',
			self::Es->value    => 'Spanisch',
			self::It->value    => 'Italienisch',
			self::En->value    => 'Englisch',
			self::Nl->value    => 'Niederländisch',
			self::Pt->value    => 'Portugiesisch (Portugal)',
			self::Pl->value    => 'Polnisch',
			self::Ar->value    => 'Arabisch',
			self::Zh->value    => 'Chinesisch',
			self::De->value    => 'Deutsch',
			self::Tr->value    => 'Türkisch',
			self::He->value    => 'Hebräisch',
			self::Ja->value    => 'Japanisch',
			self::PtBr->value  => 'Portugiesisch (Brasilien)',
			self::Fr->value    => 'Französisch',
			self::Ru->value    => 'Russisch',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Da->value    => 'Danois',
			self::Es->value    => 'Espagnol',
			self::It->value    => 'Italien',
			self::En->value    => 'Anglais',
			self::Nl->value    => 'Néerlandais',
			self::Pt->value    => 'Portugais (Portugal)',
			self::Pl->value    => 'Polonais',
			self::Ar->value    => 'Arabe',
			self::Zh->value    => 'Chinois',
			self::De->value    => 'Allemand',
			self::Tr->value    => 'Turc',
			self::He->value    => 'Hébreu',
			self::Ja->value    => 'Japonais',
			self::PtBr->value  => 'Portugais (Brésil)',
			self::Fr->value    => 'Français',
			self::Ru->value    => 'Russe',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Da->value    => 'דנית',
			self::Es->value    => 'ספרדית',
			self::It->value    => 'איטלקית',
			self::En->value    => 'אנגלית',
			self::Nl->value    => 'הולנדית',
			self::Pt->value    => 'פורטוגזית (פורטוגל)',
			self::Pl->value    => 'פולנית',
			self::Ar->value    => 'ערבית',
			self::Zh->value    => 'סינית',
			self::De->value    => 'גרמנית',
			self::Tr->value    => 'טורקית',
			self::He->value    => 'עברית',
			self::Ja->value    => 'יפנית',
			self::PtBr->value  => 'פורטוגזית (ברזיל)',
			self::Fr->value    => 'צרפתית',
			self::Ru->value    => 'רוסית',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Da->value    => 'Danese',
			self::Es->value    => 'Spagnolo',
			self::It->value    => 'Italiano',
			self::En->value    => 'Inglese',
			self::Nl->value    => 'Olandese',
			self::Pt->value    => 'Portoghese (Portogallo)',
			self::Pl->value    => 'Polacco',
			self::Ar->value    => 'Arabo',
			self::Zh->value    => 'Cinese',
			self::De->value    => 'Tedesco',
			self::Tr->value    => 'Turco',
			self::He->value    => 'Ebraico',
			self::Ja->value    => 'Giapponese',
			self::PtBr->value  => 'Portoghese (Brasile)',
			self::Fr->value    => 'Francese',
			self::Ru->value    => 'Russo',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Da->value    => 'デンマーク語',
			self::Es->value    => 'スペイン語',
			self::It->value    => 'イタリア語',
			self::En->value    => '英語',
			self::Nl->value    => 'オランダ語',
			self::Pt->value    => 'ポルトガル語（ポルトガル）',
			self::Pl->value    => 'ポーランド語',
			self::Ar->value    => 'アラビア語',
			self::Zh->value    => '中国語',
			self::De->value    => 'ドイツ語',
			self::Tr->value    => 'トルコ語',
			self::He->value    => 'ヘブライ語',
			self::Ja->value    => '日本語',
			self::PtBr->value  => 'ポルトガル語（ブラジル）',
			self::Fr->value    => 'フランス語',
			self::Ru->value    => 'ロシア語',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Da->value    => 'Deens',
			self::Es->value    => 'Spaans',
			self::It->value    => 'Italiaans',
			self::En->value    => 'Engels',
			self::Nl->value    => 'Nederlands',
			self::Pt->value    => 'Portugees (Portugal)',
			self::Pl->value    => 'Pools',
			self::Ar->value    => 'Arabisch',
			self::Zh->value    => 'Chinees',
			self::De->value    => 'Duits',
			self::Tr->value    => 'Turks',
			self::He->value    => 'Hebreeuws',
			self::Ja->value    => 'Japans',
			self::PtBr->value  => 'Portugees (Brazilië)',
			self::Fr->value    => 'Frans',
			self::Ru->value    => 'Russisch',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Da->value    => 'Duński',
			self::Es->value    => 'Hiszpański',
			self::It->value    => 'Włoski',
			self::En->value    => 'Angielski',
			self::Nl->value    => 'Holenderski',
			self::Pt->value    => 'Portugalski (Portugalia)',
			self::Pl->value    => 'Polski',
			self::Ar->value    => 'Arabski',
			self::Zh->value    => 'Chiński',
			self::De->value    => 'Niemiecki',
			self::Tr->value    => 'Turecki',
			self::He->value    => 'Hebrajski',
			self::Ja->value    => 'Japoński',
			self::PtBr->value  => 'Portugalski (Brazylia)',
			self::Fr->value    => 'Francuski',
			self::Ru->value    => 'Rosyjski',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Da->value    => 'Датский',
			self::Es->value    => 'Испанский',
			self::It->value    => 'Итальянский',
			self::En->value    => 'Английский',
			self::Nl->value    => 'Нидерландский',
			self::Pt->value    => 'Португальский (Португалия)',
			self::Pl->value    => 'Польский',
			self::Ar->value    => 'Арабский',
			self::Zh->value    => 'Китайский',
			self::De->value    => 'Немецкий',
			self::Tr->value    => 'Турецкий',
			self::He->value    => 'Иврит',
			self::Ja->value    => 'Японский',
			self::PtBr->value  => 'Португальский (Бразилия)',
			self::Fr->value    => 'Французский',
			self::Ru->value    => 'Русский',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Da->value    => 'Danca',
			self::Es->value    => 'İspanyolca',
			self::It->value    => 'İtalyanca',
			self::En->value    => 'İngilizce',
			self::Nl->value    => 'Hollandaca',
			self::Pt->value    => 'Portekizce (Portekiz)',
			self::Pl->value    => 'Lehçe',
			self::Ar->value    => 'Arapça',
			self::Zh->value    => 'Çince',
			self::De->value    => 'Almanca',
			self::Tr->value    => 'Türkçe',
			self::He->value    => 'İbranice',
			self::Ja->value    => 'Japonca',
			self::PtBr->value  => 'Portekizce (Brezilya)',
			self::Fr->value    => 'Fransızca',
			self::Ru->value    => 'Rusça',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Da->value    => '丹麦语',
			self::Es->value    => '西班牙语',
			self::It->value    => '意大利语',
			self::En->value    => '英语',
			self::Nl->value    => '荷兰语',
			self::Pt->value    => '葡萄牙语（葡萄牙）',
			self::Pl->value    => '波兰语',
			self::Ar->value    => '阿拉伯语',
			self::Zh->value    => '中文',
			self::De->value    => '德语',
			self::Tr->value    => '土耳其语',
			self::He->value    => '希伯来语',
			self::Ja->value    => '日语',
			self::PtBr->value  => '葡萄牙语（巴西）',
			self::Fr->value    => '法语',
			self::Ru->value    => '俄语',
		];
	}
}
