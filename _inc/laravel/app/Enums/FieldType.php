<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants as DC;

enum FieldType: string
{
	case Text            = 'text';
	case Email           = 'email';
	case Tel             = 'tel';
	case Number          = 'number';
	case Date            = 'date';
	case Url             = 'url';
	case RadioGroup      = 'radiogroup';
	case Checkbox        = 'checkbox';
	case Select          = 'select';
	case Time            = 'time';
	case DateTimeLocal   = 'datetime-local';
	case Month           = 'month';
	case Week            = 'week';
	case Textarea        = 'textarea';
	case Range           = 'range';
	case Color           = 'color';
	case File            = 'file';
	case Password        = 'password';
	case Search          = 'search';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Text;

		$normalizedValue = preg_replace('/[^a-z0-9_\-]/', '', strtolower(trim($value ?? '')));
		return match ($normalizedValue) {
			// Text variations
			'text', 'string', 'input', 'textfield' => self::Text,

			// Email variations
			'email', 'mail', 'e-mail' => self::Email,

			// Telephone variations
			'tel', 'telephone', 'phone', 'phonenumber', 'mobile' => self::Tel,

			// Number variations
			'number', 'numeric', 'integer', 'float', 'decimal' => self::Number,

			// Date variations
			'date', 'calendar', 'datepicker' => self::Date,

			// URL variations
			'url', 'link', 'website', 'uri' => self::Url,

			// Radio group variations
			'radiogroup', 'radio', 'radiobuttons', 'radiobutton' => self::RadioGroup,

			// Checkbox variations
			'checkbox', 'check', 'checkboxgroup', 'boolean' => self::Checkbox,

			// Select variations
			'select', 'dropdown', 'combobox', 'selectbox' => self::Select,

			// Time variations
			'time', 'timepicker', 'clock' => self::Time,

			// Datetime variations
			'datetime-local', 'datetime', 'timestamp', 'datetimepicker' => self::DateTimeLocal,

			// Month variations
			'month', 'monthpicker', 'yearmonth' => self::Month,

			// Week variations
			'week', 'weekpicker' => self::Week,

			// Textarea variations
			'textarea', 'multilinetext', 'longtext', 'memo' => self::Textarea,

			// Range variations
			'range', 'slider', 'rangeinput' => self::Range,

			// Color variations
			'color', 'colorpicker', 'colorselector' => self::Color,

			// File variations
			'file', 'fileupload', 'upload', 'attachment' => self::File,

			// Password variations
			'password', 'pass', 'secret', 'hiddeninput' => self::Password,

			// Search variations
			'search', 'searchbox', 'searchfield' => self::Search,

			default => self::Text,
		};
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public static function labels($lang = DC::DEFAULT_LANG): array
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

	public static function placeholders($lang = DC::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		return match ($lang) {
			'pt-br', 'pt' => self::placeholdersPtBr(),
			'es', 'es-es' => self::placeholdersEs(),
			'ar', 'ar-sa' => self::placeholdersAr(),
			'da', 'da-dk' => self::placeholdersDa(),
			'de', 'de-de' => self::placeholdersDe(),
			'fr', 'fr-fr' => self::placeholdersFr(),
			'he', 'he-il' => self::placeholdersHe(),
			'it', 'it-it' => self::placeholdersIt(),
			'ja', 'ja-jp' => self::placeholdersJa(),
			'nl', 'nl-nl' => self::placeholdersNl(),
			'pl', 'pl-pl' => self::placeholdersPl(),
			'ru', 'ru-ru' => self::placeholdersRu(),
			'tr', 'tr-tr' => self::placeholdersTr(),
			'zh', 'zh-cn' => self::placeholdersZh(),
			default => self::placeholdersEn(),
		};
	}

	/**
	 * Get HTML input type attribute for each field type
	 */
	public static function htmlTypes(): array
	{
		return [
			self::Text->value          => 'text',
			self::Email->value         => 'email',
			self::Tel->value           => 'tel',
			self::Number->value        => 'number',
			self::Date->value          => 'date',
			self::Url->value           => 'url',
			self::RadioGroup->value    => 'radio',
			self::Checkbox->value      => 'checkbox',
			self::Select->value        => 'select',
			self::Time->value          => 'time',
			self::DateTimeLocal->value => 'datetime-local',
			self::Month->value         => 'month',
			self::Week->value          => 'week',
			self::Textarea->value      => 'textarea',
			self::Range->value         => 'range',
			self::Color->value         => 'color',
			self::File->value          => 'file',
			self::Password->value      => 'password',
			self::Search->value        => 'search',
		];
	}

