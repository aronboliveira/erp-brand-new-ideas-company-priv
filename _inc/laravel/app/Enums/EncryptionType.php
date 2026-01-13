<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum EncryptionType: string
{
	// Encryption types
	case EnhancedEncryption = 'enhanced_encryption';
	case EndToEnd = 'e2e';
	case Standard = 'standard';
	case Transport = 'transport';
	case AtRest = 'at_rest';
	case FullDisk = 'full_disk';
	case FileLevel = 'file_level';
	case Database = 'database';
	case ApplicationLevel = 'application_level';
	case ZeroKnowledge = 'zero_knowledge';
	case Homomorphic = 'homomorphic';
	case QuantumSafe = 'quantum_safe';
	case MilitaryGrade = 'military_grade';
	case BankGrade = 'bank_grade';
	case AES256 = 'aes_256';
	case AES128 = 'aes_128';
	case RSA = 'rsa';
	case ECC = 'ecc';
	case PGP = 'pgp';
	case SSL = 'ssl';
	case TLS = 'tls';

	/**
	 * Normalize input to EncryptionType
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
			// Enhanced encryption
			'enhancedencryption', 'enhanced', 'highgrade', 'strongencryption',
			'advancedencryption' => self::EnhancedEncryption,

			// End-to-end
			'e2e', 'endtoend', 'endtoendencryption', 'e2ee',
			'clienttoclient', 'peertopeer' => self::EndToEnd,

			// Standard encryption
			'standard', 'basic', 'default', 'normal' => self::Standard,

			// Transport encryption
			'transport', 'transit', 'intransit', 'transfer' => self::Transport,

			// At rest encryption
			'atrest', 'stored', 'storage', 'dataatrest' => self::AtRest,

			// Full disk
			'fulldisk', 'diskencryption', 'wholedisk', 'fde' => self::FullDisk,

			// File level
			'filelevel', 'fileencryption', 'individualfiles' => self::FileLevel,

			// Database
			'database', 'dbencryption', 'databaselevel' => self::Database,

			// Application level
			'applicationlevel', 'applevel', 'appencryption' => self::ApplicationLevel,

			// Zero knowledge
			'zeroknowledge', 'zk', 'zeroknowledgeproof' => self::ZeroKnowledge,

			// Homomorphic
			'homomorphic', 'homomorphicencryption', 'he' => self::Homomorphic,

			// Quantum safe
			'quantumsafe', 'postquantum', 'quantumresistant' => self::QuantumSafe,

			// Military grade
			'militarygrade', 'military', 'govgrade', 'governmentgrade' => self::MilitaryGrade,

			// Bank grade
			'bankgrade', 'banking', 'financialgrade' => self::BankGrade,

			// AES algorithms
			'aes256', 'aes256bit', 'aes256encryption' => self::AES256,
			'aes128', 'aes128bit', 'aes128encryption' => self::AES128,

			// RSA
			'rsa', 'rsaencryption', 'rivestshamiradleman' => self::RSA,

			// ECC
			'ecc', 'ellipticcurve', 'eccencryption' => self::ECC,

			// PGP
			'pgp', 'gnupg', 'prettygoodprivacy' => self::PGP,

			// SSL
			'ssl', 'secure socketslayer', 'sslencription' => self::SSL,

			// TLS
			'tls', 'transportlayersecurity', 'tlsencryption' => self::TLS,

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
	 * Get label for this encryption type in specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? ucfirst(str_replace('_', ' ', $this->value));
	}

	/**
	 * Get color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			// High security - green
			self::EnhancedEncryption, self::MilitaryGrade, self::BankGrade,
			self::AES256, self::ZeroKnowledge => '#10b981',

			// End-to-end/transport - blue
			self::EndToEnd, self::Transport, self::SSL, self::TLS => '#3b82f6',

			// Standard/basic - gray
			self::Standard, self::AES128 => '#6b7280',

			// Storage/at rest - purple
			self::AtRest, self::FullDisk, self::FileLevel,
			self::Database, self::ApplicationLevel => '#8b5cf6',

			// Advanced/quantum - amber
			self::Homomorphic, self::QuantumSafe => '#f59e0b',

			// Algorithm specific - indigo
			self::RSA, self::ECC, self::PGP => '#6366f1',

			default => '#9ca3af',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::EnhancedEncryption => 'shield-check',
			self::EndToEnd => 'user-shield',
			self::Standard => 'shield',
			self::Transport => 'truck',
			self::AtRest => 'database',
			self::FullDisk => 'hdd',
			self::FileLevel => 'file-alt',
			self::Database => 'server',
			self::ApplicationLevel => 'code',
			self::ZeroKnowledge => 'user-secret',
			self::Homomorphic => 'atom',
			self::QuantumSafe => 'lightning',
			self::MilitaryGrade => 'medal',
			self::BankGrade => 'university',
			self::AES256 => 'key',
			self::AES128 => 'key',
			self::RSA => 'lock',
			self::ECC => 'project-diagram',
			self::PGP => 'envelope',
			self::SSL => 'globe',
			self::TLS => 'exchange-alt',
		};
	}

	/**
	 * Check if encryption provides end-to-end protection
	 */
	public function isEndToEnd(): bool
	{
		return $this === self::EndToEnd || $this === self::ZeroKnowledge;
	}

	/**
	 * Check if encryption is for data at rest
	 */
	public function isForDataAtRest(): bool
	{
		return in_array($this, [
			self::AtRest,
			self::FullDisk,
			self::FileLevel,
			self::Database,
			self::ApplicationLevel,
		]);
	}

	/**
	 * Check if encryption is for data in transit
	 */
	public function isForDataInTransit(): bool
	{
		return in_array($this, [
			self::Transport,
			self::SSL,
			self::TLS,
		]);
	}

	/**
	 * Get security level (1-5, where 5 is highest security)
	 */
	public function getSecurityLevel(): int
	{
		return match ($this) {
			self::Standard, self::AES128 => 2,
			self::Transport, self::SSL, self::AtRest => 3,
			self::EnhancedEncryption, self::EndToEnd, self::AES256,
			self::BankGrade, self::PGP => 4,
			self::MilitaryGrade, self::ZeroKnowledge,
			self::QuantumSafe, self::Homomorphic => 5,
			default => 3,
		};
	}

	/**
	 * Check if encryption is considered quantum-safe
	 */
	public function isQuantumSafe(): bool
	{
		return in_array($this, [
			self::QuantumSafe,
			self::ECC, // Elliptic-curve cryptography is more quantum-resistant than RSA
		]);
	}

	/**
	 * Get encryption category
	 */
	public function getCategory(): string
	{
		return match ($this) {
			// Algorithm categories
			self::AES256, self::AES128 => 'symmetric',
			self::RSA, self::ECC => 'asymmetric',
			self::PGP => 'hybrid',

			// Usage categories
			self::EndToEnd, self::ZeroKnowledge => 'end_to_end',
			self::Transport, self::SSL, self::TLS => 'transport',
			self::AtRest, self::FullDisk, self::FileLevel => 'storage',

			// Level categories
			self::Database, self::ApplicationLevel => 'application',
			self::Standard, self::EnhancedEncryption => 'general',

			// Special categories
			self::MilitaryGrade, self::BankGrade => 'certified',
			self::Homomorphic, self::QuantumSafe => 'advanced',

			default => 'other',
		};
	}

	/**
	 * Get description of the encryption type
	 */
	public function getDescription(): string
	{
		return match ($this) {
			self::EnhancedEncryption => 'High-grade encryption with additional security features',
			self::EndToEnd => 'Encryption where only communicating users can read messages',
			self::Standard => 'Basic encryption suitable for general use',
			self::Transport => 'Encryption for data in transit between systems',
			self::AtRest => 'Encryption for stored data',
			self::FullDisk => 'Encryption of entire storage device',
			self::FileLevel => 'Individual file encryption',
			self::Database => 'Encryption at database level',
			self::ApplicationLevel => 'Encryption implemented at application level',
			self::ZeroKnowledge => 'Encryption where server has zero knowledge of user data',
			self::Homomorphic => 'Encryption allowing computation on encrypted data',
			self::QuantumSafe => 'Encryption resistant to quantum computer attacks',
			self::MilitaryGrade => 'Encryption meeting military security standards',
			self::BankGrade => 'Encryption meeting financial industry security standards',
			self::AES256 => 'Advanced Encryption Standard with 256-bit key',
			self::AES128 => 'Advanced Encryption Standard with 128-bit key',
			self::RSA => 'Rivest-Shamir-Adleman asymmetric encryption',
			self::ECC => 'Elliptic-curve cryptography',
			self::PGP => 'Pretty Good Privacy encryption for emails and files',
			self::SSL => 'Secure Sockets Layer protocol',
			self::TLS => 'Transport Layer Security protocol',
		};
	}

	/**
	 * Check if encryption type is recommended for sensitive data
	 */
	public function isRecommendedForSensitiveData(): bool
	{
		return $this->getSecurityLevel() >= 4;
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::EnhancedEncryption->value => 'Enhanced Encryption',
			self::EndToEnd->value => 'End-to-End Encryption',
			self::Standard->value => 'Standard Encryption',
			self::Transport->value => 'Transport Encryption',
			self::AtRest->value => 'At-Rest Encryption',
			self::FullDisk->value => 'Full Disk Encryption',
			self::FileLevel->value => 'File-Level Encryption',
			self::Database->value => 'Database Encryption',
			self::ApplicationLevel->value => 'Application-Level Encryption',
			self::ZeroKnowledge->value => 'Zero-Knowledge Encryption',
			self::Homomorphic->value => 'Homomorphic Encryption',
			self::QuantumSafe->value => 'Quantum-Safe Encryption',
			self::MilitaryGrade->value => 'Military-Grade Encryption',
			self::BankGrade->value => 'Bank-Grade Encryption',
			self::AES256->value => 'AES-256 Encryption',
			self::AES128->value => 'AES-128 Encryption',
			self::RSA->value => 'RSA Encryption',
			self::ECC->value => 'Elliptic Curve Cryptography',
			self::PGP->value => 'PGP Encryption',
			self::SSL->value => 'SSL Encryption',
			self::TLS->value => 'TLS Encryption',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::EnhancedEncryption->value => 'Criptografia Avançada',
			self::EndToEnd->value => 'Criptografia de Ponta a Ponta',
			self::Standard->value => 'Criptografia Padrão',
			self::Transport->value => 'Criptografia de Transporte',
			self::AtRest->value => 'Criptografia em Repouso',
			self::FullDisk->value => 'Criptografia de Disco Completo',
			self::FileLevel->value => 'Criptografia em Nível de Arquivo',
			self::Database->value => 'Criptografia de Banco de Dados',
			self::ApplicationLevel->value => 'Criptografia em Nível de Aplicação',
			self::ZeroKnowledge->value => 'Criptografia de Conhecimento Zero',
			self::Homomorphic->value => 'Criptografia Homomórfica',
			self::QuantumSafe->value => 'Criptografia à Prova de Quântica',
			self::MilitaryGrade->value => 'Criptografia de Grau Militar',
			self::BankGrade->value => 'Criptografia de Grau Bancário',
			self::AES256->value => 'Criptografia AES-256',
			self::AES128->value => 'Criptografia AES-128',
			self::RSA->value => 'Criptografia RSA',
			self::ECC->value => 'Criptografia de Curva Elíptica',
			self::PGP->value => 'Criptografia PGP',
			self::SSL->value => 'Criptografia SSL',
			self::TLS->value => 'Criptografia TLS',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::EnhancedEncryption->value => 'Cifrado Mejorado',
			self::EndToEnd->value => 'Cifrado de Extremo a Extremo',
			self::Standard->value => 'Cifrado Estándar',
			self::Transport->value => 'Cifrado de Transporte',
			self::AtRest->value => 'Cifrado en Reposo',
			self::FullDisk->value => 'Cifrado de Disco Completo',
			self::FileLevel->value => 'Cifrado a Nivel de Archivo',
			self::Database->value => 'Cifrado de Base de Datos',
			self::ApplicationLevel->value => 'Cifrado a Nivel de Aplicación',
			self::ZeroKnowledge->value => 'Cifrado de Conocimiento Cero',
			self::Homomorphic->value => 'Cifrado Homomórfico',
			self::QuantumSafe->value => 'Cifrado a Prueba de Cuántica',
			self::MilitaryGrade->value => 'Cifrado de Grado Militar',
			self::BankGrade->value => 'Cifrado de Grado Bancario',
			self::AES256->value => 'Cifrado AES-256',
			self::AES128->value => 'Cifrado AES-128',
			self::RSA->value => 'Cifrado RSA',
			self::ECC->value => 'Criptografía de Curva Elíptica',
			self::PGP->value => 'Cifrado PGP',
			self::SSL->value => 'Cifrado SSL',
			self::TLS->value => 'Cifrado TLS',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::EnhancedEncryption->value => 'Chiffrement Amélioré',
			self::EndToEnd->value => 'Chiffrement de Bout en Bout',
			self::Standard->value => 'Chiffrement Standard',
			self::Transport->value => 'Chiffrement de Transport',
			self::AtRest->value => 'Chiffrement au Repos',
			self::FullDisk->value => 'Chiffrement de Disque Complet',
			self::FileLevel->value => 'Chiffrement au Niveau des Fichiers',
			self::Database->value => 'Chiffrement de Base de Données',
			self::ApplicationLevel->value => 'Chiffrement au Niveau de l\'Application',
			self::ZeroKnowledge->value => 'Chiffrement à Connaissance Zéro',
			self::Homomorphic->value => 'Chiffrement Homomorphe',
			self::QuantumSafe->value => 'Chiffrement Résistant aux Ordinateurs Quantiques',
			self::MilitaryGrade->value => 'Chiffrement de Grade Militaire',
			self::BankGrade->value => 'Chiffrement de Grade Bancaire',
			self::AES256->value => 'Chiffrement AES-256',
			self::AES128->value => 'Chiffrement AES-128',
			self::RSA->value => 'Chiffrement RSA',
			self::ECC->value => 'Cryptographie à Courbe Elliptique',
			self::PGP->value => 'Chiffrement PGP',
			self::SSL->value => 'Chiffrement SSL',
			self::TLS->value => 'Chiffrement TLS',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::EnhancedEncryption->value => 'Erweiterte Verschlüsselung',
			self::EndToEnd->value => 'Ende-zu-Ende-Verschlüsselung',
			self::Standard->value => 'Standard-Verschlüsselung',
			self::Transport->value => 'Transport-Verschlüsselung',
			self::AtRest->value => 'Ruhende Verschlüsselung',
			self::FullDisk->value => 'Vollständige Datenträgerverschlüsselung',
			self::FileLevel->value => 'Dateiebene-Verschlüsselung',
			self::Database->value => 'Datenbank-Verschlüsselung',
			self::ApplicationLevel->value => 'Anwendungsebene-Verschlüsselung',
			self::ZeroKnowledge->value => 'Zero-Knowledge-Verschlüsselung',
			self::Homomorphic->value => 'Homomorphe Verschlüsselung',
			self::QuantumSafe->value => 'Quantensichere Verschlüsselung',
			self::MilitaryGrade->value => 'Militärische Verschlüsselung',
			self::BankGrade->value => 'Banken-Verschlüsselung',
			self::AES256->value => 'AES-256-Verschlüsselung',
			self::AES128->value => 'AES-128-Verschlüsselung',
			self::RSA->value => 'RSA-Verschlüsselung',
			self::ECC->value => 'Elliptische-Kurven-Kryptographie',
			self::PGP->value => 'PGP-Verschlüsselung',
			self::SSL->value => 'SSL-Verschlüsselung',
			self::TLS->value => 'TLS-Verschlüsselung',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::EnhancedEncryption->value => 'Crittografia Avanzata',
			self::EndToEnd->value => 'Crittografia End-to-End',
			self::Standard->value => 'Crittografia Standard',
			self::Transport->value => 'Crittografia di Trasporto',
			self::AtRest->value => 'Crittografia a Riposo',
			self::FullDisk->value => 'Crittografia del Disco Completo',
			self::FileLevel->value => 'Crittografia a Livello di File',
			self::Database->value => 'Crittografia del Database',
			self::ApplicationLevel->value => 'Crittografia a Livello Applicativo',
			self::ZeroKnowledge->value => 'Crittografia a Conoscenza Zero',
			self::Homomorphic->value => 'Crittografia Omomorfa',
			self::QuantumSafe->value => 'Crittografia Quantum-Safe',
			self::MilitaryGrade->value => 'Crittografia di Grado Militare',
			self::BankGrade->value => 'Crittografia di Grado Bancario',
			self::AES256->value => 'Crittografia AES-256',
			self::AES128->value => 'Crittografia AES-128',
			self::RSA->value => 'Crittografia RSA',
			self::ECC->value => 'Crittografia a Curva Ellittica',
			self::PGP->value => 'Crittografia PGP',
			self::SSL->value => 'Crittografia SSL',
			self::TLS->value => 'Crittografia TLS',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::EnhancedEncryption->value => 'Verbeterde Versleuteling',
			self::EndToEnd->value => 'End-to-End Versleuteling',
			self::Standard->value => 'Standaard Versleuteling',
			self::Transport->value => 'Transport Versleuteling',
			self::AtRest->value => 'Versleuteling in Rust',
			self::FullDisk->value => 'Volledige Schijfversleuteling',
			self::FileLevel->value => 'Bestandsniveau Versleuteling',
			self::Database->value => 'Database Versleuteling',
			self::ApplicationLevel->value => 'Applicatieniveau Versleuteling',
			self::ZeroKnowledge->value => 'Zero-Knowledge Versleuteling',
			self::Homomorphic->value => 'Homomorfe Versleuteling',
			self::QuantumSafe->value => 'Quantum-Veilige Versleuteling',
			self::MilitaryGrade->value => 'Militaire Graad Versleuteling',
			self::BankGrade->value => 'Bank Graad Versleuteling',
			self::AES256->value => 'AES-256 Versleuteling',
			self::AES128->value => 'AES-128 Versleuteling',
			self::RSA->value => 'RSA Versleuteling',
			self::ECC->value => 'Elliptische Kromme Cryptografie',
			self::PGP->value => 'PGP Versleuteling',
			self::SSL->value => 'SSL Versleuteling',
			self::TLS->value => 'TLS Versleuteling',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::EnhancedEncryption->value => 'Zaawansowane Szyfrowanie',
			self::EndToEnd->value => 'Szyfrowanie End-to-End',
			self::Standard->value => 'Standardowe Szyfrowanie',
			self::Transport->value => 'Szyfrowanie Transportu',
			self::AtRest->value => 'Szyfrowanie w Spoczynku',
			self::FullDisk->value => 'Pełne Szyfrowanie Dyskowe',
			self::FileLevel->value => 'Szyfrowanie na Poziomie Plików',
			self::Database->value => 'Szyfrowanie Bazy Danych',
			self::ApplicationLevel->value => 'Szyfrowanie na Poziomie Aplikacji',
			self::ZeroKnowledge->value => 'Szyfrowanie Zero-Knowledge',
			self::Homomorphic->value => 'Szyfrowanie Homomorficzne',
			self::QuantumSafe->value => 'Szyfrowanie Odporne na Komputery Kwantowe',
			self::MilitaryGrade->value => 'Szyfrowanie Klasy Militarnej',
			self::BankGrade->value => 'Szyfrowanie Klasy Bankowej',
			self::AES256->value => 'Szyfrowanie AES-256',
			self::AES128->value => 'Szyfrowanie AES-128',
			self::RSA->value => 'Szyfrowanie RSA',
			self::ECC->value => 'Kryptografia Krzywej Eliptycznej',
			self::PGP->value => 'Szyfrowanie PGP',
			self::SSL->value => 'Szyfrowanie SSL',
			self::TLS->value => 'Szyfrowanie TLS',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::EnhancedEncryption->value => 'Улучшенное Шифрование',
			self::EndToEnd->value => 'Сквозное Шифрование',
			self::Standard->value => 'Стандартное Шифрование',
			self::Transport->value => 'Транспортное Шифрование',
			self::AtRest->value => 'Шифрование в Покое',
			self::FullDisk->value => 'Полное Шифрование Диска',
			self::FileLevel->value => 'Файловое Шифрование',
			self::Database->value => 'Шифрование Базы Данных',
			self::ApplicationLevel->value => 'Шифрование на Уровне Приложения',
			self::ZeroKnowledge->value => 'Шифрование Zero-Knowledge',
			self::Homomorphic->value => 'Гомоморфное Шифрование',
			self::QuantumSafe->value => 'Квантово-Устойчивое Шифрование',
			self::MilitaryGrade->value => 'Шифрование Военного Класса',
			self::BankGrade->value => 'Шифрование Банковского Класса',
			self::AES256->value => 'Шифрование AES-256',
			self::AES128->value => 'Шифрование AES-128',
			self::RSA->value => 'Шифрование RSA',
			self::ECC->value => 'Криптография на Эллиптических Кривых',
			self::PGP->value => 'Шифрование PGP',
			self::SSL->value => 'Шифрование SSL',
			self::TLS->value => 'Шифрование TLS',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::EnhancedEncryption->value => 'Gelişmiş Şifreleme',
			self::EndToEnd->value => 'Uçtan Uca Şifreleme',
			self::Standard->value => 'Standart Şifreleme',
			self::Transport->value => 'Taşıma Şifrelemesi',
			self::AtRest->value => 'Beklemede Şifreleme',
			self::FullDisk->value => 'Tam Disk Şifrelemesi',
			self::FileLevel->value => 'Dosya Düzeyinde Şifreleme',
			self::Database->value => 'Veritabanı Şifrelemesi',
			self::ApplicationLevel->value => 'Uygulama Düzeyinde Şifreleme',
			self::ZeroKnowledge->value => 'Zero-Knowledge Şifreleme',
			self::Homomorphic->value => 'Homomorfik Şifreleme',
			self::QuantumSafe->value => 'Kuantuma Dayanıklı Şifreleme',
			self::MilitaryGrade->value => 'Askeri Sınıf Şifreleme',
			self::BankGrade->value => 'Banka Sınıfı Şifreleme',
			self::AES256->value => 'AES-256 Şifreleme',
			self::AES128->value => 'AES-128 Şifreleme',
			self::RSA->value => 'RSA Şifreleme',
			self::ECC->value => 'Eliptik Eğri Kriptografisi',
			self::PGP->value => 'PGP Şifreleme',
			self::SSL->value => 'SSL Şifreleme',
			self::TLS->value => 'TLS Şifreleme',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::EnhancedEncryption->value => 'تشفير معزز',
			self::EndToEnd->value => 'تشفير من طرف لطرف',
			self::Standard->value => 'تشفير قياسي',
			self::Transport->value => 'تشفير النقل',
			self::AtRest->value => 'تشفير البيانات المخزنة',
			self::FullDisk->value => 'تشفير القرص الكامل',
			self::FileLevel->value => 'تشفير على مستوى الملف',
			self::Database->value => 'تشفير قاعدة البيانات',
			self::ApplicationLevel->value => 'تشفير على مستوى التطبيق',
			self::ZeroKnowledge->value => 'تشفير المعرفة الصفرية',
			self::Homomorphic->value => 'تشفير متجانس',
			self::QuantumSafe->value => 'تشفير مقاوم للكمبيوتر الكمي',
			self::MilitaryGrade->value => 'تشفير عسكري المستوى',
			self::BankGrade->value => 'تشفير مصرفي المستوى',
			self::AES256->value => 'تشفير AES-256',
			self::AES128->value => 'تشفير AES-128',
			self::RSA->value => 'تشفير RSA',
			self::ECC->value => 'تشفير منحنى بيضاوي',
			self::PGP->value => 'تشفير PGP',
			self::SSL->value => 'تشفير SSL',
			self::TLS->value => 'تشفير TLS',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::EnhancedEncryption->value => 'הצפנה מתקדמת',
			self::EndToEnd->value => 'הצפנה מקצה לקצה',
			self::Standard->value => 'הצפנה סטנדרטית',
			self::Transport->value => 'הצפנת תעבורה',
			self::AtRest->value => 'הצפנה במנוחה',
			self::FullDisk->value => 'הצפנת דיסק מלאה',
			self::FileLevel->value => 'הצפנה ברמת קובץ',
			self::Database->value => 'הצפנת מסד נתונים',
			self::ApplicationLevel->value => 'הצפנה ברמת אפליקציה',
			self::ZeroKnowledge->value => 'הצפנת ידע אפס',
			self::Homomorphic->value => 'הצפנה הומומורפית',
			self::QuantumSafe->value => 'הצפנה עמידה לקוונטים',
			self::MilitaryGrade->value => 'הצפנה בדרג צבאי',
			self::BankGrade->value => 'הצפנה בדרג בנקאי',
			self::AES256->value => 'הצפנת AES-256',
			self::AES128->value => 'הצפנת AES-128',
			self::RSA->value => 'הצפנת RSA',
			self::ECC->value => 'הצפנה בעקומה אליפטית',
			self::PGP->value => 'הצפנת PGP',
			self::SSL->value => 'הצפנת SSL',
			self::TLS->value => 'הצפנת TLS',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::EnhancedEncryption->value => '拡張暗号化',
			self::EndToEnd->value => 'エンドツーエンド暗号化',
			self::Standard->value => '標準暗号化',
			self::Transport->value => '転送暗号化',
			self::AtRest->value => '保管時暗号化',
			self::FullDisk->value => 'フルディスク暗号化',
			self::FileLevel->value => 'ファイルレベル暗号化',
			self::Database->value => 'データベース暗号化',
			self::ApplicationLevel->value => 'アプリケーションレベル暗号化',
			self::ZeroKnowledge->value => 'ゼロ知識暗号化',
			self::Homomorphic->value => '準同型暗号',
			self::QuantumSafe->value => '量子耐性暗号',
			self::MilitaryGrade->value => '軍事用暗号',
			self::BankGrade->value => '銀行用暗号',
			self::AES256->value => 'AES-256暗号',
			self::AES128->value => 'AES-128暗号',
			self::RSA->value => 'RSA暗号',
			self::ECC->value => '楕円曲線暗号',
			self::PGP->value => 'PGP暗号',
			self::SSL->value => 'SSL暗号',
			self::TLS->value => 'TLS暗号',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::EnhancedEncryption->value => 'Forbedret Kryptering',
			self::EndToEnd->value => 'End-to-End Kryptering',
			self::Standard->value => 'Standard Kryptering',
			self::Transport->value => 'Transport Kryptering',
			self::AtRest->value => 'Kryptering i Hvile',
			self::FullDisk->value => 'Fuld Disk Kryptering',
			self::FileLevel->value => 'Filniveau Kryptering',
			self::Database->value => 'Database Kryptering',
			self::ApplicationLevel->value => 'Applikationsniveau Kryptering',
			self::ZeroKnowledge->value => 'Zero-Knowledge Kryptering',
			self::Homomorphic->value => 'Homomorf Kryptering',
			self::QuantumSafe->value => 'Kvantesikker Kryptering',
			self::MilitaryGrade->value => 'Militær Kryptering',
			self::BankGrade->value => 'Bank Kryptering',
			self::AES256->value => 'AES-256 Kryptering',
			self::AES128->value => 'AES-128 Kryptering',
			self::RSA->value => 'RSA Kryptering',
			self::ECC->value => 'Elliptisk Kurve Kryptografi',
			self::PGP->value => 'PGP Kryptering',
			self::SSL->value => 'SSL Kryptering',
			self::TLS->value => 'TLS Kryptering',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			self::EnhancedEncryption->value => '增强加密',
			self::EndToEnd->value => '端到端加密',
			self::Standard->value => '标准加密',
			self::Transport->value => '传输加密',
			self::AtRest->value => '静态加密',
			self::FullDisk->value => '全盘加密',
			self::FileLevel->value => '文件级加密',
			self::Database->value => '数据库加密',
			self::ApplicationLevel->value => '应用级加密',
			self::ZeroKnowledge->value => '零知识加密',
			self::Homomorphic->value => '同态加密',
			self::QuantumSafe->value => '抗量子加密',
			self::MilitaryGrade->value => '军用级加密',
			self::BankGrade->value => '银行级加密',
			self::AES256->value => 'AES-256加密',
			self::AES128->value => 'AES-128加密',
			self::RSA->value => 'RSA加密',
			self::ECC->value => '椭圆曲线加密',
			self::PGP->value => 'PGP加密',
			self::SSL->value => 'SSL加密',
			self::TLS->value => 'TLS加密',
		];
	}
}
