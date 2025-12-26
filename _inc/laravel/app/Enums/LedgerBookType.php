<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum LedgerBookType: string
{
	case GeneralLedger = 'G';
	case Ledger = 'R';
	case AuxiliaryLedger = 'A';
	case TrialBalance = 'B';

	/**
	 * Normalize input to LedgerType
	 */
	public static function normalize(string|int|null|self $value = null): ?self
	{
		if ($value instanceof self) {
			return $value;
		}

		if ($value === null) {
			return null;
		}

		$normalizedValue = preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $value)));
		return match ($normalizedValue) {
			'g', 'generalledger', 'geral', 'general', 'livrodiariogeral', 'gl' => self::GeneralLedger,
			'r', 'ledger', 'razao', 'razão', 'livrorazao', 'lr' => self::Ledger,
			'a', 'auxiliaryledger', 'auxiliar', 'livroauxiliar', 'al' => self::AuxiliaryLedger,
			'b', 'trialbalance', 'balancete', 'balancetes', 'tb' => self::TrialBalance,
			default => null,
		};
	}

	/**
	 * Get labels in specified language
	 */
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

	/**
	 * Get label for this ledger type in specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? '';
	}

	/**
	 * Get descriptions in specified language
	 */
	public static function descriptions($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		return match ($lang) {
			'pt-br', 'pt' => self::descriptionsPtBr(),
			'es', 'es-es' => self::descriptionsEs(),
			'ar', 'ar-sa' => self::descriptionsAr(),
			'da', 'da-dk' => self::descriptionsDa(),
			'de', 'de-de' => self::descriptionsDe(),
			'fr', 'fr-fr' => self::descriptionsFr(),
			'he', 'he-il' => self::descriptionsHe(),
			'it', 'it-it' => self::descriptionsIt(),
			'ja', 'ja-jp' => self::descriptionsJa(),
			'nl', 'nl-nl' => self::descriptionsNl(),
			'pl', 'pl-pl' => self::descriptionsPl(),
			'ru', 'ru-ru' => self::descriptionsRu(),
			'tr', 'tr-tr' => self::descriptionsTr(),
			'zh', 'zh-cn' => self::descriptionsZh(),
			default => self::descriptionsEn(),
		};
	}

	/**
	 * Get description for this ledger type
	 */
	public function getDescription($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$descriptions = self::descriptions($lang);
		return $descriptions[$this->value] ?? '';
	}

	/**
	 * Get color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			self::GeneralLedger => '#3b82f6', // blue
			self::Ledger => '#10b981', // green
			self::AuxiliaryLedger => '#8b5cf6', // violet
			self::TrialBalance => '#f59e0b', // amber
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::GeneralLedger => 'book-open',
			self::Ledger => 'book',
			self::AuxiliaryLedger => 'clipboard-list',
			self::TrialBalance => 'scale',
		};
	}

	/**
	 * Get the order index for sorting
	 */
	public function getOrder(): int
	{
		return match ($this) {
			self::GeneralLedger => 1,
			self::Ledger => 2,
			self::AuxiliaryLedger => 3,
			self::TrialBalance => 4,
		};
	}

	/**
	 * Check if this is a main ledger type
	 */
	public function isMainLedger(): bool
	{
		return in_array($this, [self::GeneralLedger, self::Ledger]);
	}

	/**
	 * Check if this is a supporting ledger type
	 */
	public function isSupportingLedger(): bool
	{
		return in_array($this, [self::AuxiliaryLedger, self::TrialBalance]);
	}

	/**
	 * Get the abbreviation in Portuguese (original language)
	 */
	public function getPortugueseAbbreviation(): string
	{
		return match ($this) {
			self::GeneralLedger => 'G',
			self::Ledger => 'R',
			self::AuxiliaryLedger => 'A',
			self::TrialBalance => 'B',
		};
	}

	/**
	 * Convert to array for select dropdown
	 */
	public static function toSelectArray($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$labels = self::labels($lang);
		$result = [];

		foreach (self::cases() as $case) {
			$result[$case->value] = $labels[$case->value] ?? $case->name;
		}

		return $result;
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::GeneralLedger->value => 'General Ledger',
			self::Ledger->value => 'Ledger',
			self::AuxiliaryLedger->value => 'Auxiliary Ledger',
			self::TrialBalance->value => 'Trial Balance',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::GeneralLedger->value => 'Livro Diário Geral',
			self::Ledger->value => 'Livro Razão',
			self::AuxiliaryLedger->value => 'Livro Auxiliar',
			self::TrialBalance->value => 'Balancetes',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::GeneralLedger->value => 'Libro Diario General',
			self::Ledger->value => 'Libro Mayor',
			self::AuxiliaryLedger->value => 'Libro Auxiliar',
			self::TrialBalance->value => 'Balance de Comprobación',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::GeneralLedger->value => 'Hauptbuch',
			self::Ledger->value => 'Kontenbuch',
			self::AuxiliaryLedger->value => 'Nebenbuch',
			self::TrialBalance->value => 'Probebilanz',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::GeneralLedger->value => 'Grand Livre Général',
			self::Ledger->value => 'Livre de Comptes',
			self::AuxiliaryLedger->value => 'Livre Auxiliaire',
			self::TrialBalance->value => 'Balance de Vérification',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::GeneralLedger->value => 'Libro Giornale Generale',
			self::Ledger->value => 'Libro Mastro',
			self::AuxiliaryLedger->value => 'Libro Ausiliario',
			self::TrialBalance->value => 'Bilancio di Verifica',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::GeneralLedger->value => 'Grootboek',
			self::Ledger->value => 'Repertoire',
			self::AuxiliaryLedger->value => 'Hulpboek',
			self::TrialBalance->value => 'Proefbalans',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::GeneralLedger->value => 'Księga Główna',
			self::Ledger->value => 'Rejestr',
			self::AuxiliaryLedger->value => 'Księga Pomocnicza',
			self::TrialBalance->value => 'Bilans Próbny',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::GeneralLedger->value => 'Главная Книга',
			self::Ledger->value => 'Регистр',
			self::AuxiliaryLedger->value => 'Вспомогательная Книга',
			self::TrialBalance->value => 'Пробный Баланс',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::GeneralLedger->value => 'Genel Defter',
			self::Ledger->value => 'Defter',
			self::AuxiliaryLedger->value => 'Yardımcı Defter',
			self::TrialBalance->value => 'Deneme Bilançosu',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::GeneralLedger->value => 'دفتر الأستاذ العام',
			self::Ledger->value => 'دفتر الأستاذ',
			self::AuxiliaryLedger->value => 'دفتر مساعد',
			self::TrialBalance->value => 'ميزان المراجعة',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::GeneralLedger->value => 'ספר ראשי',
			self::Ledger->value => 'ספר',
			self::AuxiliaryLedger->value => 'ספר עזר',
			self::TrialBalance->value => 'מאזן בוחן',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::GeneralLedger->value => '総勘定元帳',
			self::Ledger->value => '元帳',
			self::AuxiliaryLedger->value => '補助元帳',
			self::TrialBalance->value => '試算表',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::GeneralLedger->value => 'Hovedbog',
			self::Ledger->value => 'Kontobog',
			self::AuxiliaryLedger->value => 'Hjælpebog',
			self::TrialBalance->value => 'Prøvebalance',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			self::GeneralLedger->value => '总分类账',
			self::Ledger->value => '分类账',
			self::AuxiliaryLedger->value => '辅助账',
			self::TrialBalance->value => '试算表',
		];
	}

	// English Descriptions
	public static function descriptionsEn(): array
	{
		return [
			self::GeneralLedger->value => 'Primary accounting record containing all financial transactions',
			self::Ledger->value => 'Record of individual accounts showing debits and credits',
			self::AuxiliaryLedger->value => 'Supplementary ledger for detailed tracking of specific accounts',
			self::TrialBalance->value => 'Statement of all ledger balances to verify arithmetic accuracy',
		];
	}

	// Portuguese Descriptions
	public static function descriptionsPtBr(): array
	{
		return [
			self::GeneralLedger->value => 'Registro contábil primário contendo todas as transações financeiras',
			self::Ledger->value => 'Registro de contas individuais mostrando débitos e créditos',
			self::AuxiliaryLedger->value => 'Livro auxiliar para rastreamento detalhado de contas específicas',
			self::TrialBalance->value => 'Demonstrativo de todos os saldos do razão para verificar a precisão aritmética',
		];
	}

	// Spanish Descriptions
	public static function descriptionsEs(): array
	{
		return [
			self::GeneralLedger->value => 'Registro contable principal que contiene todas las transacciones financieras',
			self::Ledger->value => 'Registro de cuentas individuales que muestra débitos y créditos',
			self::AuxiliaryLedger->value => 'Libro auxiliar para el seguimiento detallado de cuentas específicas',
			self::TrialBalance->value => 'Estado de todos los saldos del libro mayor para verificar la precisión aritmética',
		];
	}

	// German Descriptions
	public static function descriptionsDe(): array
	{
		return [
			self::GeneralLedger->value => 'Primäres Buchhaltungsbuch mit allen Finanztransaktionen',
			self::Ledger->value => 'Aufzeichnung einzelner Konten mit Soll- und Habenbuchungen',
			self::AuxiliaryLedger->value => 'Ergänzendes Buch für die detaillierte Verfolgung spezifischer Konten',
			self::TrialBalance->value => 'Aufstellung aller Kontensalden zur Überprüfung der rechnerischen Genauigkeit',
		];
	}

	// French Descriptions
	public static function descriptionsFr(): array
	{
		return [
			self::GeneralLedger->value => 'Registre comptable principal contenant toutes les transactions financières',
			self::Ledger->value => 'Registre des comptes individuels montrant les débits et les crédits',
			self::AuxiliaryLedger->value => 'Livre auxiliaire pour le suivi détaillé de comptes spécifiques',
			self::TrialBalance->value => 'État de tous les soldes du grand livre pour vérifier l\'exactitude arithmétique',
		];
	}

	// Italian Descriptions
	public static function descriptionsIt(): array
	{
		return [
			self::GeneralLedger->value => 'Registro contabile primario contenente tutte le transazioni finanziarie',
			self::Ledger->value => 'Registro di singoli conti che mostra debiti e crediti',
			self::AuxiliaryLedger->value => 'Libro ausiliario per il monitoraggio dettagliato di conti specifici',
			self::TrialBalance->value => 'Dichiarazione di tutti i saldi del libro mastro per verificare l\'accuratezza aritmetica',
		];
	}

	// Dutch Descriptions
	public static function descriptionsNl(): array
	{
		return [
			self::GeneralLedger->value => 'Primair boekhoudkundig register met alle financiële transacties',
			self::Ledger->value => 'Registratie van individuele rekeningen met debet en credit',
			self::AuxiliaryLedger->value => 'Aanvullend register voor gedetailleerde tracking van specifieke accounts',
			self::TrialBalance->value => 'Overzicht van alle grootboeksaldi om de rekenkundige nauwkeurigheid te verifiëren',
		];
	}

	// Polish Descriptions
	public static function descriptionsPl(): array
	{
		return [
			self::GeneralLedger->value => 'Podstawowy zapis księgowy zawierający wszystkie transakcje finansowe',
			self::Ledger->value => 'Rejestr poszczególnych kont pokazujący debety i kredyty',
			self::AuxiliaryLedger->value => 'Księga pomocnicza do szczegółowego śledzenia określonych kont',
			self::TrialBalance->value => 'Zestawienie wszystkich sald księgowych w celu sprawdzenia dokładności arytmetycznej',
		];
	}

	// Russian Descriptions
	public static function descriptionsRu(): array
	{
		return [
			self::GeneralLedger->value => 'Основная бухгалтерская запись, содержащая все финансовые операции',
			self::Ledger->value => 'Запись отдельных счетов с дебетом и кредитом',
			self::AuxiliaryLedger->value => 'Вспомогательная книга для детального отслеживания конкретных счетов',
			self::TrialBalance->value => 'Ведомость всех остатков по счетам для проверки арифметической точности',
		];
	}

	// Turkish Descriptions
	public static function descriptionsTr(): array
	{
		return [
			self::GeneralLedger->value => 'Tüm finansal işlemleri içeren birincil muhasebe kaydı',
			self::Ledger->value => 'Borç ve alacakları gösteren bireysel hesapların kaydı',
			self::AuxiliaryLedger->value => 'Belirli hesapların ayrıntılı takibi için yardımcı defter',
			self::TrialBalance->value => 'Aritmetik doğruluğu doğrulamak için tüm defter bakiyelerinin beyanı',
		];
	}

	// Arabic Descriptions
	public static function descriptionsAr(): array
	{
		return [
			self::GeneralLedger->value => 'سجل محاسبي أولي يحتوي على جميع المعاملات المالية',
			self::Ledger->value => 'سجل الحسابات الفردية يظهر المدين والدائن',
			self::AuxiliaryLedger->value => 'دفتر مساعد للتتبع التفصيلي لحسابات محددة',
			self::TrialBalance->value => 'بيان جميع أرصدة الدفتر للتحقق من الدقة الحسابية',
		];
	}

	// Hebrew Descriptions
	public static function descriptionsHe(): array
	{
		return [
			self::GeneralLedger->value => 'רישום חשבונאי ראשוני המכיל את כל העסקאות הפיננסיות',
			self::Ledger->value => 'רישום של חשבונות בודדים המציג חיובים וזיכויים',
			self::AuxiliaryLedger->value => 'ספר עזר למעקב מפורט אחר חשבונות ספציפיים',
			self::TrialBalance->value => 'דוח של כל יתרות הספר כדי לאמת דיוק אריתמטי',
		];
	}

	// Japanese Descriptions
	public static function descriptionsJa(): array
	{
		return [
			self::GeneralLedger->value => 'すべての財務取引を含む主要な会計記録',
			self::Ledger->value => '借方と貸方を示す個々の口座の記録',
			self::AuxiliaryLedger->value => '特定の口座の詳細な追跡のための補助元帳',
			self::TrialBalance->value => '計算精度を検証するためのすべての元帳残高の明細',
		];
	}

	// Danish Descriptions
	public static function descriptionsDa(): array
	{
		return [
			self::GeneralLedger->value => 'Primær bogføringsoptegnelse indeholdende alle finansielle transaktioner',
			self::Ledger->value => 'Optegnelse af individuelle konti, der viser debet og kredit',
			self::AuxiliaryLedger->value => 'Supplerende bog for detaljeret opfølgning af specifikke konti',
			self::TrialBalance->value => 'Opgørelse af alle hovedbogsaldi for at verificere aritmetisk nøjagtighed',
		];
	}

	// Chinese Descriptions
	public static function descriptionsZh(): array
	{
		return [
			self::GeneralLedger->value => '包含所有财务交易的主要会计记录',
			self::Ledger->value => '显示借方和贷方的各个账户记录',
			self::AuxiliaryLedger->value => '用于详细跟踪特定账户的辅助账簿',
			self::TrialBalance->value => '所有分类账余额的报表，用于验证算术准确性',
		];
	}
}