	/**
	 * Get validation rules for each field type
	 */
	public static function validationRules(): array
	{
		return [
			self::Text->value          => 'string|max:255',
			self::Email->value         => 'email|max:255',
			self::Tel->value           => 'string|regex:/^[\d\s\-\+\(\)]+$/',
			self::Number->value        => 'numeric',
			self::Date->value          => 'date',
			self::Url->value           => 'url|max:255',
			self::RadioGroup->value    => 'string|in_array',
			self::Checkbox->value      => 'boolean',
			self::Select->value        => 'string',
			self::Time->value          => 'date_format:H:i',
			self::DateTimeLocal->value => 'date',
			self::Month->value         => 'date_format:Y-m',
			self::Week->value          => 'date_format:Y-\WW',
			self::Textarea->value      => 'string|max:65535',
			self::Range->value         => 'numeric|min:0|max:100',
			self::Color->value         => 'regex:/^#[0-9A-F]{6}$/i',
			self::File->value          => 'file|max:10240', // 10MB
			self::Password->value      => 'string|min:8|max:255',
			self::Search->value        => 'string|max:255',
		];
	}

	/**
	 * Get icon for each field type (for UI)
	 */
	public static function icons(): array
	{
		return [
			self::Text->value          => 'text-fields',
			self::Email->value         => 'email',
			self::Tel->value           => 'phone',
			self::Number->value        => 'numbers',
			self::Date->value          => 'calendar-today',
			self::Url->value           => 'link',
			self::RadioGroup->value    => 'radio-button-checked',
			self::Checkbox->value      => 'check-box',
			self::Select->value        => 'arrow-drop-down',
			self::Time->value          => 'access-time',
			self::DateTimeLocal->value => 'date-range',
			self::Month->value         => 'event-note',
			self::Week->value          => 'view-week',
			self::Textarea->value      => 'notes',
			self::Range->value         => 'linear-scale',
			self::Color->value         => 'palette',
			self::File->value          => 'attach-file',
			self::Password->value      => 'lock',
			self::Search->value        => 'search',
		];
	}

	/**
	 * Check if field type requires multiple values
	 */
	public function isMultiple(): bool
	{
		return match ($this) {
			self::Select,
			self::Checkbox,
			self::File => true,
			default => false,
		};
	}

	public function isTextual(): bool
	{
		return match ($this) {
			self::Text,
			self::Email,
			self::Tel,
			self::Textarea,
			self::Search,
			self::Url,
			self::Password => true,
			default => false,
		};
	}

	public function isNumeric(): bool
	{
		return match ($this) {
			self::Number,
			self::Range => true,
			default => false,
		};
	}

	public function isCheckable(): bool
	{
		return match ($this) {
			self::Checkbox,
			self::RadioGroup => true,
			default => false,
		};
	}

	/**
	 * Check if field type is a date/time type
	 */
	public function isDateTime(): bool
	{
		return match ($this) {
			self::Date,
			self::Time,
			self::DateTimeLocal,
			self::Month,
			self::Week => true,
			default => false,
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::Text->value          => 'Text',
			self::Email->value         => 'Email',
			self::Tel->value           => 'Telephone',
			self::Number->value        => 'Number',
			self::Date->value          => 'Date',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'Radio Group',
			self::Checkbox->value      => 'Checkbox',
			self::Select->value        => 'Select',
			self::Time->value          => 'Time',
			self::DateTimeLocal->value => 'Date & Time',
			self::Month->value         => 'Month',
			self::Week->value          => 'Week',
			self::Textarea->value      => 'Textarea',
			self::Range->value         => 'Range',
			self::Color->value         => 'Color Picker',
			self::File->value          => 'File',
			self::Password->value      => 'Password',
			self::Search->value        => 'Search',
		];
	}

