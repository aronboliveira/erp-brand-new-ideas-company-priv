<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum ContactKeyType: string
{
	case Email = 'email';
	case Phone = 'phone';
	case Contact = 'contact';

	/**
	 * Keys that indicate email in various languages/conventions.
	 * (Normalized: lowercase, separators -> "_")
	 */
	public const EMAIL_KEYS = [
		'email',
		'e_mail',
		'e_mail_address',
		'e_mail_adresse',
		'e_mailadresse',
		'e_mail_addresse',
		'correo',
		'correo_electronico',
		'correo_electronico_principal',
		'courriel',
		'courrier_electronique',
		'correio_eletronico',
		'correio_eletrônico',
		'eposta',
		'e_posta',
		'adresse_email',
		'adresse_de_courriel',
		'электронная_почта',
		'メール',
		'メールアドレス',
		'电子邮件',
		'電郵',
		// Additional common variations
		'mail',
		'mail_address',
		'email_address',
		'emailadresse',
		'email_addresse',
		'emailadres',
		'e_post',
		'e_postadress',
		'email_adresa',
		'elektronicka_postova_adresa',
		'elektronicky_postovy_adres',
		'elektroninio_pasto_adresas',
		'ηλεκτρονικό_ταχυδρομείο',
		'електронна_пошта',
		'ელფოსტა',
		'ಇಮೇಲ್',
		'ഇമെയിൽ',
		'ईमेल',
		'ইমেইল',
		'ایمیل',
		'ईमेल_पता',
		'ਈਮੇਲ',
		'மின்னஞ்சல்',
		'ఈమెయిల్',
		'ಇಮೇಲ್_ವಿಳಾಸ',
		'ઇમેઈલ',
		'ई-मेल',
		'ای میل',
		'ايميل',
		'بريد_الكتروني',
	];

	/**
	 * Keys that indicate phone in various languages/conventions.
	 */
	public const PHONE_KEYS = [
		'phone',
		'phone_number',
		'phone_nr',
		'telephone',
		'telefon',
		'telefonnummer',
		'téléphone',
		'tel',
		'telefone',
		'telefone_fixo',
		'telefone_celular',
		'telefone_movel',
		'telefone_móvel',
		'telefono',
		'telefono_movil',
		'telefono_móvil',
		'电话',
		'電話',
		'טלפון',
		'telefono_aziendale',
		// Additional common variations
		'mobile',
		'mobile_number',
		'cell',
		'cellphone',
		'cell_phone',
		'cellular',
		'cellular_phone',
		'mobil',
		'mobiltelefon',
		'mobilnummer',
		'handy',
		'handynummer',
		'gsm',
		'gsm_number',
		'numara',
		'telefon_numarasi',
		'телефон',
		'номер_телефона',
		'телефонний_номер',
		'τηλέφωνο',
		'αριθμός_τηλεφώνου',
		'ਫੋਨ',
		'फ़ोन',
		'ফোন',
		'ਫੋਨ_ਨੰਬਰ',
		'फोन_नंबर',
		'ফোন_নম্বর',
		'தொலைபேசி',
		'దూరవాణి',
		'फोन',
		'टेलीफोन',
		'تلفن',
		'تلفون',
		'رقم_الهاتف',
		'هاتف',
		'رقم_التليفون',
		'هاتف_محمول',
		'موبايل',
		'جوال',
	];

	/**
	 * Generic keys for "contact" in various languages.
	 */
	public const CONTACT_KEYS = [
		'contact',
		'contact_info',
		'contacto',
		'contato',
		'kontakt',
		'контакт',
		'iletişim',
		'contactpersoon',
		'連絡先',
		'联系人',
		'contato_principal',
		'dados_de_contato',
		'datos_de_contacto',
		// Additional common variations
		'contact_person',
		'contact_personne',
		'contact_persona',
		'contact_gegevens',
		'contact_information',
		'contact_details',
		'contactgegevens',
		'contactinformatie',
		'kontaktinformationen',
		'kontaktdaten',
		'informazioni_di_contatto',
		'dati_di_contatto',
		'informations_de_contact',
		'coordonnées',
		'κοντακτ',
		'επικοινωνία',
		'контактна_інформація',
		'контактные_данные',
		'संपर्क',
		'যোগাযোগ',
		'સંપર્ક',
		'संपर्क_माहिती',
		'연락처',
		'संपर्क_जानकारी',
		'संपर्क_विवरण',
		'تواصل',
		'اتصال',
		'معلومات_الاتصال',
		'تفاصيل_الاتصال',
		'persona_de_contacto',
		'pessoa_de_contato',
	];

	/**
	 * Regex patterns used in NormalizesAddresses, to check before calling the normalizer.
	 */
	public const EMAIL_REGEX = '/^[^@\s]+@[^@\s]+\.[^@\s]+$/';
	public const PHONE_REGEX = '/^\+?[0-9 ()\-]{7,20}$/';

	public static function normalize(?string $value): ?self
	{
		if ($value === null) {
			return self::Contact;
		}

		$v = strtolower(trim($value));

		// Normalize separators to underscores
		$v = preg_replace('/[\s\-\.]+/', '_', $v);

		// If it's already a valid case value, return it directly
		foreach (self::cases() as $case) {
			if ($case->value === $v) {
				return $case;
			}
		}

		// Check email keys
		if (in_array($v, self::EMAIL_KEYS, true)) {
			return self::Email;
		}

		// Check phone keys
		if (in_array($v, self::PHONE_KEYS, true)) {
			return self::Phone;
		}

		// Check contact keys
		if (in_array($v, self::CONTACT_KEYS, true)) {
			return self::Contact;
		}

		// Try to match by regex patterns
		if (preg_match(self::EMAIL_REGEX, $v)) {
			return self::Email;
		}

		if (preg_match(self::PHONE_REGEX, $v)) {
			return self::Phone;
		}

		return self::Contact;
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public function label(): string
	{
		return match ($this) {
			self::Email => 'Email',
			self::Phone => 'Phone',
			self::Contact => 'Contact',
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
			self::Email->value => 'E-mail',
			self::Phone->value => 'Telefone',
			self::Contact->value => 'Contato',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Email->value => 'Email',
			self::Phone->value => 'Phone',
			self::Contact->value => 'Contact',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Email->value => 'Correo electrónico',
			self::Phone->value => 'Teléfono',
			self::Contact->value => 'Contacto',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Email->value => 'البريد الإلكتروني',
			self::Phone->value => 'الهاتف',
			self::Contact->value => 'جهة الاتصال',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Email->value => 'E-mail',
			self::Phone->value => 'Telefon',
			self::Contact->value => 'Kontakt',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Email->value => 'E-Mail',
			self::Phone->value => 'Telefon',
			self::Contact->value => 'Kontakt',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Email->value => 'Courriel',
			self::Phone->value => 'Téléphone',
			self::Contact->value => 'Contact',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Email->value => 'דוא"ל',
			self::Phone->value => 'טלפון',
			self::Contact->value => 'איש קשר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Email->value => 'Email',
			self::Phone->value => 'Telefono',
			self::Contact->value => 'Contatto',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Email->value => 'メール',
			self::Phone->value => '電話',
			self::Contact->value => '連絡先',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Email->value => 'E-mail',
			self::Phone->value => 'Telefoon',
			self::Contact->value => 'Contact',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Email->value => 'E-mail',
			self::Phone->value => 'Telefon',
			self::Contact->value => 'Kontakt',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Email->value => 'Электронная почта',
			self::Phone->value => 'Телефон',
			self::Contact->value => 'Контакт',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Email->value => 'E-posta',
			self::Phone->value => 'Telefon',
			self::Contact->value => 'İletişim',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Email->value => '电子邮件',
			self::Phone->value => '电话',
			self::Contact->value => '联系人',
		];
	}

	// Helper methods for business logic
	public function isEmail(): bool
	{
		return $this === self::Email;
	}

	public function isPhone(): bool
	{
		return $this === self::Phone;
	}

	public function isContact(): bool
	{
		return $this === self::Contact;
	}

	public function getKeys(): array
	{
		return match ($this) {
			self::Email => self::EMAIL_KEYS,
			self::Phone => self::PHONE_KEYS,
			self::Contact => self::CONTACT_KEYS,
		};
	}

	public function getRegex(): string
	{
		return match ($this) {
			self::Email => self::EMAIL_REGEX,
			self::Phone => self::PHONE_REGEX,
			self::Contact => '',
		};
	}

	public function getIcon(): string
	{
		return match ($this) {
			self::Email => 'envelope',
			self::Phone => 'phone',
			self::Contact => 'address-book',
		};
	}

	public function getFieldType(): string
	{
		return match ($this) {
			self::Email => 'email',
			self::Phone => 'tel',
			self::Contact => 'text',
		};
	}
}
