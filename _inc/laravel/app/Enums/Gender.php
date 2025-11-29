<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum Gender: string
{
	case Male = 'male';
	case Female = 'female';
	case NonBinary = 'non_binary';
	case Other = 'other';
	case PreferNotToSay = 'prefer_not_to_say';

	public static function normalize(string|null|self $v): ?self
	{
		if ($v instanceof self)
			return $v;
		if ($v === null) return self::PreferNotToSay;
		$v = strtolower(trim($v));
		return match ($v) {
			'm', 'masc', 'male', 'homem', 'masculino'                   => self::Male,
			'f', 'fem', 'female', 'mulher', 'feminino'                 => self::Female,
			'nb', 'non-binary', 'não binário', 'non_binary', 'no binario' => self::NonBinary,
			'other', 'outro', 'outros', 'otro', 'otros'                    => self::Other,
			'n/a', 'na', 'prefiro não informar', 'prefer_not_to_say', 'prefiero no decir' => self::PreferNotToSay,
			default => null,
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
			self::Male->value => 'Masculino',
			self::Female->value => 'Feminino',
			self::NonBinary->value => 'Não Binário',
			self::Other->value => 'Outro',
			self::PreferNotToSay->value => 'Prefiro Não Informar',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Male->value => 'Male',
			self::Female->value => 'Female',
			self::NonBinary->value => 'Non-Binary',
			self::Other->value => 'Other',
			self::PreferNotToSay->value => 'Prefer Not to Say',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Male->value => 'Masculino',
			self::Female->value => 'Femenino',
			self::NonBinary->value => 'No Binario',
			self::Other->value => 'Otro',
			self::PreferNotToSay->value => 'Prefiero No Decir',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Male->value => 'ذكر',
			self::Female->value => 'أنثى',
			self::NonBinary->value => 'غير ثنائي',
			self::Other->value => 'آخر',
			self::PreferNotToSay->value => 'أفضل عدم القول',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Male->value => 'Mand',
			self::Female->value => 'Kvinde',
			self::NonBinary->value => 'Ikke-binær',
			self::Other->value => 'Andet',
			self::PreferNotToSay->value => 'Foretrækker ikke at sige',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Male->value => 'Männlich',
			self::Female->value => 'Weiblich',
			self::NonBinary->value => 'Nicht-binär',
			self::Other->value => 'Andere',
			self::PreferNotToSay->value => 'Möchte ich nicht angeben',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Male->value => 'Masculin',
			self::Female->value => 'Féminin',
			self::NonBinary->value => 'Non-binaire',
			self::Other->value => 'Autre',
			self::PreferNotToSay->value => 'Je préfère ne pas le dire',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Male->value => 'זכר',
			self::Female->value => 'נקבה',
			self::NonBinary->value => 'לא בינארי',
			self::Other->value => 'אחר',
			self::PreferNotToSay->value => 'מעדיף/ה לא לומר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Male->value => 'Maschio',
			self::Female->value => 'Femmina',
			self::NonBinary->value => 'Non-binario',
			self::Other->value => 'Altro',
			self::PreferNotToSay->value => 'Preferisco non dirlo',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Male->value => '男性',
			self::Female->value => '女性',
			self::NonBinary->value => 'ノンバイナリー',
			self::Other->value => 'その他',
			self::PreferNotToSay->value => '回答を控える',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Male->value => 'Man',
			self::Female->value => 'Vrouw',
			self::NonBinary->value => 'Non-binair',
			self::Other->value => 'Anders',
			self::PreferNotToSay->value => 'Zeg ik liever niet',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Male->value => 'Mężczyzna',
			self::Female->value => 'Kobieta',
			self::NonBinary->value => 'Niebinarny',
			self::Other->value => 'Inna',
			self::PreferNotToSay->value => 'Wolę nie mówić',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Male->value => 'Мужской',
			self::Female->value => 'Женский',
			self::NonBinary->value => 'Небинарный',
			self::Other->value => 'Другой',
			self::PreferNotToSay->value => 'Предпочитаю не указывать',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Male->value => 'Erkek',
			self::Female->value => 'Kadın',
			self::NonBinary->value => 'İkili Olmayan',
			self::Other->value => 'Diğer',
			self::PreferNotToSay->value => 'Söylemeyi Tercih Etmiyorum',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Male->value => '男性',
			self::Female->value => '女性',
			self::NonBinary->value => '非二元',
			self::Other->value => '其他',
			self::PreferNotToSay->value => '不想说明',
		];
	}
}