	// English Placeholders
	public static function placeholdersEn(): array
	{
		return [
			self::Text->value          => 'Enter your name',
			self::Email->value         => 'user@example.com',
			self::Tel->value           => '+1 (555) 123-4567',
			self::Number->value        => 'Enter quantity',
			self::Date->value          => 'MM/DD/YYYY',
			self::Url->value           => 'https://example.com',
			self::RadioGroup->value    => 'Select an option',
			self::Checkbox->value      => 'Check to confirm',
			self::Select->value        => 'Choose an option',
			self::Time->value          => 'HH:MM',
			self::DateTimeLocal->value => 'MM/DD/YYYY HH:MM',
			self::Month->value         => 'YYYY-MM',
			self::Week->value          => 'YYYY-W##',
			self::Textarea->value      => 'Enter your message here...',
			self::Range->value         => 'Adjust the value',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'Choose a file',
			self::Password->value      => '••••••••',
			self::Search->value        => 'Search...',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::Text->value          => 'Texto',
			self::Email->value         => 'E-mail',
			self::Tel->value           => 'Telefone',
			self::Number->value        => 'Número',
			self::Date->value          => 'Data',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'Grupo de Rádio',
			self::Checkbox->value      => 'Caixa de Seleção',
			self::Select->value        => 'Selecionar',
			self::Time->value          => 'Hora',
			self::DateTimeLocal->value => 'Data & Hora',
			self::Month->value         => 'Mês',
			self::Week->value          => 'Semana',
			self::Textarea->value      => 'Área de Texto',
			self::Range->value         => 'Intervalo',
			self::Color->value         => 'Seletor de Cor',
			self::File->value          => 'Arquivo',
			self::Password->value      => 'Senha',
			self::Search->value        => 'Pesquisar',
		];
	}

	// Portuguese (Brazil) Placeholders
	public static function placeholdersPtBr(): array
	{
		return [
			self::Text->value          => 'Digite seu nome',
			self::Email->value         => 'usuario@exemplo.com',
			self::Tel->value           => '+55 (11) 99999-9999',
			self::Number->value        => 'Digite a quantidade',
			self::Date->value          => 'DD/MM/AAAA',
			self::Url->value           => 'https://exemplo.com',
			self::RadioGroup->value    => 'Selecione uma opção',
			self::Checkbox->value      => 'Marque para confirmar',
			self::Select->value        => 'Escolha uma opção',
			self::Time->value          => 'HH:MM',
			self::DateTimeLocal->value => 'DD/MM/AAAA HH:MM',
			self::Month->value         => 'AAAA-MM',
			self::Week->value          => 'AAAA-S##',
			self::Textarea->value      => 'Digite sua mensagem aqui...',
			self::Range->value         => 'Ajuste o valor',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'Escolha um arquivo',
			self::Password->value      => '••••••••',
			self::Search->value        => 'Pesquisar...',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::Text->value          => 'Texto',
			self::Email->value         => 'Correo Electrónico',
			self::Tel->value           => 'Teléfono',
			self::Number->value        => 'Número',
			self::Date->value          => 'Fecha',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'Grupo de Radio',
			self::Checkbox->value      => 'Casilla de Verificación',
			self::Select->value        => 'Seleccionar',
			self::Time->value          => 'Hora',
			self::DateTimeLocal->value => 'Fecha & Hora',
			self::Month->value         => 'Mes',
			self::Week->value          => 'Semana',
			self::Textarea->value      => 'Área de Texto',
			self::Range->value         => 'Rango',
			self::Color->value         => 'Selector de Color',
			self::File->value          => 'Archivo',
			self::Password->value      => 'Contraseña',
			self::Search->value        => 'Buscar',
		];
	}

	// Spanish Placeholders
	public static function placeholdersEs(): array
	{
		return [
			self::Text->value          => 'Ingrese su nombre',
			self::Email->value         => 'usuario@ejemplo.com',
			self::Tel->value           => '+34 912 345 678',
			self::Number->value        => 'Ingrese cantidad',
			self::Date->value          => 'DD/MM/AAAA',
			self::Url->value           => 'https://ejemplo.com',
			self::RadioGroup->value    => 'Seleccione una opción',
			self::Checkbox->value      => 'Marque para confirmar',
			self::Select->value        => 'Elija una opción',
			self::Time->value          => 'HH:MM',
			self::DateTimeLocal->value => 'DD/MM/AAAA HH:MM',
			self::Month->value         => 'AAAA-MM',
			self::Week->value          => 'AAAA-S##',
			self::Textarea->value      => 'Ingrese su mensaje aquí...',
			self::Range->value         => 'Ajuste el valor',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'Elija un archivo',
			self::Password->value      => '••••••••',
			self::Search->value        => 'Buscar...',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::Text->value          => 'نص',
			self::Email->value         => 'البريد الإلكتروني',
			self::Tel->value           => 'هاتف',
			self::Number->value        => 'رقم',
			self::Date->value          => 'تاريخ',
			self::Url->value           => 'رابط',
			self::RadioGroup->value    => 'مجموعة اختيار',
			self::Checkbox->value      => 'مربع اختيار',
			self::Select->value        => 'قائمة منسدلة',
			self::Time->value          => 'وقت',
			self::DateTimeLocal->value => 'تاريخ ووقت',
			self::Month->value         => 'شهر',
			self::Week->value          => 'أسبوع',
			self::Textarea->value      => 'منطقة نصية',
			self::Range->value         => 'نطاق',
			self::Color->value         => 'منتقي الألوان',
			self::File->value          => 'ملف',
			self::Password->value      => 'كلمة المرور',
			self::Search->value        => 'بحث',
		];
	}

	// Arabic Placeholders
	public static function placeholdersAr(): array
	{
		return [
			self::Text->value          => 'أدخل اسمك',
			self::Email->value         => 'user@example.com',
			self::Tel->value           => '+966 55 123 4567',
			self::Number->value        => 'أدخل الكمية',
			self::Date->value          => 'يوم/شهر/سنة',
			self::Url->value           => 'https://example.com',
			self::RadioGroup->value    => 'اختر خيارًا',
			self::Checkbox->value      => 'حدد للتأكيد',
			self::Select->value        => 'اختر خيارًا',
			self::Time->value          => 'ساعة:دقيقة',
			self::DateTimeLocal->value => 'يوم/شهر/سنة ساعة:دقيقة',
			self::Month->value         => 'سنة-شهر',
			self::Week->value          => 'سنة-أسبوع##',
			self::Textarea->value      => 'أدخل رسالتك هنا...',
			self::Range->value         => 'اضبط القيمة',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'اختر ملفًا',
			self::Password->value      => '••••••••',
			self::Search->value        => 'بحث...',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::Text->value          => 'Text',
			self::Email->value         => 'E-Mail',
			self::Tel->value           => 'Telefon',
			self::Number->value        => 'Nummer',
			self::Date->value          => 'Datum',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'Optionsgruppe',
			self::Checkbox->value      => 'Kontrollkästchen',
			self::Select->value        => 'Auswahl',
			self::Time->value          => 'Uhrzeit',
			self::DateTimeLocal->value => 'Datum & Uhrzeit',
			self::Month->value         => 'Monat',
			self::Week->value          => 'Woche',
			self::Textarea->value      => 'Textbereich',
			self::Range->value         => 'Bereich',
			self::Color->value         => 'Farbwähler',
			self::File->value          => 'Datei',
			self::Password->value      => 'Passwort',
			self::Search->value        => 'Suche',
		];
	}

	// German Placeholders
	public static function placeholdersDe(): array
	{
		return [
			self::Text->value          => 'Geben Sie Ihren Namen ein',
			self::Email->value         => 'benutzer@beispiel.de',
			self::Tel->value           => '+49 123 456789',
			self::Number->value        => 'Menge eingeben',
			self::Date->value          => 'TT.MM.JJJJ',
			self::Url->value           => 'https://beispiel.de',
			self::RadioGroup->value    => 'Option wählen',
			self::Checkbox->value      => 'Zur Bestätigung ankreuzen',
			self::Select->value        => 'Option auswählen',
			self::Time->value          => 'HH:MM',
			self::DateTimeLocal->value => 'TT.MM.JJJJ HH:MM',
			self::Month->value         => 'JJJJ-MM',
			self::Week->value          => 'JJJJ-W##',
			self::Textarea->value      => 'Geben Sie hier Ihre Nachricht ein...',
			self::Range->value         => 'Wert anpassen',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'Datei auswählen',
			self::Password->value      => '••••••••',
			self::Search->value        => 'Suchen...',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::Text->value          => 'Texte',
			self::Email->value         => 'E-mail',
			self::Tel->value           => 'Téléphone',
			self::Number->value        => 'Numéro',
			self::Date->value          => 'Date',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'Groupe Radio',
			self::Checkbox->value      => 'Case à Cocher',
			self::Select->value        => 'Sélectionner',
			self::Time->value          => 'Heure',
			self::DateTimeLocal->value => 'Date & Heure',
			self::Month->value         => 'Mois',
			self::Week->value          => 'Semaine',
			self::Textarea->value      => 'Zone de Texte',
			self::Range->value         => 'Plage',
			self::Color->value         => 'Sélecteur de Couleur',
			self::File->value          => 'Fichier',
			self::Password->value      => 'Mot de Passe',
			self::Search->value        => 'Recherche',
		];
	}

	// French Placeholders
	public static function placeholdersFr(): array
	{
		return [
			self::Text->value          => 'Entrez votre nom',
			self::Email->value         => 'utilisateur@exemple.fr',
			self::Tel->value           => '+33 1 23 45 67 89',
			self::Number->value        => 'Entrez la quantité',
			self::Date->value          => 'JJ/MM/AAAA',
			self::Url->value           => 'https://exemple.fr',
			self::RadioGroup->value    => 'Sélectionnez une option',
			self::Checkbox->value      => 'Cocher pour confirmer',
			self::Select->value        => 'Choisissez une option',
			self::Time->value          => 'HH:MM',
			self::DateTimeLocal->value => 'JJ/MM/AAAA HH:MM',
			self::Month->value         => 'AAAA-MM',
			self::Week->value          => 'AAAA-S##',
			self::Textarea->value      => 'Entrez votre message ici...',
			self::Range->value         => 'Ajustez la valeur',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'Choisissez un fichier',
			self::Password->value      => '••••••••',
			self::Search->value        => 'Rechercher...',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::Text->value          => 'Tekst',
			self::Email->value         => 'E-mail',
			self::Tel->value           => 'Telefon',
			self::Number->value        => 'Nummer',
			self::Date->value          => 'Dato',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'Radiogruppe',
			self::Checkbox->value      => 'Afkrydsningsfelt',
			self::Select->value        => 'Vælg',
			self::Time->value          => 'Tid',
			self::DateTimeLocal->value => 'Dato & Tid',
			self::Month->value         => 'Måned',
			self::Week->value          => 'Uge',
			self::Textarea->value      => 'Tekstområde',
			self::Range->value         => 'Interval',
			self::Color->value         => 'Farvevælger',
			self::File->value          => 'Fil',
			self::Password->value      => 'Adgangskode',
			self::Search->value        => 'Søg',
		];
	}

	// Danish Placeholders
	public static function placeholdersDa(): array
	{
		return [
			self::Text->value          => 'Indtast dit navn',
			self::Email->value         => 'bruger@eksempel.dk',
			self::Tel->value           => '+45 12 34 56 78',
			self::Number->value        => 'Indtast antal',
			self::Date->value          => 'DD-MM-ÅÅÅÅ',
			self::Url->value           => 'https://eksempel.dk',
			self::RadioGroup->value    => 'Vælg en mulighed',
			self::Checkbox->value      => 'Marker for at bekræfte',
			self::Select->value        => 'Vælg en mulighed',
			self::Time->value          => 'TT:MM',
			self::DateTimeLocal->value => 'DD-MM-ÅÅÅÅ TT:MM',
			self::Month->value         => 'ÅÅÅÅ-MM',
			self::Week->value          => 'ÅÅÅÅ-U##',
			self::Textarea->value      => 'Indtast din besked her...',
			self::Range->value         => 'Juster værdien',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'Vælg en fil',
			self::Password->value      => '••••••••',
			self::Search->value        => 'Søg...',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::Text->value          => 'טקסט',
			self::Email->value         => 'דואר אלקטרוני',
			self::Tel->value           => 'טלפון',
			self::Number->value        => 'מספר',
			self::Date->value          => 'תאריך',
			self::Url->value           => 'כתובת אתר',
			self::RadioGroup->value    => 'קבוצת רדיו',
			self::Checkbox->value      => 'תיבת סימון',
			self::Select->value        => 'בחירה',
			self::Time->value          => 'שעה',
			self::DateTimeLocal->value => 'תאריך & שעה',
			self::Month->value         => 'חודש',
			self::Week->value          => 'שבוע',
			self::Textarea->value      => 'אזור טקסט',
			self::Range->value         => 'טווח',
			self::Color->value         => 'בורר צבעים',
			self::File->value          => 'קובץ',
			self::Password->value      => 'סיסמה',
			self::Search->value        => 'חיפוש',
		];
	}

	// Hebrew Placeholders
	public static function placeholdersHe(): array
	{
		return [
			self::Text->value          => 'הכנס את שמך',
			self::Email->value         => 'user@example.co.il',
			self::Tel->value           => '+972 50 123 4567',
			self::Number->value        => 'הכנס כמות',
			self::Date->value          => 'יום/חודש/שנה',
			self::Url->value           => 'https://example.co.il',
			self::RadioGroup->value    => 'בחר אפשרות',
			self::Checkbox->value      => 'סמן לאישור',
			self::Select->value        => 'בחר אפשרות',
			self::Time->value          => 'שעה:דקות',
			self::DateTimeLocal->value => 'יום/חודש/שנה שעה:דקות',
			self::Month->value         => 'שנה-חודש',
			self::Week->value          => 'שנה-שבוע##',
			self::Textarea->value      => 'הכנס את ההודעה שלך כאן...',
			self::Range->value         => 'כוון את הערך',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'בחר קובץ',
			self::Password->value      => '••••••••',
			self::Search->value        => 'חיפוש...',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::Text->value          => 'Testo',
			self::Email->value         => 'Email',
			self::Tel->value           => 'Telefono',
			self::Number->value        => 'Numero',
			self::Date->value          => 'Data',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'Gruppo Radio',
			self::Checkbox->value      => 'Casella di Spunta',
			self::Select->value        => 'Seleziona',
			self::Time->value          => 'Ora',
			self::DateTimeLocal->value => 'Data & Ora',
			self::Month->value         => 'Mese',
			self::Week->value          => 'Settimana',
			self::Textarea->value      => 'Area di Testo',
			self::Range->value         => 'Intervallo',
			self::Color->value         => 'Selettore Colore',
			self::File->value          => 'File',
			self::Password->value      => 'Password',
			self::Search->value        => 'Cerca',
		];
	}

	// Italian Placeholders
	public static function placeholdersIt(): array
	{
		return [
			self::Text->value          => 'Inserisci il tuo nome',
			self::Email->value         => 'utente@esempio.it',
			self::Tel->value           => '+39 02 1234567',
			self::Number->value        => 'Inserisci quantità',
			self::Date->value          => 'GG/MM/AAAA',
			self::Url->value           => 'https://esempio.it',
			self::RadioGroup->value    => 'Seleziona un\'opzione',
			self::Checkbox->value      => 'Spunta per confermare',
			self::Select->value        => 'Scegli un\'opzione',
			self::Time->value          => 'HH:MM',
			self::DateTimeLocal->value => 'GG/MM/AAAA HH:MM',
			self::Month->value         => 'AAAA-MM',
			self::Week->value          => 'AAAA-S##',
			self::Textarea->value      => 'Inserisci il tuo messaggio qui...',
			self::Range->value         => 'Regola il valore',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'Scegli un file',
			self::Password->value      => '••••••••',
			self::Search->value        => 'Cerca...',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::Text->value          => 'テキスト',
			self::Email->value         => 'メールアドレス',
			self::Tel->value           => '電話番号',
			self::Number->value        => '数字',
			self::Date->value          => '日付',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'ラジオグループ',
			self::Checkbox->value      => 'チェックボックス',
			self::Select->value        => '選択',
			self::Time->value          => '時間',
			self::DateTimeLocal->value => '日付 & 時間',
			self::Month->value         => '月',
			self::Week->value          => '週',
			self::Textarea->value      => 'テキストエリア',
			self::Range->value         => '範囲',
			self::Color->value         => 'カラーピッカー',
			self::File->value          => 'ファイル',
			self::Password->value      => 'パスワード',
			self::Search->value        => '検索',
		];
	}

	// Japanese Placeholders
	public static function placeholdersJa(): array
	{
		return [
			self::Text->value          => '名前を入力',
			self::Email->value         => 'user@example.jp',
			self::Tel->value           => '090-1234-5678',
			self::Number->value        => '数量を入力',
			self::Date->value          => 'YYYY年MM月DD日',
			self::Url->value           => 'https://example.jp',
			self::RadioGroup->value    => 'オプションを選択',
			self::Checkbox->value      => '確認のためにチェック',
			self::Select->value        => 'オプションを選択',
			self::Time->value          => 'HH:MM',
			self::DateTimeLocal->value => 'YYYY年MM月DD日 HH:MM',
			self::Month->value         => 'YYYY-MM',
			self::Week->value          => 'YYYY-W##',
			self::Textarea->value      => 'メッセージを入力...',
			self::Range->value         => '値を調整',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'ファイルを選択',
			self::Password->value      => '••••••••',
			self::Search->value        => '検索...',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::Text->value          => 'Tekst',
			self::Email->value         => 'E-mail',
			self::Tel->value           => 'Telefoon',
			self::Number->value        => 'Nummer',
			self::Date->value          => 'Datum',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'Radiogroep',
			self::Checkbox->value      => 'Selectievakje',
			self::Select->value        => 'Selecteren',
			self::Time->value          => 'Tijd',
			self::DateTimeLocal->value => 'Datum & Tijd',
			self::Month->value         => 'Maand',
			self::Week->value          => 'Week',
			self::Textarea->value      => 'Tekstvak',
			self::Range->value         => 'Bereik',
			self::Color->value         => 'Kleurkiezer',
			self::File->value          => 'Bestand',
			self::Password->value      => 'Wachtwoord',
			self::Search->value        => 'Zoeken',
		];
	}

	// Dutch Placeholders
	public static function placeholdersNl(): array
	{
		return [
			self::Text->value          => 'Voer uw naam in',
			self::Email->value         => 'gebruiker@voorbeeld.nl',
			self::Tel->value           => '+31 6 12345678',
			self::Number->value        => 'Voer hoeveelheid in',
			self::Date->value          => 'DD-MM-JJJJ',
			self::Url->value           => 'https://voorbeeld.nl',
			self::RadioGroup->value    => 'Selecteer een optie',
			self::Checkbox->value      => 'Vink aan om te bevestigen',
			self::Select->value        => 'Kies een optie',
			self::Time->value          => 'UU:MM',
			self::DateTimeLocal->value => 'DD-MM-JJJJ UU:MM',
			self::Month->value         => 'JJJJ-MM',
			self::Week->value          => 'JJJJ-W##',
			self::Textarea->value      => 'Voer uw bericht hier in...',
			self::Range->value         => 'Pas de waarde aan',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'Kies een bestand',
			self::Password->value      => '••••••••',
			self::Search->value        => 'Zoeken...',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::Text->value          => 'Tekst',
			self::Email->value         => 'E-mail',
			self::Tel->value           => 'Telefon',
			self::Number->value        => 'Liczba',
			self::Date->value          => 'Data',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'Grupa Radiowa',
			self::Checkbox->value      => 'Pole Wyboru',
			self::Select->value        => 'Wybierz',
			self::Time->value          => 'Czas',
			self::DateTimeLocal->value => 'Data & Czas',
			self::Month->value         => 'Miesiąc',
			self::Week->value          => 'Tydzień',
			self::Textarea->value      => 'Obszar Tekstowy',
			self::Range->value         => 'Zakres',
			self::Color->value         => 'Wybór Koloru',
			self::File->value          => 'Plik',
			self::Password->value      => 'Hasło',
			self::Search->value        => 'Szukaj',
		];
	}

	// Polish Placeholders
	public static function placeholdersPl(): array
	{
		return [
			self::Text->value          => 'Wprowadź swoje imię',
			self::Email->value         => 'uzytkownik@przyklad.pl',
			self::Tel->value           => '+48 123 456 789',
			self::Number->value        => 'Wprowadź ilość',
			self::Date->value          => 'DD.MM.RRRR',
			self::Url->value           => 'https://przyklad.pl',
			self::RadioGroup->value    => 'Wybierz opcję',
			self::Checkbox->value      => 'Zaznacz aby potwierdzić',
			self::Select->value        => 'Wybierz opcję',
			self::Time->value          => 'GG:MM',
			self::DateTimeLocal->value => 'DD.MM.RRRR GG:MM',
			self::Month->value         => 'RRRR-MM',
			self::Week->value          => 'RRRR-T##',
			self::Textarea->value      => 'Wprowadź swoją wiadomość tutaj...',
			self::Range->value         => 'Dostosuj wartość',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'Wybierz plik',
			self::Password->value      => '••••••••',
			self::Search->value        => 'Szukaj...',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::Text->value          => 'Текст',
			self::Email->value         => 'Электронная почта',
			self::Tel->value           => 'Телефон',
			self::Number->value        => 'Число',
			self::Date->value          => 'Дата',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'Группа Радио',
			self::Checkbox->value      => 'Флажок',
			self::Select->value        => 'Выбрать',
			self::Time->value          => 'Время',
			self::DateTimeLocal->value => 'Дата & Время',
			self::Month->value         => 'Месяц',
			self::Week->value          => 'Неделя',
			self::Textarea->value      => 'Текстовое поле',
			self::Range->value         => 'Диапазон',
			self::Color->value         => 'Выбор цвета',
			self::File->value          => 'Файл',
			self::Password->value      => 'Пароль',
			self::Search->value        => 'Поиск',
		];
	}

	// Russian Placeholders
	public static function placeholdersRu(): array
	{
		return [
			self::Text->value          => 'Введите ваше имя',
			self::Email->value         => 'user@example.ru',
			self::Tel->value           => '+7 912 345-67-89',
			self::Number->value        => 'Введите количество',
			self::Date->value          => 'ДД.ММ.ГГГГ',
			self::Url->value           => 'https://example.ru',
			self::RadioGroup->value    => 'Выберите вариант',
			self::Checkbox->value      => 'Отметьте для подтверждения',
			self::Select->value        => 'Выберите вариант',
			self::Time->value          => 'ЧЧ:ММ',
			self::DateTimeLocal->value => 'ДД.ММ.ГГГГ ЧЧ:ММ',
			self::Month->value         => 'ГГГГ-ММ',
			self::Week->value          => 'ГГГГ-Н##',
			self::Textarea->value      => 'Введите ваше сообщение здесь...',
			self::Range->value         => 'Настройте значение',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'Выберите файл',
			self::Password->value      => '••••••••',
			self::Search->value        => 'Поиск...',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::Text->value          => 'Metin',
			self::Email->value         => 'E-posta',
			self::Tel->value           => 'Telefon',
			self::Number->value        => 'Sayı',
			self::Date->value          => 'Tarih',
			self::Url->value           => 'URL',
			self::RadioGroup->value    => 'Radyo Grubu',
			self::Checkbox->value      => 'Onay Kutusu',
			self::Select->value        => 'Seç',
			self::Time->value          => 'Saat',
			self::DateTimeLocal->value => 'Tarih & Saat',
			self::Month->value         => 'Ay',
			self::Week->value          => 'Hafta',
			self::Textarea->value      => 'Metin Alanı',
			self::Range->value         => 'Aralık',
			self::Color->value         => 'Renk Seçici',
			self::File->value          => 'Dosya',
			self::Password->value      => 'Şifre',
			self::Search->value        => 'Ara',
		];
	}

	// Turkish Placeholders
	public static function placeholdersTr(): array
	{
		return [
			self::Text->value          => 'Adınızı girin',
			self::Email->value         => 'kullanici@ornek.com.tr',
			self::Tel->value           => '+90 555 123 4567',
			self::Number->value        => 'Miktar girin',
			self::Date->value          => 'GG.AA.YYYY',
			self::Url->value           => 'https://ornek.com.tr',
			self::RadioGroup->value    => 'Bir seçenek seçin',
			self::Checkbox->value      => 'Onaylamak için işaretleyin',
			self::Select->value        => 'Bir seçenek seçin',
			self::Time->value          => 'SS:DD',
			self::DateTimeLocal->value => 'GG.AA.YYYY SS:DD',
			self::Month->value         => 'YYYY-AA',
			self::Week->value          => 'YYYY-H##',
			self::Textarea->value      => 'Mesajınızı buraya girin...',
			self::Range->value         => 'Değeri ayarlayın',
			self::Color->value         => '#FFFFFF',
			self::File->value          => 'Bir dosya seçin',
			self::Password->value      => '••••••••',
			self::Search->value        => 'Ara...',
		];
	}

	// Chinese (Simplified) Labels
	public static function labelsZh(): array
	{
		return [
			self::Text->value          => '文本',
			self::Email->value         => '电子邮件',
			self::Tel->value           => '电话',
			self::Number->value        => '数字',
			self::Date->value          => '日期',
			self::Url->value           => '网址',
			self::RadioGroup->value    => '单选组',
			self::Checkbox->value      => '复选框',
			self::Select->value        => '选择',
			self::Time->value          => '时间',
			self::DateTimeLocal->value => '日期 & 时间',
			self::Month->value         => '月份',
			self::Week->value          => '周',
			self::Textarea->value      => '文本区域',
			self::Range->value         => '范围',
			self::Color->value         => '颜色选择器',
			self::File->value          => '文件',
			self::Password->value      => '密码',
			self::Search->value        => '搜索',
		];
	}

	// Chinese (Simplified) Placeholders
	public static function placeholdersZh(): array
	{
		return [
			self::Text->value          => '输入您的姓名',
			self::Email->value         => 'user@example.cn',
			self::Tel->value           => '+86 138 0013 8000',
			self::Number->value        => '输入数量',
			self::Date->value          => 'YYYY年MM月DD日',
			self::Url->value           => 'https://example.cn',
			self::RadioGroup->value    => '选择一个选项',
			self::Checkbox->value      => '勾选以确认',
			self::Select->value        => '选择一个选项',
			self::Time->value          => 'HH:MM',
			self::DateTimeLocal->value => 'YYYY年MM月DD日 HH:MM',
			self::Month->value         => 'YYYY-MM',
			self::Week->value          => 'YYYY-W##',
			self::Textarea->value      => '在此输入您的消息...',
			self::Range->value         => '调整数值',
			self::Color->value         => '#FFFFFF',
			self::File->value          => '选择文件',
			self::Password->value      => '••••••••',
			self::Search->value        => '搜索...',
		];
	}
}
