<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum AttachmentModuleType: string
{
	// Legal/Contractual Modules
	case Contract = 'contract';
	case Agreement = 'agreement';
	case Addendum = 'addendum';
	case Amendment = 'amendment';
	case Schedule = 'schedule';
	case Annex = 'annex';
	case Exhibit = 'exhibit';
	case Appendix = 'appendix';

		// Financial Modules
	case Invoice = 'invoice';
	case Receipt = 'receipt';
	case Quote = 'quote';
	case Proposal = 'proposal';
	case PurchaseOrder = 'purchase_order';
	case PaymentConfirmation = 'payment_confirmation';
	case BankStatement = 'bank_statement';
	case FinancialStatement = 'financial_statement';
	case TaxDocument = 'tax_document';
	case AuditReport = 'audit_report';

		// Legal Proceedings
	case LegalCase = 'legal_case';
	case CourtDocument = 'court_document';
	case Pleading = 'pleading';
	case Motion = 'motion';
	case Deposition = 'deposition';
	case Affidavit = 'affidavit';
	case Subpoena = 'subpoena';
	case Summons = 'summons';
	case Judgment = 'judgment';
	case Settlement = 'settlement';

		// Corporate/Compliance
	case CorporateResolution = 'corporate_resolution';
	case Bylaws = 'bylaws';
	case ArticlesOfIncorporation = 'articles_of_incorporation';
	case ShareholderAgreement = 'shareholder_agreement';
	case ComplianceDocument = 'compliance_document';
	case RegulatoryFiling = 'regulatory_filing';
	case License = 'license';
	case Permit = 'permit';
	case Certificate = 'certificate';

		// Property/Real Estate
	case Deed = 'deed';
	case Title = 'title';
	case Lease = 'lease';
	case Mortgage = 'mortgage';
	case Survey = 'survey';
	case Appraisal = 'appraisal';
	case InsurancePolicy = 'insurance_policy';
	case PropertyDocument = 'property_document';

		// Identification/Personal
	case Identification = 'identification';
	case Passport = 'passport';
	case DriverLicense = 'driver_license';
	case BirthCertificate = 'birth_certificate';
	case MarriageCertificate = 'marriage_certificate';
	case PowerOfAttorney = 'power_of_attorney';
	case Will = 'will';
	case TrustDocument = 'trust_document';

		// Communication/Correspondence
	case Correspondence = 'correspondence';
	case Email = 'email';
	case Letter = 'letter';
	case Memo = 'memo';
	case Notice = 'notice';
	case Disclosure = 'disclosure';
	case Waiver = 'waiver';
	case ConsentForm = 'consent_form';

		// Intellectual Property
	case Patent = 'patent';
	case Trademark = 'trademark';
	case Copyright = 'copyright';
	case NDA = 'nda';
	case ConfidentialityAgreement = 'confidentiality_agreement';
	case NonCompete = 'non_compete';
	case IPAssignment = 'ip_assignment';

		// Reports/Records
	case MeetingMinutes = 'meeting_minutes';
	case Report = 'report';
	case Record = 'record';
	case Log = 'log';
	case Transcript = 'transcript';
	case Verification = 'verification';
	case Certification = 'certification';

		// Proof/Evidence
	case ProofOfPayment = 'proof_of_payment';
	case ProofOfAddress = 'proof_of_address';
	case ProofOfIdentity = 'proof_of_identity';
	case ProofOfInsurance = 'proof_of_insurance';
	case ProofOfEmployment = 'proof_of_employment';
	case Evidence = 'evidence';

		// Other
	case Other = 'other';
	case Miscellaneous = 'miscellaneous';
	case Reference = 'reference';
	case SupportingDocument = 'supporting_document';
	case Backup = 'backup';
	case Draft = 'draft';
	case Template = 'template';

	/**
	 * Normalize input to AttachmentModuleType
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
			// Contract/Agreement related
			'contract', 'contractual', 'legalcontract' => self::Contract,
			'agreement', 'mutualagreement' => self::Agreement,
			'addendum', 'addenda', 'supplement' => self::Addendum,
			'amendment', 'modification', 'changeorder' => self::Amendment,
			'schedule', 'attachment', 'attachmenta' => self::Schedule,
			'annex', 'annexure' => self::Annex,
			'exhibit', 'exhibit a', 'attachmentb' => self::Exhibit,
			'appendix', 'appendices' => self::Appendix,

			// Financial documents
			'invoice', 'bill', 'statement' => self::Invoice,
			'receipt', 'paymentreceipt', 'acknowledgment' => self::Receipt,
			'quote', 'quotation', 'estimate' => self::Quote,
			'proposal', 'offer', 'bid' => self::Proposal,
			'purchaseorder', 'po', 'orderform' => self::PurchaseOrder,
			'paymentconfirmation', 'paymentreceipt', 'paymentproof' => self::PaymentConfirmation,
			'bankstatement', 'accountstatement' => self::BankStatement,
			'financialstatement', 'financialreport', 'balancesheet' => self::FinancialStatement,
			'taxdocument', 'taxreturn', 'taxform' => self::TaxDocument,
			'auditreport', 'audit', 'auditorreport' => self::AuditReport,

			// Legal proceedings
			'legalcase', 'casefile', 'lawsuit' => self::LegalCase,
			'courtdocument', 'courtfiling', 'courtpaper' => self::CourtDocument,
			'pleading', 'complaint', 'answer' => self::Pleading,
			'motion', 'request', 'application' => self::Motion,
			'deposition', 'testimony', 'statement' => self::Deposition,
			'affidavit', 'swornstatement', 'declaration' => self::Affidavit,
			'subpoena', 'subpoenaduces tecum' => self::Subpoena,
			'summons', 'citation', 'writ' => self::Summons,
			'judgment', 'verdict', 'ruling' => self::Judgment,
			'settlement', 'settlementagreement' => self::Settlement,

			// Corporate/Compliance
			'corporateresolution', 'boardresolution' => self::CorporateResolution,
			'bylaws', 'bylaw', 'rules' => self::Bylaws,
			'articlesofincorporation', 'certificateofincorporation' => self::ArticlesOfIncorporation,
			'shareholderagreement', 'stockholderagreement' => self::ShareholderAgreement,
			'compliancedocument', 'compliance', 'regulatorycompliance' => self::ComplianceDocument,
			'regulatoryfiling', 'filing', 'secfiling' => self::RegulatoryFiling,
			'license', 'licensing', 'permission' => self::License,
			'permit', 'authorization', 'clearance' => self::Permit,
			'certificate', 'cert', 'credential' => self::Certificate,

			// Property/Real Estate
			'deed', 'propertydeed', 'warrantydeed' => self::Deed,
			'title', 'titledocument', 'propertytitle' => self::Title,
			'lease', 'rentalagreement', 'tenancyagreement' => self::Lease,
			'mortgage', 'loanagreement', 'securedloan' => self::Mortgage,
			'survey', 'landsurvey', 'property survey' => self::Survey,
			'appraisal', 'valuation', 'propertyvaluation' => self::Appraisal,
			'insurancepolicy', 'policy', 'insurance' => self::InsurancePolicy,
			'propertydocument', 'realestatedocument' => self::PropertyDocument,

			// Identification/Personal
			'identification', 'id', 'idcard' => self::Identification,
			'passport', 'passportdocument' => self::Passport,
			'driverlicense', 'driverslicense', 'dl' => self::DriverLicense,
			'birthcertificate', 'birthcert' => self::BirthCertificate,
			'marriagecertificate', 'marriagecert', 'weddingcertificate' => self::MarriageCertificate,
			'powerofattorney', 'poa', 'attorneyinfact' => self::PowerOfAttorney,
			'will', 'lastwill', 'testament' => self::Will,
			'trustdocument', 'trust', 'trustagreement' => self::TrustDocument,

			// Communication/Correspondence
			'correspondence', 'communication', 'letters' => self::Correspondence,
			'email', 'emails', 'electronicmail' => self::Email,
			'letter', 'formalletter', 'businessletter' => self::Letter,
			'memo', 'memorandum', 'internalmemo' => self::Memo,
			'notice', 'notification', 'formalnotice' => self::Notice,
			'disclosure', 'disclosuredocument' => self::Disclosure,
			'waiver', 'release', 'liabilitywaiver' => self::Waiver,
			'consentform', 'consent', 'authorizationform' => self::ConsentForm,

			// Intellectual Property
			'patent', 'patentapplication' => self::Patent,
			'trademark', 'tm', 'servicemark' => self::Trademark,
			'copyright', 'copyrightregistration' => self::Copyright,
			'nda', 'nondisclosure', 'nondisclosureagreement' => self::NDA,
			'confidentialityagreement', 'confidentiality' => self::ConfidentialityAgreement,
			'noncompete', 'noncompetition', 'covenantnotcompete' => self::NonCompete,
			'ipassignment', 'intellectualpropertyassignment' => self::IPAssignment,

			// Reports/Records
			'meetingminutes', 'minutes', 'meetingnotes' => self::MeetingMinutes,
			'report', 'documentation', 'findings' => self::Report,
			'record', 'document', 'file' => self::Record,
			'log', 'logfile', 'activitylog' => self::Log,
			'transcript', 'transcription', 'hearingtranscript' => self::Transcript,
			'verification', 'verificationdocument' => self::Verification,
			'certification', 'certifieddocument' => self::Certification,

			// Proof/Evidence
			'proofofpayment', 'paymentproof', 'paymentevidence' => self::ProofOfPayment,
			'proofofaddress', 'addressproof', 'residencyproof' => self::ProofOfAddress,
			'proofofidentity', 'identityproof', 'idproof' => self::ProofOfIdentity,
			'proofofinsurance', 'insuranceproof', 'coverageproof' => self::ProofOfInsurance,
			'proofofemployment', 'employmentproof', 'jobproof' => self::ProofOfEmployment,
			'evidence', 'proof', 'documentaryevidence' => self::Evidence,

			// Other
			'other', 'misc', 'general' => self::Other,
			'miscellaneous', 'various', 'assorted' => self::Miscellaneous,
			'reference', 'referencematerial', 'source' => self::Reference,
			'supportingdocument', 'support', 'backupdocument' => self::SupportingDocument,
			'backup', 'backupfile', 'duplicate' => self::Backup,
			'draft', 'roughdraft', 'preliminary' => self::Draft,
			'template', 'form', 'boilerplate' => self::Template,

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
	 * Get label for this module type in specified language
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
			// Legal/Contractual - dark blue
			self::Contract, self::Agreement, self::Addendum,
			self::Amendment, self::Schedule, self::Annex,
			self::Exhibit, self::Appendix => '#1e40af',

			// Financial - green
			self::Invoice, self::Receipt, self::Quote,
			self::Proposal, self::PurchaseOrder, self::PaymentConfirmation,
			self::BankStatement, self::FinancialStatement,
			self::TaxDocument, self::AuditReport => '#10b981',

			// Legal Proceedings - red
			self::LegalCase, self::CourtDocument, self::Pleading,
			self::Motion, self::Deposition, self::Affidavit,
			self::Subpoena, self::Summons, self::Judgment,
			self::Settlement => '#dc2626',

			// Corporate/Compliance - purple
			self::CorporateResolution, self::Bylaws,
			self::ArticlesOfIncorporation, self::ShareholderAgreement,
			self::ComplianceDocument, self::RegulatoryFiling,
			self::License, self::Permit, self::Certificate => '#8b5cf6',

			// Property/Real Estate - amber
			self::Deed, self::Title, self::Lease, self::Mortgage,
			self::Survey, self::Appraisal, self::InsurancePolicy,
			self::PropertyDocument => '#f59e0b',

			// Identification/Personal - indigo
			self::Identification, self::Passport, self::DriverLicense,
			self::BirthCertificate, self::MarriageCertificate,
			self::PowerOfAttorney, self::Will, self::TrustDocument => '#6366f1',

			// Communication - teal
			self::Correspondence, self::Email, self::Letter,
			self::Memo, self::Notice, self::Disclosure,
			self::Waiver, self::ConsentForm => '#14b8a6',

			// Intellectual Property - pink
			self::Patent, self::Trademark, self::Copyright,
			self::NDA, self::ConfidentialityAgreement,
			self::NonCompete, self::IPAssignment => '#ec4899',

			// Reports/Records - gray
			self::MeetingMinutes, self::Report, self::Record,
			self::Log, self::Transcript, self::Verification,
			self::Certification => '#6b7280',

			// Proof/Evidence - orange
			self::ProofOfPayment, self::ProofOfAddress,
			self::ProofOfIdentity, self::ProofOfInsurance,
			self::ProofOfEmployment, self::Evidence => '#ea580c',

			// Other - light gray
			self::Other, self::Miscellaneous, self::Reference,
			self::SupportingDocument, self::Backup,
			self::Draft, self::Template => '#9ca3af',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			// Legal/Contractual
			self::Contract, self::Agreement => 'file-contract',
			self::Addendum, self::Amendment => 'file-edit',
			self::Schedule, self::Annex => 'file-import',
			self::Exhibit, self::Appendix => 'file-export',

			// Financial
			self::Invoice, self::Receipt => 'file-invoice-dollar',
			self::Quote, self::Proposal => 'file-signature',
			self::PurchaseOrder => 'shopping-cart',
			self::PaymentConfirmation => 'credit-card',
			self::BankStatement => 'university',
			self::FinancialStatement => 'chart-line',
			self::TaxDocument => 'receipt',
			self::AuditReport => 'search-dollar',

			// Legal Proceedings
			self::LegalCase, self::CourtDocument => 'gavel',
			self::Pleading, self::Motion => 'balance-scale',
			self::Deposition => 'user-tie',
			self::Affidavit => 'signature',
			self::Subpoena, self::Summons => 'envelope-open-text',
			self::Judgment, self::Settlement => 'scale-balanced',

			// Corporate/Compliance
			self::CorporateResolution => 'building',
			self::Bylaws => 'book',
			self::ArticlesOfIncorporation => 'file-certificate',
			self::ShareholderAgreement => 'handshake',
			self::ComplianceDocument => 'shield-check',
			self::RegulatoryFiling => 'clipboard-check',
			self::License, self::Permit => 'id-card',
			self::Certificate => 'award',

			// Property/Real Estate
			self::Deed, self::Title => 'home',
			self::Lease => 'key',
			self::Mortgage => 'hand-holding-usd',
			self::Survey => 'map',
			self::Appraisal => 'calculator',
			self::InsurancePolicy => 'umbrella',
			self::PropertyDocument => 'landmark',

			// Identification/Personal
			self::Identification, self::Passport => 'id-badge',
			self::DriverLicense => 'car',
			self::BirthCertificate => 'baby',
			self::MarriageCertificate => 'ring',
			self::PowerOfAttorney => 'user-shield',
			self::Will => 'scroll',
			self::TrustDocument => 'users',

			// Communication/Correspondence
			self::Correspondence, self::Email => 'envelope',
			self::Letter, self::Memo => 'envelope-open',
			self::Notice => 'bullhorn',
			self::Disclosure => 'eye',
			self::Waiver => 'hand-paper',
			self::ConsentForm => 'check-circle',

			// Intellectual Property
			self::Patent => 'lightbulb',
			self::Trademark => 'registered',
			self::Copyright => 'copyright',
			self::NDA, self::ConfidentialityAgreement => 'user-secret',
			self::NonCompete => 'ban',
			self::IPAssignment => 'exchange-alt',

			// Reports/Records
			self::MeetingMinutes => 'users',
			self::Report => 'chart-bar',
			self::Record, self::Log => 'clipboard-list',
			self::Transcript => 'microphone',
			self::Verification => 'check-double',
			self::Certification => 'stamp',

			// Proof/Evidence
			self::ProofOfPayment => 'money-check',
			self::ProofOfAddress => 'map-marker-alt',
			self::ProofOfIdentity => 'fingerprint',
			self::ProofOfInsurance => 'shield-alt',
			self::ProofOfEmployment => 'briefcase',
			self::Evidence => 'camera',

			// Other
			self::Other, self::Miscellaneous => 'file-alt',
			self::Reference => 'bookmark',
			self::SupportingDocument => 'paperclip',
			self::Backup => 'save',
			self::Draft => 'edit',
			self::Template => 'clone',
		};
	}

	/**
	 * Check if this is a legal/contractual document
	 */
	public function isLegalDocument(): bool
	{
		return in_array($this, [
			self::Contract,
			self::Agreement,
			self::Addendum,
			self::Amendment,
			self::Schedule,
			self::Annex,
			self::Exhibit,
			self::Appendix,
			self::LegalCase,
			self::CourtDocument,
			self::Pleading,
			self::Motion,
			self::Deposition,
			self::Affidavit,
			self::Subpoena,
			self::Summons,
			self::Judgment,
			self::Settlement,
			self::CorporateResolution,
			self::Bylaws,
			self::ArticlesOfIncorporation,
			self::ShareholderAgreement,
			self::ComplianceDocument,
			self::RegulatoryFiling,
			self::License,
			self::Permit,
			self::Certificate,
			self::Deed,
			self::Title,
			self::Lease,
			self::Mortgage,
			self::PowerOfAttorney,
			self::Will,
			self::TrustDocument,
			self::Waiver,
			self::ConsentForm,
			self::Patent,
			self::Trademark,
			self::Copyright,
			self::NDA,
			self::ConfidentialityAgreement,
			self::NonCompete,
			self::IPAssignment,
		]);
	}

	/**
	 * Check if this is a financial document
	 */
	public function isFinancialDocument(): bool
	{
		return in_array($this, [
			self::Invoice,
			self::Receipt,
			self::Quote,
			self::Proposal,
			self::PurchaseOrder,
			self::PaymentConfirmation,
			self::BankStatement,
			self::FinancialStatement,
			self::TaxDocument,
			self::AuditReport,
			self::Mortgage,
			self::Appraisal,
			self::InsurancePolicy,
			self::ProofOfPayment,
			self::ProofOfInsurance,
		]);
	}

	/**
	 * Check if this is an identification document
	 */
	public function isIdentificationDocument(): bool
	{
		return in_array($this, [
			self::Identification,
			self::Passport,
			self::DriverLicense,
			self::BirthCertificate,
			self::MarriageCertificate,
			self::ProofOfIdentity,
			self::ProofOfAddress,
			self::ProofOfEmployment,
		]);
	}

	/**
	 * Check if this is a corporate document
	 */
	public function isCorporateDocument(): bool
	{
		return in_array($this, [
			self::CorporateResolution,
			self::Bylaws,
			self::ArticlesOfIncorporation,
			self::ShareholderAgreement,
			self::ComplianceDocument,
			self::RegulatoryFiling,
			self::License,
			self::Permit,
			self::Certificate,
			self::MeetingMinutes,
			self::Report,
		]);
	}

	/**
	 * Check if this is a property/real estate document
	 */
	public function isPropertyDocument(): bool
	{
		return in_array($this, [
			self::Deed,
			self::Title,
			self::Lease,
			self::Mortgage,
			self::Survey,
			self::Appraisal,
			self::InsurancePolicy,
			self::PropertyDocument,
		]);
	}

	/**
	 * Check if this document requires notarization
	 */
	public function requiresNotarization(): bool
	{
		return in_array($this, [
			self::Affidavit,
			self::PowerOfAttorney,
			self::Will,
			self::Deed,
			self::Mortgage,
			self::ArticlesOfIncorporation,
			self::CorporateResolution,
		]);
	}

	/**
	 * Check if this document is confidential
	 */
	public function isConfidential(): bool
	{
		return in_array($this, [
			self::NDA,
			self::ConfidentialityAgreement,
			self::LegalCase,
			self::CourtDocument,
			self::Settlement,
			self::FinancialStatement,
			self::AuditReport,
			self::Identification,
			self::Passport,
			self::DriverLicense,
			self::BirthCertificate,
			self::MarriageCertificate,
			self::BankStatement,
			self::TaxDocument,
		]);
	}

	/**
	 * Get document category
	 */
	public function getCategory(): string
	{
		return match ($this) {
			// Legal
			self::Contract, self::Agreement, self::Addendum,
			self::Amendment, self::Schedule, self::Annex,
			self::Exhibit, self::Appendix => 'contract',

			// Financial
			self::Invoice, self::Receipt, self::Quote,
			self::Proposal, self::PurchaseOrder, self::PaymentConfirmation,
			self::BankStatement, self::FinancialStatement,
			self::TaxDocument, self::AuditReport => 'financial',

			// Legal Proceedings
			self::LegalCase, self::CourtDocument, self::Pleading,
			self::Motion, self::Deposition, self::Affidavit,
			self::Subpoena, self::Summons, self::Judgment,
			self::Settlement => 'legal_proceedings',

			// Corporate
			self::CorporateResolution, self::Bylaws,
			self::ArticlesOfIncorporation, self::ShareholderAgreement,
			self::ComplianceDocument, self::RegulatoryFiling => 'corporate',

			// Permits/Licenses
			self::License, self::Permit, self::Certificate => 'authorization',

			// Property
			self::Deed, self::Title, self::Lease, self::Mortgage,
			self::Survey, self::Appraisal, self::InsurancePolicy,
			self::PropertyDocument => 'property',

			// Identification
			self::Identification, self::Passport, self::DriverLicense,
			self::BirthCertificate, self::MarriageCertificate,
			self::ProofOfIdentity, self::ProofOfAddress,
			self::ProofOfEmployment => 'identification',

			// Personal/Legal
			self::PowerOfAttorney, self::Will, self::TrustDocument => 'personal_legal',

			// Communication
			self::Correspondence, self::Email, self::Letter,
			self::Memo, self::Notice, self::Disclosure => 'communication',

			// Forms/Waivers
			self::Waiver, self::ConsentForm => 'forms',

			// Intellectual Property
			self::Patent, self::Trademark, self::Copyright,
			self::NDA, self::ConfidentialityAgreement,
			self::NonCompete, self::IPAssignment => 'intellectual_property',

			// Records/Reports
			self::MeetingMinutes, self::Report, self::Record,
			self::Log, self::Transcript, self::Verification,
			self::Certification => 'records',

			// Proof/Evidence
			self::ProofOfPayment, self::ProofOfInsurance,
			self::Evidence => 'proof',

			// Other
			self::Other, self::Miscellaneous, self::Reference,
			self::SupportingDocument, self::Backup,
			self::Draft, self::Template => 'other',
		};
	}

	/**
	 * Get retention period in years (for compliance purposes)
	 */
	public function getRetentionPeriod(): int
	{
		return match ($this) {
			// Permanent retention
			self::ArticlesOfIncorporation, self::Bylaws,
			self::Deed, self::Title, self::Will,
			self::BirthCertificate, self::MarriageCertificate,
			self::Patent, self::Trademark, self::Copyright => 99,

			// 10+ years retention
			self::Contract, self::Agreement, self::LegalCase,
			self::CourtDocument, self::Judgment, self::Settlement,
			self::FinancialStatement, self::TaxDocument,
			self::AuditReport, self::Mortgage, self::Lease => 10,

			// 7 years retention (standard business)
			self::Invoice, self::Receipt, self::BankStatement,
			self::PurchaseOrder, self::PaymentConfirmation,
			self::ShareholderAgreement, self::CorporateResolution,
			self::RegulatoryFiling, self::ComplianceDocument => 7,

			// 3-5 years retention
			self::Quote, self::Proposal, self::Correspondence,
			self::Email, self::Letter, self::Memo,
			self::MeetingMinutes, self::Report => 5,

			// 1-2 years retention
			self::ProofOfPayment, self::ProofOfAddress,
			self::ProofOfIdentity, self::ProofOfInsurance,
			self::ProofOfEmployment, self::Evidence => 2,

			// Temporary/Short term
			self::Draft, self::Template, self::Backup => 1,

			// Default
			default => 7,
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'Contract',
			self::Agreement->value => 'Agreement',
			self::Addendum->value => 'Addendum',
			self::Amendment->value => 'Amendment',
			self::Schedule->value => 'Schedule',
			self::Annex->value => 'Annex',
			self::Exhibit->value => 'Exhibit',
			self::Appendix->value => 'Appendix',

			// Financial
			self::Invoice->value => 'Invoice',
			self::Receipt->value => 'Receipt',
			self::Quote->value => 'Quote',
			self::Proposal->value => 'Proposal',
			self::PurchaseOrder->value => 'Purchase Order',
			self::PaymentConfirmation->value => 'Payment Confirmation',
			self::BankStatement->value => 'Bank Statement',
			self::FinancialStatement->value => 'Financial Statement',
			self::TaxDocument->value => 'Tax Document',
			self::AuditReport->value => 'Audit Report',

			// Legal Proceedings
			self::LegalCase->value => 'Legal Case',
			self::CourtDocument->value => 'Court Document',
			self::Pleading->value => 'Pleading',
			self::Motion->value => 'Motion',
			self::Deposition->value => 'Deposition',
			self::Affidavit->value => 'Affidavit',
			self::Subpoena->value => 'Subpoena',
			self::Summons->value => 'Summons',
			self::Judgment->value => 'Judgment',
			self::Settlement->value => 'Settlement',

			// Corporate/Compliance
			self::CorporateResolution->value => 'Corporate Resolution',
			self::Bylaws->value => 'Bylaws',
			self::ArticlesOfIncorporation->value => 'Articles of Incorporation',
			self::ShareholderAgreement->value => 'Shareholder Agreement',
			self::ComplianceDocument->value => 'Compliance Document',
			self::RegulatoryFiling->value => 'Regulatory Filing',
			self::License->value => 'License',
			self::Permit->value => 'Permit',
			self::Certificate->value => 'Certificate',

			// Property/Real Estate
			self::Deed->value => 'Deed',
			self::Title->value => 'Title',
			self::Lease->value => 'Lease',
			self::Mortgage->value => 'Mortgage',
			self::Survey->value => 'Survey',
			self::Appraisal->value => 'Appraisal',
			self::InsurancePolicy->value => 'Insurance Policy',
			self::PropertyDocument->value => 'Property Document',

			// Identification/Personal
			self::Identification->value => 'Identification',
			self::Passport->value => 'Passport',
			self::DriverLicense->value => 'Driver License',
			self::BirthCertificate->value => 'Birth Certificate',
			self::MarriageCertificate->value => 'Marriage Certificate',
			self::PowerOfAttorney->value => 'Power of Attorney',
			self::Will->value => 'Will',
			self::TrustDocument->value => 'Trust Document',

			// Communication/Correspondence
			self::Correspondence->value => 'Correspondence',
			self::Email->value => 'Email',
			self::Letter->value => 'Letter',
			self::Memo->value => 'Memo',
			self::Notice->value => 'Notice',
			self::Disclosure->value => 'Disclosure',
			self::Waiver->value => 'Waiver',
			self::ConsentForm->value => 'Consent Form',

			// Intellectual Property
			self::Patent->value => 'Patent',
			self::Trademark->value => 'Trademark',
			self::Copyright->value => 'Copyright',
			self::NDA->value => 'NDA',
			self::ConfidentialityAgreement->value => 'Confidentiality Agreement',
			self::NonCompete->value => 'Non-Compete Agreement',
			self::IPAssignment->value => 'IP Assignment',

			// Reports/Records
			self::MeetingMinutes->value => 'Meeting Minutes',
			self::Report->value => 'Report',
			self::Record->value => 'Record',
			self::Log->value => 'Log',
			self::Transcript->value => 'Transcript',
			self::Verification->value => 'Verification',
			self::Certification->value => 'Certification',

			// Proof/Evidence
			self::ProofOfPayment->value => 'Proof of Payment',
			self::ProofOfAddress->value => 'Proof of Address',
			self::ProofOfIdentity->value => 'Proof of Identity',
			self::ProofOfInsurance->value => 'Proof of Insurance',
			self::ProofOfEmployment->value => 'Proof of Employment',
			self::Evidence->value => 'Evidence',

			// Other
			self::Other->value => 'Other',
			self::Miscellaneous->value => 'Miscellaneous',
			self::Reference->value => 'Reference',
			self::SupportingDocument->value => 'Supporting Document',
			self::Backup->value => 'Backup',
			self::Draft->value => 'Draft',
			self::Template->value => 'Template',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'Contrato',
			self::Agreement->value => 'Acordo',
			self::Addendum->value => 'Adendo',
			self::Amendment->value => 'Emenda',
			self::Schedule->value => 'Anexo',
			self::Annex->value => 'Anexo',
			self::Exhibit->value => 'Exibição',
			self::Appendix->value => 'Apêndice',

			// Financial
			self::Invoice->value => 'Fatura',
			self::Receipt->value => 'Recibo',
			self::Quote->value => 'Cotação',
			self::Proposal->value => 'Proposta',
			self::PurchaseOrder->value => 'Ordem de Compra',
			self::PaymentConfirmation->value => 'Confirmação de Pagamento',
			self::BankStatement->value => 'Extrato Bancário',
			self::FinancialStatement->value => 'Demonstração Financeira',
			self::TaxDocument->value => 'Documento Fiscal',
			self::AuditReport->value => 'Relatório de Auditoria',

			// Legal Proceedings
			self::LegalCase->value => 'Processo Legal',
			self::CourtDocument->value => 'Documento Judicial',
			self::Pleading->value => 'Petição',
			self::Motion->value => 'Movimento',
			self::Deposition->value => 'Depoimento',
			self::Affidavit->value => 'Declaração Jurada',
			self::Subpoena->value => 'Intimação',
			self::Summons->value => 'Citação',
			self::Judgment->value => 'Sentença',
			self::Settlement->value => 'Acordo Judicial',

			// Corporate/Compliance
			self::CorporateResolution->value => 'Resolução Corporativa',
			self::Bylaws->value => 'Estatuto Social',
			self::ArticlesOfIncorporation->value => 'Contrato Social',
			self::ShareholderAgreement->value => 'Acordo de Acionistas',
			self::ComplianceDocument->value => 'Documento de Conformidade',
			self::RegulatoryFiling->value => 'Arquivamento Regulatório',
			self::License->value => 'Licença',
			self::Permit->value => 'Permissão',
			self::Certificate->value => 'Certificado',

			// Property/Real Estate
			self::Deed->value => 'Escritura',
			self::Title->value => 'Título',
			self::Lease->value => 'Arrendamento',
			self::Mortgage->value => 'Hipoteca',
			self::Survey->value => 'Levantamento',
			self::Appraisal->value => 'Avaliação',
			self::InsurancePolicy->value => 'Apólice de Seguro',
			self::PropertyDocument->value => 'Documento de Propriedade',

			// Identification/Personal
			self::Identification->value => 'Identificação',
			self::Passport->value => 'Passaporte',
			self::DriverLicense->value => 'Carteira de Motorista',
			self::BirthCertificate->value => 'Certidão de Nascimento',
			self::MarriageCertificate->value => 'Certidão de Casamento',
			self::PowerOfAttorney->value => 'Procuração',
			self::Will->value => 'Testamento',
			self::TrustDocument->value => 'Documento de Confiança',

			// Communication/Correspondence
			self::Correspondence->value => 'Correspondência',
			self::Email->value => 'E-mail',
			self::Letter->value => 'Carta',
			self::Memo->value => 'Memorando',
			self::Notice->value => 'Aviso',
			self::Disclosure->value => 'Divulgação',
			self::Waiver->value => 'Renúncia',
			self::ConsentForm->value => 'Formulário de Consentimento',

			// Intellectual Property
			self::Patent->value => 'Patente',
			self::Trademark->value => 'Marca Registrada',
			self::Copyright->value => 'Direitos Autorais',
			self::NDA->value => 'NDA',
			self::ConfidentialityAgreement->value => 'Acordo de Confidencialidade',
			self::NonCompete->value => 'Acordo de Não Concorrência',
			self::IPAssignment->value => 'Cessão de Propriedade Intelectual',

			// Reports/Records
			self::MeetingMinutes->value => 'Atas de Reunião',
			self::Report->value => 'Relatório',
			self::Record->value => 'Registro',
			self::Log->value => 'Log',
			self::Transcript->value => 'Transcrição',
			self::Verification->value => 'Verificação',
			self::Certification->value => 'Certificação',

			// Proof/Evidence
			self::ProofOfPayment->value => 'Comprovante de Pagamento',
			self::ProofOfAddress->value => 'Comprovante de Endereço',
			self::ProofOfIdentity->value => 'Comprovante de Identidade',
			self::ProofOfInsurance->value => 'Comprovante de Seguro',
			self::ProofOfEmployment->value => 'Comprovante de Emprego',
			self::Evidence->value => 'Evidência',

			// Other
			self::Other->value => 'Outro',
			self::Miscellaneous->value => 'Diversos',
			self::Reference->value => 'Referência',
			self::SupportingDocument->value => 'Documento de Suporte',
			self::Backup->value => 'Backup',
			self::Draft->value => 'Rascunho',
			self::Template->value => 'Modelo',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'Contrato',
			self::Agreement->value => 'Acuerdo',
			self::Addendum->value => 'Adenda',
			self::Amendment->value => 'Enmienda',
			self::Schedule->value => 'Anexo',
			self::Annex->value => 'Anexo',
			self::Exhibit->value => 'Exhibición',
			self::Appendix->value => 'Apéndice',

			// Financial
			self::Invoice->value => 'Factura',
			self::Receipt->value => 'Recibo',
			self::Quote->value => 'Cotización',
			self::Proposal->value => 'Propuesta',
			self::PurchaseOrder->value => 'Orden de Compra',
			self::PaymentConfirmation->value => 'Confirmación de Pago',
			self::BankStatement->value => 'Estado de Cuenta',
			self::FinancialStatement->value => 'Estado Financiero',
			self::TaxDocument->value => 'Documento Fiscal',
			self::AuditReport->value => 'Informe de Auditoría',

			// Legal Proceedings
			self::LegalCase->value => 'Caso Legal',
			self::CourtDocument->value => 'Documento Judicial',
			self::Pleading->value => 'Petición',
			self::Motion->value => 'Moción',
			self::Deposition->value => 'Declaración',
			self::Affidavit->value => 'Declaración Jurada',
			self::Subpoena->value => 'Citación',
			self::Summons->value => 'Citación',
			self::Judgment->value => 'Sentencia',
			self::Settlement->value => 'Acuerdo Judicial',

			// Corporate/Compliance
			self::CorporateResolution->value => 'Resolución Corporativa',
			self::Bylaws->value => 'Estatutos',
			self::ArticlesOfIncorporation->value => 'Actas Constitutivas',
			self::ShareholderAgreement->value => 'Acuerdo de Accionistas',
			self::ComplianceDocument->value => 'Documento de Cumplimiento',
			self::RegulatoryFiling->value => 'Presentación Regulatoria',
			self::License->value => 'Licencia',
			self::Permit->value => 'Permiso',
			self::Certificate->value => 'Certificado',

			// Property/Real Estate
			self::Deed->value => 'Escritura',
			self::Title->value => 'Título',
			self::Lease->value => 'Arrendamiento',
			self::Mortgage->value => 'Hipoteca',
			self::Survey->value => 'Estudio Topográfico',
			self::Appraisal->value => 'Tasación',
			self::InsurancePolicy->value => 'Póliza de Seguro',
			self::PropertyDocument->value => 'Documento de Propiedad',

			// Identification/Personal
			self::Identification->value => 'Identificación',
			self::Passport->value => 'Pasaporte',
			self::DriverLicense->value => 'Licencia de Conducir',
			self::BirthCertificate->value => 'Certificado de Nacimiento',
			self::MarriageCertificate->value => 'Certificado de Matrimonio',
			self::PowerOfAttorney->value => 'Poder Notarial',
			self::Will->value => 'Testamento',
			self::TrustDocument->value => 'Documento de Fideicomiso',

			// Communication/Correspondence
			self::Correspondence->value => 'Correspondencia',
			self::Email->value => 'Correo Electrónico',
			self::Letter->value => 'Carta',
			self::Memo->value => 'Memorándum',
			self::Notice->value => 'Aviso',
			self::Disclosure->value => 'Divulgación',
			self::Waiver->value => 'Renuncia',
			self::ConsentForm->value => 'Formulario de Consentimiento',

			// Intellectual Property
			self::Patent->value => 'Patente',
			self::Trademark->value => 'Marca Registrada',
			self::Copyright->value => 'Derechos de Autor',
			self::NDA->value => 'NDA',
			self::ConfidentialityAgreement->value => 'Acuerdo de Confidencialidad',
			self::NonCompete->value => 'Acuerdo de No Competencia',
			self::IPAssignment->value => 'Cesión de Propiedad Intelectual',

			// Reports/Records
			self::MeetingMinutes->value => 'Acta de Reunión',
			self::Report->value => 'Informe',
			self::Record->value => 'Registro',
			self::Log->value => 'Registro',
			self::Transcript->value => 'Transcripción',
			self::Verification->value => 'Verificación',
			self::Certification->value => 'Certificación',

			// Proof/Evidence
			self::ProofOfPayment->value => 'Comprobante de Pago',
			self::ProofOfAddress->value => 'Comprobante de Domicilio',
			self::ProofOfIdentity->value => 'Comprobante de Identidad',
			self::ProofOfInsurance->value => 'Comprobante de Seguro',
			self::ProofOfEmployment->value => 'Comprobante de Empleo',
			self::Evidence->value => 'Evidencia',

			// Other
			self::Other->value => 'Otro',
			self::Miscellaneous->value => 'Misceláneo',
			self::Reference->value => 'Referencia',
			self::SupportingDocument->value => 'Documento de Soporte',
			self::Backup->value => 'Copia de Seguridad',
			self::Draft->value => 'Borrador',
			self::Template->value => 'Plantilla',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'Contrat',
			self::Agreement->value => 'Accord',
			self::Addendum->value => 'Addendum',
			self::Amendment->value => 'Amendement',
			self::Schedule->value => 'Annexe',
			self::Annex->value => 'Annexe',
			self::Exhibit->value => 'Pièce',
			self::Appendix->value => 'Appendice',

			// Financial
			self::Invoice->value => 'Facture',
			self::Receipt->value => 'Reçu',
			self::Quote->value => 'Devis',
			self::Proposal->value => 'Proposition',
			self::PurchaseOrder->value => 'Bon de Commande',
			self::PaymentConfirmation->value => 'Confirmation de Paiement',
			self::BankStatement->value => 'Relevé Bancaire',
			self::FinancialStatement->value => 'État Financier',
			self::TaxDocument->value => 'Document Fiscal',
			self::AuditReport->value => 'Rapport d\'Audit',

			// Legal Proceedings
			self::LegalCase->value => 'Affaire Juridique',
			self::CourtDocument->value => 'Document Judiciaire',
			self::Pleading->value => 'Plaidoyer',
			self::Motion->value => 'Motion',
			self::Deposition->value => 'Déposition',
			self::Affidavit->value => 'Affidavit',
			self::Subpoena->value => 'Subpoena',
			self::Summons->value => 'Citation',
			self::Judgment->value => 'Jugement',
			self::Settlement->value => 'Règlement',

			// Corporate/Compliance
			self::CorporateResolution->value => 'Résolution d\'Entreprise',
			self::Bylaws->value => 'Statuts',
			self::ArticlesOfIncorporation->value => 'Actes Constitutifs',
			self::ShareholderAgreement->value => 'Accord d\'Actionnaires',
			self::ComplianceDocument->value => 'Document de Conformité',
			self::RegulatoryFiling->value => 'Dépôt Réglementaire',
			self::License->value => 'Licence',
			self::Permit->value => 'Permis',
			self::Certificate->value => 'Certificat',

			// Property/Real Estate
			self::Deed->value => 'Acte',
			self::Title->value => 'Titre',
			self::Lease->value => 'Bail',
			self::Mortgage->value => 'Hypothèque',
			self::Survey->value => 'Arpentage',
			self::Appraisal->value => 'Évaluation',
			self::InsurancePolicy->value => 'Police d\'Assurance',
			self::PropertyDocument->value => 'Document de Propriété',

			// Identification/Personal
			self::Identification->value => 'Identification',
			self::Passport->value => 'Passeport',
			self::DriverLicense->value => 'Permis de Conduire',
			self::BirthCertificate->value => 'Acte de Naissance',
			self::MarriageCertificate->value => 'Acte de Mariage',
			self::PowerOfAttorney->value => 'Procuration',
			self::Will->value => 'Testament',
			self::TrustDocument->value => 'Document de Fiducie',

			// Communication/Correspondence
			self::Correspondence->value => 'Correspondance',
			self::Email->value => 'Courriel',
			self::Letter->value => 'Lettre',
			self::Memo->value => 'Mémorandum',
			self::Notice->value => 'Avis',
			self::Disclosure->value => 'Divulgation',
			self::Waiver->value => 'Renonciation',
			self::ConsentForm->value => 'Formulaire de Consentement',

			// Intellectual Property
			self::Patent->value => 'Brevet',
			self::Trademark->value => 'Marque Déposée',
			self::Copyright->value => 'Droit d\'Auteur',
			self::NDA->value => 'NDA',
			self::ConfidentialityAgreement->value => 'Accord de Confidentialité',
			self::NonCompete->value => 'Accord de Non-Concurrence',
			self::IPAssignment->value => 'Cession de Propriété Intellectuelle',

			// Reports/Records
			self::MeetingMinutes->value => 'Procès-Verbal',
			self::Report->value => 'Rapport',
			self::Record->value => 'Enregistrement',
			self::Log->value => 'Journal',
			self::Transcript->value => 'Transcription',
			self::Verification->value => 'Vérification',
			self::Certification->value => 'Certification',

			// Proof/Evidence
			self::ProofOfPayment->value => 'Preuve de Paiement',
			self::ProofOfAddress->value => 'Preuve de Domicile',
			self::ProofOfIdentity->value => 'Preuve d\'Identité',
			self::ProofOfInsurance->value => 'Preuve d\'Assurance',
			self::ProofOfEmployment->value => 'Preuve d\'Emploi',
			self::Evidence->value => 'Preuve',

			// Other
			self::Other->value => 'Autre',
			self::Miscellaneous->value => 'Divers',
			self::Reference->value => 'Référence',
			self::SupportingDocument->value => 'Document de Soutien',
			self::Backup->value => 'Sauvegarde',
			self::Draft->value => 'Brouillon',
			self::Template->value => 'Modèle',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'Vertrag',
			self::Agreement->value => 'Vereinbarung',
			self::Addendum->value => 'Anhang',
			self::Amendment->value => 'Änderung',
			self::Schedule->value => 'Anlage',
			self::Annex->value => 'Anlage',
			self::Exhibit->value => 'Anlage',
			self::Appendix->value => 'Anhang',

			// Financial
			self::Invoice->value => 'Rechnung',
			self::Receipt->value => 'Quittung',
			self::Quote->value => 'Angebot',
			self::Proposal->value => 'Vorschlag',
			self::PurchaseOrder->value => 'Bestellung',
			self::PaymentConfirmation->value => 'Zahlungsbestätigung',
			self::BankStatement->value => 'Kontoauszug',
			self::FinancialStatement->value => 'Jahresabschluss',
			self::TaxDocument->value => 'Steuerdokument',
			self::AuditReport->value => 'Prüfungsbericht',

			// Legal Proceedings
			self::LegalCase->value => 'Rechtsfall',
			self::CourtDocument->value => 'Gerichtsdokument',
			self::Pleading->value => 'Klageschrift',
			self::Motion->value => 'Antrag',
			self::Deposition->value => 'Zeugenaussage',
			self::Affidavit->value => 'Eidesstattliche Erklärung',
			self::Subpoena->value => 'Vorladung',
			self::Summons->value => 'Ladung',
			self::Judgment->value => 'Urteil',
			self::Settlement->value => 'Vergleich',

			// Corporate/Compliance
			self::CorporateResolution->value => 'Unternehmensbeschluss',
			self::Bylaws->value => 'Satzung',
			self::ArticlesOfIncorporation->value => 'Gründungsurkunde',
			self::ShareholderAgreement->value => 'Aktionärsvereinbarung',
			self::ComplianceDocument->value => 'Compliance-Dokument',
			self::RegulatoryFiling->value => 'Regulatorische Einreichung',
			self::License->value => 'Lizenz',
			self::Permit->value => 'Genehmigung',
			self::Certificate->value => 'Zertifikat',

			// Property/Real Estate
			self::Deed->value => 'Grundstücksurkunde',
			self::Title->value => 'Eigentumsurkunde',
			self::Lease->value => 'Mietvertrag',
			self::Mortgage->value => 'Hypothek',
			self::Survey->value => 'Vermessung',
			self::Appraisal->value => 'Bewertung',
			self::InsurancePolicy->value => 'Versicherungspolice',
			self::PropertyDocument->value => 'Eigentumsdokument',

			// Identification/Personal
			self::Identification->value => 'Identifikation',
			self::Passport->value => 'Reisepass',
			self::DriverLicense->value => 'Führerschein',
			self::BirthCertificate->value => 'Geburtsurkunde',
			self::MarriageCertificate->value => 'Heiratsurkunde',
			self::PowerOfAttorney->value => 'Vollmacht',
			self::Will->value => 'Testament',
			self::TrustDocument->value => 'Treuhanddokument',

			// Communication/Correspondence
			self::Correspondence->value => 'Korrespondenz',
			self::Email->value => 'E-Mail',
			self::Letter->value => 'Brief',
			self::Memo->value => 'Memo',
			self::Notice->value => 'Mitteilung',
			self::Disclosure->value => 'Offenlegung',
			self::Waiver->value => 'Verzichtserklärung',
			self::ConsentForm->value => 'Einwilligungserklärung',

			// Intellectual Property
			self::Patent->value => 'Patent',
			self::Trademark->value => 'Marke',
			self::Copyright->value => 'Urheberrecht',
			self::NDA->value => 'NDA',
			self::ConfidentialityAgreement->value => 'Vertraulichkeitsvereinbarung',
			self::NonCompete->value => 'Wettbewerbsverbot',
			self::IPAssignment->value => 'IP-Übertragung',

			// Reports/Records
			self::MeetingMinutes->value => 'Protokoll',
			self::Report->value => 'Bericht',
			self::Record->value => 'Aufzeichnung',
			self::Log->value => 'Protokoll',
			self::Transcript->value => 'Abschrift',
			self::Verification->value => 'Verifizierung',
			self::Certification->value => 'Zertifizierung',

			// Proof/Evidence
			self::ProofOfPayment->value => 'Zahlungsnachweis',
			self::ProofOfAddress->value => 'Adressnachweis',
			self::ProofOfIdentity->value => 'Identitätsnachweis',
			self::ProofOfInsurance->value => 'Versicherungsnachweis',
			self::ProofOfEmployment->value => 'Beschäftigungsnachweis',
			self::Evidence->value => 'Beweis',

			// Other
			self::Other->value => 'Andere',
			self::Miscellaneous->value => 'Verschiedenes',
			self::Reference->value => 'Referenz',
			self::SupportingDocument->value => 'Unterstützendes Dokument',
			self::Backup->value => 'Sicherungskopie',
			self::Draft->value => 'Entwurf',
			self::Template->value => 'Vorlage',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'Contratto',
			self::Agreement->value => 'Accordo',
			self::Addendum->value => 'Addendum',
			self::Amendment->value => 'Emendamento',
			self::Schedule->value => 'Allegato',
			self::Annex->value => 'Allegato',
			self::Exhibit->value => 'Allegato',
			self::Appendix->value => 'Appendice',

			// Financial
			self::Invoice->value => 'Fattura',
			self::Receipt->value => 'Ricevuta',
			self::Quote->value => 'Preventivo',
			self::Proposal->value => 'Proposta',
			self::PurchaseOrder->value => 'Ordine di Acquisto',
			self::PaymentConfirmation->value => 'Conferma di Pagamento',
			self::BankStatement->value => 'Estratto Conto',
			self::FinancialStatement->value => 'Bilancio',
			self::TaxDocument->value => 'Documento Fiscale',
			self::AuditReport->value => 'Relazione di Audit',

			// Legal Proceedings
			self::LegalCase->value => 'Caso Legale',
			self::CourtDocument->value => 'Documento Giudiziario',
			self::Pleading->value => 'Richiesta',
			self::Motion->value => 'Mozione',
			self::Deposition->value => 'Deposizione',
			self::Affidavit->value => 'Dichiarazione Giurata',
			self::Subpoena->value => 'Citazione',
			self::Summons->value => 'Citazione',
			self::Judgment->value => 'Sentenza',
			self::Settlement->value => 'Transazione',

			// Corporate/Compliance
			self::CorporateResolution->value => 'Delibera Aziendale',
			self::Bylaws->value => 'Statuto',
			self::ArticlesOfIncorporation->value => 'Atto Costitutivo',
			self::ShareholderAgreement->value => 'Accordo tra Azionisti',
			self::ComplianceDocument->value => 'Documento di Conformità',
			self::RegulatoryFiling->value => 'Deposito Regolamentare',
			self::License->value => 'Licenza',
			self::Permit->value => 'Permesso',
			self::Certificate->value => 'Certificato',

			// Property/Real Estate
			self::Deed->value => 'Atto',
			self::Title->value => 'Titolo',
			self::Lease->value => 'Contratto di Locazione',
			self::Mortgage->value => 'Mutuo',
			self::Survey->value => 'Rilevamento',
			self::Appraisal->value => 'Valutazione',
			self::InsurancePolicy->value => 'Polizza Assicurativa',
			self::PropertyDocument->value => 'Documento di Proprietà',

			// Identification/Personal
			self::Identification->value => 'Identificazione',
			self::Passport->value => 'Passaporto',
			self::DriverLicense->value => 'Patente di Guida',
			self::BirthCertificate->value => 'Certificato di Nascita',
			self::MarriageCertificate->value => 'Certificato di Matrimonio',
			self::PowerOfAttorney->value => 'Procura',
			self::Will->value => 'Testamento',
			self::TrustDocument->value => 'Documento Fiduciario',

			// Communication/Correspondence
			self::Correspondence->value => 'Corrispondenza',
			self::Email->value => 'Email',
			self::Letter->value => 'Lettera',
			self::Memo->value => 'Promemoria',
			self::Notice->value => 'Avviso',
			self::Disclosure->value => 'Divulgazione',
			self::Waiver->value => 'Rinuncia',
			self::ConsentForm->value => 'Modulo di Consenso',

			// Intellectual Property
			self::Patent->value => 'Brevetto',
			self::Trademark->value => 'Marchio Registrato',
			self::Copyright->value => 'Diritto d\'Autore',
			self::NDA->value => 'NDA',
			self::ConfidentialityAgreement->value => 'Accordo di Riservatezza',
			self::NonCompete->value => 'Accordo di Non Concorrenza',
			self::IPAssignment->value => 'Cessione di Proprietà Intellettuale',

			// Reports/Records
			self::MeetingMinutes->value => 'Verbale della Riunione',
			self::Report->value => 'Rapporto',
			self::Record->value => 'Registro',
			self::Log->value => 'Registro',
			self::Transcript->value => 'Trascrizione',
			self::Verification->value => 'Verifica',
			self::Certification->value => 'Certificazione',

			// Proof/Evidence
			self::ProofOfPayment->value => 'Prova di Pagamento',
			self::ProofOfAddress->value => 'Prova di Residenza',
			self::ProofOfIdentity->value => 'Prova di Identità',
			self::ProofOfInsurance->value => 'Prova di Assicurazione',
			self::ProofOfEmployment->value => 'Prova di Occupazione',
			self::Evidence->value => 'Prova',

			// Other
			self::Other->value => 'Altro',
			self::Miscellaneous->value => 'Varie',
			self::Reference->value => 'Riferimento',
			self::SupportingDocument->value => 'Documento di Supporto',
			self::Backup->value => 'Backup',
			self::Draft->value => 'Bozza',
			self::Template->value => 'Modello',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'Contract',
			self::Agreement->value => 'Overeenkomst',
			self::Addendum->value => 'Addendum',
			self::Amendment->value => 'Wijziging',
			self::Schedule->value => 'Bijlage',
			self::Annex->value => 'Bijlage',
			self::Exhibit->value => 'Bijlage',
			self::Appendix->value => 'Bijlage',

			// Financial
			self::Invoice->value => 'Factuur',
			self::Receipt->value => 'Bon',
			self::Quote->value => 'Offerte',
			self::Proposal->value => 'Voorstel',
			self::PurchaseOrder->value => 'Inkooporder',
			self::PaymentConfirmation->value => 'Betalingsbevestiging',
			self::BankStatement->value => 'Bankafschrift',
			self::FinancialStatement->value => 'Financiële Verklaring',
			self::TaxDocument->value => 'Belastingdocument',
			self::AuditReport->value => 'Auditrapport',

			// Legal Proceedings
			self::LegalCase->value => 'Rechtszaak',
			self::CourtDocument->value => 'Gerechtsdocument',
			self::Pleading->value => 'Pleitnota',
			self::Motion->value => 'Motie',
			self::Deposition->value => 'Verklaring',
			self::Affidavit->value => 'Beëdigde Verklaring',
			self::Subpoena->value => 'Dagvaarding',
			self::Summons->value => 'Dagvaarding',
			self::Judgment->value => 'Vonnis',
			self::Settlement->value => 'Schikking',

			// Corporate/Compliance
			self::CorporateResolution->value => 'Bedrijfsbesluit',
			self::Bylaws->value => 'Statuten',
			self::ArticlesOfIncorporation->value => 'Oprichtingsakte',
			self::ShareholderAgreement->value => 'Aandeelhoudersovereenkomst',
			self::ComplianceDocument->value => 'Nalevingsdocument',
			self::RegulatoryFiling->value => 'Regulatorische Indiening',
			self::License->value => 'Licentie',
			self::Permit->value => 'Vergunning',
			self::Certificate->value => 'Certificaat',

			// Property/Real Estate
			self::Deed->value => 'Akte',
			self::Title->value => 'Titel',
			self::Lease->value => 'Huurcontract',
			self::Mortgage->value => 'Hypotheek',
			self::Survey->value => 'Kadastraal Onderzoek',
			self::Appraisal->value => 'Taxatie',
			self::InsurancePolicy->value => 'Verzekeringspolis',
			self::PropertyDocument->value => 'Eigendomsdocument',

			// Identification/Personal
			self::Identification->value => 'Identificatie',
			self::Passport->value => 'Paspoort',
			self::DriverLicense->value => 'Rijbewijs',
			self::BirthCertificate->value => 'Geboorteakte',
			self::MarriageCertificate->value => 'Huwelijksakte',
			self::PowerOfAttorney->value => 'Volmacht',
			self::Will->value => 'Testament',
			self::TrustDocument->value => 'Trustdocument',

			// Communication/Correspondence
			self::Correspondence->value => 'Correspondentie',
			self::Email->value => 'E-mail',
			self::Letter->value => 'Brief',
			self::Memo->value => 'Memorandum',
			self::Notice->value => 'Kennisgeving',
			self::Disclosure->value => 'Openbaarmaking',
			self::Waiver->value => 'Vrijstelling',
			self::ConsentForm->value => 'Toestemmingsformulier',

			// Intellectual Property
			self::Patent->value => 'Octrooi',
			self::Trademark->value => 'Handelsmerk',
			self::Copyright->value => 'Auteursrecht',
			self::NDA->value => 'NDA',
			self::ConfidentialityAgreement->value => 'Vertrouwelijkheidsovereenkomst',
			self::NonCompete->value => 'Concurrentiebeding',
			self::IPAssignment->value => 'IP-overdracht',

			// Reports/Records
			self::MeetingMinutes->value => 'Notulen',
			self::Report->value => 'Rapport',
			self::Record->value => 'Record',
			self::Log->value => 'Logboek',
			self::Transcript->value => 'Transcriptie',
			self::Verification->value => 'Verificatie',
			self::Certification->value => 'Certificering',

			// Proof/Evidence
			self::ProofOfPayment->value => 'Betalingsbewijs',
			self::ProofOfAddress->value => 'Adresbewijs',
			self::ProofOfIdentity->value => 'Identiteitsbewijs',
			self::ProofOfInsurance->value => 'Verzekeringsbewijs',
			self::ProofOfEmployment->value => 'Werkgeversverklaring',
			self::Evidence->value => 'Bewijs',

			// Other
			self::Other->value => 'Anders',
			self::Miscellaneous->value => 'Diversen',
			self::Reference->value => 'Referentie',
			self::SupportingDocument->value => 'Ondersteunend Document',
			self::Backup->value => 'Back-up',
			self::Draft->value => 'Concept',
			self::Template->value => 'Sjabloon',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'Umowa',
			self::Agreement->value => 'Porozumienie',
			self::Addendum->value => 'Dodatek',
			self::Amendment->value => 'Poprawka',
			self::Schedule->value => 'Załącznik',
			self::Annex->value => 'Załącznik',
			self::Exhibit->value => 'Załącznik',
			self::Appendix->value => 'Dodatek',

			// Financial
			self::Invoice->value => 'Faktura',
			self::Receipt->value => 'Paragon',
			self::Quote->value => 'Wycenia',
			self::Proposal->value => 'Propozycja',
			self::PurchaseOrder->value => 'Zamówienie',
			self::PaymentConfirmation->value => 'Potwierdzenie Płatności',
			self::BankStatement->value => 'Wyciąg Bankowy',
			self::FinancialStatement->value => 'Sprawozdanie Finansowe',
			self::TaxDocument->value => 'Dokument Podatkowy',
			self::AuditReport->value => 'Raport z Audytu',

			// Legal Proceedings
			self::LegalCase->value => 'Sprawa Prawna',
			self::CourtDocument->value => 'Dokument Sądowy',
			self::Pleading->value => 'Pozew',
			self::Motion->value => 'Wniosek',
			self::Deposition->value => 'Zeznanie',
			self::Affidavit->value => 'Oświadczenie pod Przysięgą',
			self::Subpoena->value => 'Wezwanie',
			self::Summons->value => 'Wezwanie',
			self::Judgment->value => 'Wyrok',
			self::Settlement->value => 'Ugoda',

			// Corporate/Compliance
			self::CorporateResolution->value => 'Uchwała Korporacyjna',
			self::Bylaws->value => 'Statut',
			self::ArticlesOfIncorporation->value => 'Akt Założycielski',
			self::ShareholderAgreement->value => 'Umowa Akcjonariuszy',
			self::ComplianceDocument->value => 'Dokument Zgodności',
			self::RegulatoryFiling->value => 'Zgłoszenie Regulacyjne',
			self::License->value => 'Licencja',
			self::Permit->value => 'Pozwolenie',
			self::Certificate->value => 'Certyfikat',

			// Property/Real Estate
			self::Deed->value => 'Akt Własności',
			self::Title->value => 'Tytuł Własności',
			self::Lease->value => 'Umowa Najmu',
			self::Mortgage->value => 'Hipoteka',
			self::Survey->value => 'Pomiar',
			self::Appraisal->value => 'Wycenia',
			self::InsurancePolicy->value => 'Polisa Ubezpieczeniowa',
			self::PropertyDocument->value => 'Dokument Własności',

			// Identification/Personal
			self::Identification->value => 'Identyfikacja',
			self::Passport->value => 'Paszport',
			self::DriverLicense->value => 'Prawo Jazdy',
			self::BirthCertificate->value => 'Akt Urodzenia',
			self::MarriageCertificate->value => 'Akt Małżeństwa',
			self::PowerOfAttorney->value => 'Pełnomocnictwo',
			self::Will->value => 'Testament',
			self::TrustDocument->value => 'Dokument Powierniczy',

			// Communication/Correspondence
			self::Correspondence->value => 'Korespondencja',
			self::Email->value => 'E-mail',
			self::Letter->value => 'List',
			self::Memo->value => 'Notatka',
			self::Notice->value => 'Powiadomienie',
			self::Disclosure->value => 'Ujawnienie',
			self::Waiver->value => 'Zrzeczenie się',
			self::ConsentForm->value => 'Formularz Zgody',

			// Intellectual Property
			self::Patent->value => 'Patent',
			self::Trademark->value => 'Znak Towarowy',
			self::Copyright->value => 'Prawa Autorskie',
			self::NDA->value => 'NDA',
			self::ConfidentialityAgreement->value => 'Umowa o Poufności',
			self::NonCompete->value => 'Klauzula o Zakazie Konkurencji',
			self::IPAssignment->value => 'Przeniesienie Praw Własności Intelektualnej',

			// Reports/Records
			self::MeetingMinutes->value => 'Protokół z Posiedzenia',
			self::Report->value => 'Raport',
			self::Record->value => 'Zapis',
			self::Log->value => 'Dziennik',
			self::Transcript->value => 'Transkrypcja',
			self::Verification->value => 'Weryfikacja',
			self::Certification->value => 'Certyfikacja',

			// Proof/Evidence
			self::ProofOfPayment->value => 'Dowód Zapłaty',
			self::ProofOfAddress->value => 'Dowód Adresu',
			self::ProofOfIdentity->value => 'Dowód Tożsamości',
			self::ProofOfInsurance->value => 'Dowód Ubezpieczenia',
			self::ProofOfEmployment->value => 'Dowód Zatrudnienia',
			self::Evidence->value => 'Dowód',

			// Other
			self::Other->value => 'Inny',
			self::Miscellaneous->value => 'Różne',
			self::Reference->value => 'Referencja',
			self::SupportingDocument->value => 'Dokument Wspierający',
			self::Backup->value => 'Kopia Zapasowa',
			self::Draft->value => 'Szkic',
			self::Template->value => 'Szablon',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'Договор',
			self::Agreement->value => 'Соглашение',
			self::Addendum->value => 'Дополнение',
			self::Amendment->value => 'Поправка',
			self::Schedule->value => 'Приложение',
			self::Annex->value => 'Приложение',
			self::Exhibit->value => 'Приложение',
			self::Appendix->value => 'Приложение',

			// Financial
			self::Invoice->value => 'Счет',
			self::Receipt->value => 'Квитанция',
			self::Quote->value => 'Предложение',
			self::Proposal->value => 'Предложение',
			self::PurchaseOrder->value => 'Заказ на покупку',
			self::PaymentConfirmation->value => 'Подтверждение оплаты',
			self::BankStatement->value => 'Выписка из банка',
			self::FinancialStatement->value => 'Финансовый отчет',
			self::TaxDocument->value => 'Налоговый документ',
			self::AuditReport->value => 'Аудиторский отчет',

			// Legal Proceedings
			self::LegalCase->value => 'Юридическое дело',
			self::CourtDocument->value => 'Судебный документ',
			self::Pleading->value => 'Исковое заявление',
			self::Motion->value => 'Ходатайство',
			self::Deposition->value => 'Показание',
			self::Affidavit->value => 'Аффидевит',
			self::Subpoena->value => 'Судебная повестка',
			self::Summons->value => 'Повестка',
			self::Judgment->value => 'Решение суда',
			self::Settlement->value => 'Мировое соглашение',

			// Corporate/Compliance
			self::CorporateResolution->value => 'Корпоративное решение',
			self::Bylaws->value => 'Устав',
			self::ArticlesOfIncorporation->value => 'Учредительные документы',
			self::ShareholderAgreement->value => 'Соглашение акционеров',
			self::ComplianceDocument->value => 'Документ соответствия',
			self::RegulatoryFiling->value => 'Регуляторная подача',
			self::License->value => 'Лицензия',
			self::Permit->value => 'Разрешение',
			self::Certificate->value => 'Сертификат',

			// Property/Real Estate
			self::Deed->value => 'Акт собственности',
			self::Title->value => 'Титул собственности',
			self::Lease->value => 'Аренда',
			self::Mortgage->value => 'Ипотека',
			self::Survey->value => 'Обследование',
			self::Appraisal->value => 'Оценка',
			self::InsurancePolicy->value => 'Страховой полис',
			self::PropertyDocument->value => 'Документ на собственность',

			// Identification/Personal
			self::Identification->value => 'Идентификация',
			self::Passport->value => 'Паспорт',
			self::DriverLicense->value => 'Водительские права',
			self::BirthCertificate->value => 'Свидетельство о рождении',
			self::MarriageCertificate->value => 'Свидетельство о браке',
			self::PowerOfAttorney->value => 'Доверенность',
			self::Will->value => 'Завещание',
			self::TrustDocument->value => 'Документ доверительного управления',

			// Communication/Correspondence
			self::Correspondence->value => 'Корреспонденция',
			self::Email->value => 'Электронная почта',
			self::Letter->value => 'Письмо',
			self::Memo->value => 'Меморандум',
			self::Notice->value => 'Уведомление',
			self::Disclosure->value => 'Раскрытие информации',
			self::Waiver->value => 'Отказ от прав',
			self::ConsentForm->value => 'Форма согласия',

			// Intellectual Property
			self::Patent->value => 'Патент',
			self::Trademark->value => 'Товарный знак',
			self::Copyright->value => 'Авторское право',
			self::NDA->value => 'Соглашение о неразглашении',
			self::ConfidentialityAgreement->value => 'Соглашение о конфиденциальности',
			self::NonCompete->value => 'Соглашение о неконкуренции',
			self::IPAssignment->value => 'Передача прав интеллектуальной собственности',

			// Reports/Records
			self::MeetingMinutes->value => 'Протокол собрания',
			self::Report->value => 'Отчет',
			self::Record->value => 'Запись',
			self::Log->value => 'Журнал',
			self::Transcript->value => 'Расшифровка',
			self::Verification->value => 'Проверка',
			self::Certification->value => 'Сертификация',

			// Proof/Evidence
			self::ProofOfPayment->value => 'Подтверждение оплаты',
			self::ProofOfAddress->value => 'Подтверждение адреса',
			self::ProofOfIdentity->value => 'Подтверждение личности',
			self::ProofOfInsurance->value => 'Подтверждение страхования',
			self::ProofOfEmployment->value => 'Подтверждение трудоустройства',
			self::Evidence->value => 'Доказательство',

			// Other
			self::Other->value => 'Другое',
			self::Miscellaneous->value => 'Разное',
			self::Reference->value => 'Ссылка',
			self::SupportingDocument->value => 'Поддерживающий документ',
			self::Backup->value => 'Резервная копия',
			self::Draft->value => 'Черновик',
			self::Template->value => 'Шаблон',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'Sözleşme',
			self::Agreement->value => 'Anlaşma',
			self::Addendum->value => 'Ek',
			self::Amendment->value => 'Değişiklik',
			self::Schedule->value => 'Ek',
			self::Annex->value => 'Ek',
			self::Exhibit->value => 'Ek',
			self::Appendix->value => 'Ek',

			// Financial
			self::Invoice->value => 'Fatura',
			self::Receipt->value => 'Fiş',
			self::Quote->value => 'Teklif',
			self::Proposal->value => 'Öneri',
			self::PurchaseOrder->value => 'Satın Alma Siparişi',
			self::PaymentConfirmation->value => 'Ödeme Onayı',
			self::BankStatement->value => 'Banka Ekstresi',
			self::FinancialStatement->value => 'Finansal Tablo',
			self::TaxDocument->value => 'Vergi Belgesi',
			self::AuditReport->value => 'Denetim Raporu',

			// Legal Proceedings
			self::LegalCase->value => 'Dava',
			self::CourtDocument->value => 'Mahkeme Belgesi',
			self::Pleading->value => 'Dilekçe',
			self::Motion->value => 'Talep',
			self::Deposition->value => 'İfade',
			self::Affidavit->value => 'Beyanname',
			self::Subpoena->value => 'Mahkeme Celbi',
			self::Summons->value => 'Celp',
			self::Judgment->value => 'Karar',
			self::Settlement->value => 'Uzlaşma',

			// Corporate/Compliance
			self::CorporateResolution->value => 'Kurumsal Karar',
			self::Bylaws->value => 'Tüzük',
			self::ArticlesOfIncorporation->value => 'Kuruluş Sözleşmesi',
			self::ShareholderAgreement->value => 'Ortaklık Sözleşmesi',
			self::ComplianceDocument->value => 'Uyumluluk Belgesi',
			self::RegulatoryFiling->value => 'Düzenleyici Dosya',
			self::License->value => 'Lisans',
			self::Permit->value => 'İzin',
			self::Certificate->value => 'Sertifika',

			// Property/Real Estate
			self::Deed->value => 'Tapu',
			self::Title->value => 'Mülkiyet Belgesi',
			self::Lease->value => 'Kira Sözleşmesi',
			self::Mortgage->value => 'İpotek',
			self::Survey->value => 'Kadastro',
			self::Appraisal->value => 'Değerleme',
			self::InsurancePolicy->value => 'Sigorta Poliçesi',
			self::PropertyDocument->value => 'Mülk Belgesi',

			// Identification/Personal
			self::Identification->value => 'Kimlik',
			self::Passport->value => 'Pasaport',
			self::DriverLicense->value => 'Sürücü Belgesi',
			self::BirthCertificate->value => 'Doğum Belgesi',
			self::MarriageCertificate->value => 'Evlilik Belgesi',
			self::PowerOfAttorney->value => 'Vekaletname',
			self::Will->value => 'Vasiyetname',
			self::TrustDocument->value => 'Güven Belgesi',

			// Communication/Correspondence
			self::Correspondence->value => 'Yazışma',
			self::Email->value => 'E-posta',
			self::Letter->value => 'Mektup',
			self::Memo->value => 'Not',
			self::Notice->value => 'Bildirim',
			self::Disclosure->value => 'Açıklama',
			self::Waiver->value => 'Feragat',
			self::ConsentForm->value => 'Onam Formu',

			// Intellectual Property
			self::Patent->value => 'Patent',
			self::Trademark->value => 'Ticari Marka',
			self::Copyright->value => 'Telif Hakkı',
			self::NDA->value => 'Gizlilik Sözleşmesi',
			self::ConfidentialityAgreement->value => 'Gizlilik Anlaşması',
			self::NonCompete->value => 'Rekabet Etmeme Anlaşması',
			self::IPAssignment->value => 'Fikri Mülkiyet Devri',

			// Reports/Records
			self::MeetingMinutes->value => 'Toplantı Tutanağı',
			self::Report->value => 'Rapor',
			self::Record->value => 'Kayıt',
			self::Log->value => 'Günlük',
			self::Transcript->value => 'Transkript',
			self::Verification->value => 'Doğrulama',
			self::Certification->value => 'Sertifikasyon',

			// Proof/Evidence
			self::ProofOfPayment->value => 'Ödeme Kanıtı',
			self::ProofOfAddress->value => 'Adres Kanıtı',
			self::ProofOfIdentity->value => 'Kimlik Kanıtı',
			self::ProofOfInsurance->value => 'Sigorta Kanıtı',
			self::ProofOfEmployment->value => 'İstihdam Kanıtı',
			self::Evidence->value => 'Kanıt',

			// Other
			self::Other->value => 'Diğer',
			self::Miscellaneous->value => 'Çeşitli',
			self::Reference->value => 'Referans',
			self::SupportingDocument->value => 'Destekleyici Belge',
			self::Backup->value => 'Yedek',
			self::Draft->value => 'Taslak',
			self::Template->value => 'Şablon',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'عقد',
			self::Agreement->value => 'اتفاقية',
			self::Addendum->value => 'ملحق',
			self::Amendment->value => 'تعديل',
			self::Schedule->value => 'ملحق',
			self::Annex->value => 'ملحق',
			self::Exhibit->value => 'ملحق',
			self::Appendix->value => 'ملحق',

			// Financial
			self::Invoice->value => 'فاتورة',
			self::Receipt->value => 'إيصال',
			self::Quote->value => 'عرض سعر',
			self::Proposal->value => 'مقترح',
			self::PurchaseOrder->value => 'أمر شراء',
			self::PaymentConfirmation->value => 'تأكيد الدفع',
			self::BankStatement->value => 'كشف حساب بنكي',
			self::FinancialStatement->value => 'كشف مالي',
			self::TaxDocument->value => 'مستند ضريبي',
			self::AuditReport->value => 'تقرير مراجعة',

			// Legal Proceedings
			self::LegalCase->value => 'قضية قانونية',
			self::CourtDocument->value => 'مستند قضائي',
			self::Pleading->value => 'دعوى',
			self::Motion->value => 'طلب',
			self::Deposition->value => 'إفادة',
			self::Affidavit->value => 'إقرار خطي',
			self::Subpoena->value => 'استدعاء قضائي',
			self::Summons->value => 'استدعاء',
			self::Judgment->value => 'حكم',
			self::Settlement->value => 'تسوية',

			// Corporate/Compliance
			self::CorporateResolution->value => 'قرار شركة',
			self::Bylaws->value => 'النظام الأساسي',
			self::ArticlesOfIncorporation->value => 'عقد التأسيس',
			self::ShareholderAgreement->value => 'اتفاق المساهمين',
			self::ComplianceDocument->value => 'مستند الامتثال',
			self::RegulatoryFiling->value => 'إيداع تنظيمي',
			self::License->value => 'رخصة',
			self::Permit->value => 'تصريح',
			self::Certificate->value => 'شهادة',

			// Property/Real Estate
			self::Deed->value => 'سند ملكية',
			self::Title->value => 'ملكية',
			self::Lease->value => 'عقد إيجار',
			self::Mortgage->value => 'رهن عقاري',
			self::Survey->value => 'مسح',
			self::Appraisal->value => 'تقييم',
			self::InsurancePolicy->value => 'بوليصة تأمين',
			self::PropertyDocument->value => 'مستند ملكية',

			// Identification/Personal
			self::Identification->value => 'هوية',
			self::Passport->value => 'جواز سفر',
			self::DriverLicense->value => 'رخصة قيادة',
			self::BirthCertificate->value => 'شهادة ميلاد',
			self::MarriageCertificate->value => 'شهادة زواج',
			self::PowerOfAttorney->value => 'تفويض',
			self::Will->value => 'وصية',
			self::TrustDocument->value => 'مستند وصاية',

			// Communication/Correspondence
			self::Correspondence->value => 'مراسلات',
			self::Email->value => 'بريد إلكتروني',
			self::Letter->value => 'رسالة',
			self::Memo->value => 'مذكرة',
			self::Notice->value => 'إشعار',
			self::Disclosure->value => 'إفصاح',
			self::Waiver->value => 'تنازل',
			self::ConsentForm->value => 'نموذج موافقة',

			// Intellectual Property
			self::Patent->value => 'براءة اختراع',
			self::Trademark->value => 'علامة تجارية',
			self::Copyright->value => 'حقوق نشر',
			self::NDA->value => 'اتفاقية عدم إفصاح',
			self::ConfidentialityAgreement->value => 'اتفاقية سرية',
			self::NonCompete->value => 'اتفاقية عدم منافسة',
			self::IPAssignment->value => 'نقل ملكية فكرية',

			// Reports/Records
			self::MeetingMinutes->value => 'محضر اجتماع',
			self::Report->value => 'تقرير',
			self::Record->value => 'سجل',
			self::Log->value => 'سجل',
			self::Transcript->value => 'نص',
			self::Verification->value => 'تحقق',
			self::Certification->value => 'شهادة',

			// Proof/Evidence
			self::ProofOfPayment->value => 'إثبات دفع',
			self::ProofOfAddress->value => 'إثبات عنوان',
			self::ProofOfIdentity->value => 'إثبات هوية',
			self::ProofOfInsurance->value => 'إثبات تأمين',
			self::ProofOfEmployment->value => 'إثبات توظيف',
			self::Evidence->value => 'دليل',

			// Other
			self::Other->value => 'أخرى',
			self::Miscellaneous->value => 'متنوع',
			self::Reference->value => 'مرجع',
			self::SupportingDocument->value => 'مستند داعم',
			self::Backup->value => 'نسخة احتياطية',
			self::Draft->value => 'مسودة',
			self::Template->value => 'قالب',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'חוזה',
			self::Agreement->value => 'הסכם',
			self::Addendum->value => 'נספח',
			self::Amendment->value => 'תיקון',
			self::Schedule->value => 'נספח',
			self::Annex->value => 'נספח',
			self::Exhibit->value => 'נספח',
			self::Appendix->value => 'נספח',

			// Financial
			self::Invoice->value => 'חשבונית',
			self::Receipt->value => 'קבלה',
			self::Quote->value => 'הצעת מחיר',
			self::Proposal->value => 'הצעה',
			self::PurchaseOrder->value => 'הזמנת רכש',
			self::PaymentConfirmation->value => 'אישור תשלום',
			self::BankStatement->value => 'דוח בנקאי',
			self::FinancialStatement->value => 'דוח כספי',
			self::TaxDocument->value => 'מסמך מס',
			self::AuditReport->value => 'דוח ביקורת',

			// Legal Proceedings
			self::LegalCase->value => 'תיק משפטי',
			self::CourtDocument->value => 'מסמך בית משפט',
			self::Pleading->value => 'תביעה',
			self::Motion->value => 'בקשה',
			self::Deposition->value => 'הצהרה',
			self::Affidavit->value => 'הצהרה בשבועה',
			self::Subpoena->value => 'זימון לבית משפט',
			self::Summons->value => 'זימון',
			self::Judgment->value => 'פסק דין',
			self::Settlement->value => 'הסדר',

			// Corporate/Compliance
			self::CorporateResolution->value => 'החלטה תאגידית',
			self::Bylaws->value => 'תקנון',
			self::ArticlesOfIncorporation->value => 'תעודת התאגדות',
			self::ShareholderAgreement->value => 'הסכם בעלי מניות',
			self::ComplianceDocument->value => 'מסמך תאימות',
			self::RegulatoryFiling->value => 'הגשה רגולטורית',
			self::License->value => 'רישיון',
			self::Permit->value => 'היתר',
			self::Certificate->value => 'תעודה',

			// Property/Real Estate
			self::Deed->value => 'שטר בעלות',
			self::Title->value => 'זכות בעלות',
			self::Lease->value => 'חוזה שכירות',
			self::Mortgage->value => 'משכנתא',
			self::Survey->value => 'מדידה',
			self::Appraisal->value => 'הערכה',
			self::InsurancePolicy->value => 'פוליסת ביטוח',
			self::PropertyDocument->value => 'מסמך נכס',

			// Identification/Personal
			self::Identification->value => 'זיהוי',
			self::Passport->value => 'דרכון',
			self::DriverLicense->value => 'רישיון נהיגה',
			self::BirthCertificate->value => 'תעודת לידה',
			self::MarriageCertificate->value => 'תעודת נישואין',
			self::PowerOfAttorney->value => 'ייפוי כוח',
			self::Will->value => 'צוואה',
			self::TrustDocument->value => 'מסמך נאמנות',

			// Communication/Correspondence
			self::Correspondence->value => 'התכתבות',
			self::Email->value => 'דוא"ל',
			self::Letter->value => 'מכתב',
			self::Memo->value => 'תזכיר',
			self::Notice->value => 'הודעה',
			self::Disclosure->value => 'גילוי',
			self::Waiver->value => 'ויתור',
			self::ConsentForm->value => 'טופס הסכמה',

			// Intellectual Property
			self::Patent->value => 'פטנט',
			self::Trademark->value => 'סימן מסחר',
			self::Copyright->value => 'זכויות יוצרים',
			self::NDA->value => 'הסכם סודיות',
			self::ConfidentialityAgreement->value => 'הסכם סודיות',
			self::NonCompete->value => 'הסכם אי תחרות',
			self::IPAssignment->value => 'העברת זכויות קניין רוחני',

			// Reports/Records
			self::MeetingMinutes->value => 'דו"ח ישיבה',
			self::Report->value => 'דוח',
			self::Record->value => 'רשומה',
			self::Log->value => 'יומן',
			self::Transcript->value => 'תמליל',
			self::Verification->value => 'אימות',
			self::Certification->value => 'הסמכה',

			// Proof/Evidence
			self::ProofOfPayment->value => 'הוכחת תשלום',
			self::ProofOfAddress->value => 'הוכחת כתובת',
			self::ProofOfIdentity->value => 'הוכחת זהות',
			self::ProofOfInsurance->value => 'הוכחת ביטוח',
			self::ProofOfEmployment->value => 'הוכחת עבודה',
			self::Evidence->value => 'ראיה',

			// Other
			self::Other->value => 'אחר',
			self::Miscellaneous->value => 'שונות',
			self::Reference->value => 'הפניה',
			self::SupportingDocument->value => 'מסמך תומך',
			self::Backup->value => 'גיבוי',
			self::Draft->value => 'טיוטה',
			self::Template->value => 'תבנית',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => '契約書',
			self::Agreement->value => '合意書',
			self::Addendum->value => '追加条項',
			self::Amendment->value => '修正条項',
			self::Schedule->value => '別紙',
			self::Annex->value => '別紙',
			self::Exhibit->value => '別紙',
			self::Appendix->value => '付録',

			// Financial
			self::Invoice->value => '請求書',
			self::Receipt->value => '領収書',
			self::Quote->value => '見積書',
			self::Proposal->value => '提案書',
			self::PurchaseOrder->value => '発注書',
			self::PaymentConfirmation->value => '支払確認',
			self::BankStatement->value => '銀行取引明細書',
			self::FinancialStatement->value => '財務諸表',
			self::TaxDocument->value => '税務書類',
			self::AuditReport->value => '監査報告書',

			// Legal Proceedings
			self::LegalCase->value => '訴訟案件',
			self::CourtDocument->value => '裁判所文書',
			self::Pleading->value => '訴状',
			self::Motion->value => '申立書',
			self::Deposition->value => '証言録取書',
			self::Affidavit->value => '宣誓供述書',
			self::Subpoena->value => '召喚状',
			self::Summons->value => '召喚状',
			self::Judgment->value => '判決書',
			self::Settlement->value => '和解契約書',

			// Corporate/Compliance
			self::CorporateResolution->value => '企業決議',
			self::Bylaws->value => '定款',
			self::ArticlesOfIncorporation->value => '定款',
			self::ShareholderAgreement->value => '株主契約書',
			self::ComplianceDocument->value => 'コンプライアンス文書',
			self::RegulatoryFiling->value => '規制提出書類',
			self::License->value => 'ライセンス',
			self::Permit->value => '許可証',
			self::Certificate->value => '証明書',

			// Property/Real Estate
			self::Deed->value => '権利証',
			self::Title->value => '所有権証書',
			self::Lease->value => '賃貸契約書',
			self::Mortgage->value => '抵当権設定証書',
			self::Survey->value => '測量図',
			self::Appraisal->value => '評価書',
			self::InsurancePolicy->value => '保険証券',
			self::PropertyDocument->value => '不動産文書',

			// Identification/Personal
			self::Identification->value => '身分証明書',
			self::Passport->value => 'パスポート',
			self::DriverLicense->value => '運転免許証',
			self::BirthCertificate->value => '出生証明書',
			self::MarriageCertificate->value => '結婚証明書',
			self::PowerOfAttorney->value => '委任状',
			self::Will->value => '遺言書',
			self::TrustDocument->value => '信託書類',

			// Communication/Correspondence
			self::Correspondence->value => '書簡',
			self::Email->value => 'メール',
			self::Letter->value => '手紙',
			self::Memo->value => 'メモ',
			self::Notice->value => '通知',
			self::Disclosure->value => '開示',
			self::Waiver->value => '放棄書',
			self::ConsentForm->value => '同意書',

			// Intellectual Property
			self::Patent->value => '特許',
			self::Trademark->value => '商標',
			self::Copyright->value => '著作権',
			self::NDA->value => '秘密保持契約',
			self::ConfidentialityAgreement->value => '秘密保持契約',
			self::NonCompete->value => '競業避止契約',
			self::IPAssignment->value => '知的財産権譲渡書',

			// Reports/Records
			self::MeetingMinutes->value => '議事録',
			self::Report->value => '報告書',
			self::Record->value => '記録',
			self::Log->value => 'ログ',
			self::Transcript->value => '書き起こし',
			self::Verification->value => '検証',
			self::Certification->value => '認証',

			// Proof/Evidence
			self::ProofOfPayment->value => '支払証明',
			self::ProofOfAddress->value => '住所証明',
			self::ProofOfIdentity->value => '身分証明',
			self::ProofOfInsurance->value => '保険証明',
			self::ProofOfEmployment->value => '雇用証明',
			self::Evidence->value => '証拠',

			// Other
			self::Other->value => 'その他',
			self::Miscellaneous->value => 'その他',
			self::Reference->value => '参照',
			self::SupportingDocument->value => '補足資料',
			self::Backup->value => 'バックアップ',
			self::Draft->value => '草案',
			self::Template->value => 'テンプレート',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => 'Kontrakt',
			self::Agreement->value => 'Aftale',
			self::Addendum->value => 'Tillæg',
			self::Amendment->value => 'Ændring',
			self::Schedule->value => 'Bilag',
			self::Annex->value => 'Bilag',
			self::Exhibit->value => 'Bilag',
			self::Appendix->value => 'Bilag',

			// Financial
			self::Invoice->value => 'Faktura',
			self::Receipt->value => 'Kvittering',
			self::Quote->value => 'Tilbud',
			self::Proposal->value => 'Forslag',
			self::PurchaseOrder->value => 'Indkøbsordre',
			self::PaymentConfirmation->value => 'Betalingsbekræftelse',
			self::BankStatement->value => 'Kontoudtog',
			self::FinancialStatement->value => 'Årsregnskab',
			self::TaxDocument->value => 'Skattepapir',
			self::AuditReport->value => 'Revisionsrapport',

			// Legal Proceedings
			self::LegalCase->value => 'Sag',
			self::CourtDocument->value => 'Retspapir',
			self::Pleading->value => 'Sagsanlæg',
			self::Motion->value => 'Begæring',
			self::Deposition->value => 'Forklaring',
			self::Affidavit->value => 'Skriftlig erklæring',
			self::Subpoena->value => 'Stævning',
			self::Summons->value => 'Stævning',
			self::Judgment->value => 'Dom',
			self::Settlement->value => 'Forlig',

			// Corporate/Compliance
			self::CorporateResolution->value => 'Virksomhedsbeslutning',
			self::Bylaws->value => 'Vedtægter',
			self::ArticlesOfIncorporation->value => 'Stiftelsesdokument',
			self::ShareholderAgreement->value => 'Aktionæraftale',
			self::ComplianceDocument->value => 'Overholdelsesdokument',
			self::RegulatoryFiling->value => 'Regulatorisk indgivelse',
			self::License->value => 'Licens',
			self::Permit->value => 'Tilladelse',
			self::Certificate->value => 'Certifikat',

			// Property/Real Estate
			self::Deed->value => 'Ejendomspapir',
			self::Title->value => 'Ejendomstitel',
			self::Lease->value => 'Lejekontrakt',
			self::Mortgage->value => 'Pantebrev',
			self::Survey->value => 'Måling',
			self::Appraisal->value => 'Vurdering',
			self::InsurancePolicy->value => 'Forsikringspolice',
			self::PropertyDocument->value => 'Ejendomsdokument',

			// Identification/Personal
			self::Identification->value => 'Identifikation',
			self::Passport->value => 'Pas',
			self::DriverLicense->value => 'Kørekort',
			self::BirthCertificate->value => 'Fødselsattest',
			self::MarriageCertificate->value => 'Ægteskabsattest',
			self::PowerOfAttorney->value => 'Fuldmagt',
			self::Will->value => 'Testamente',
			self::TrustDocument->value => 'Tillidsdokument',

			// Communication/Correspondence
			self::Correspondence->value => 'Korrespondance',
			self::Email->value => 'E-mail',
			self::Letter->value => 'Brev',
			self::Memo->value => 'Notat',
			self::Notice->value => 'Meddelelse',
			self::Disclosure->value => 'Offentliggørelse',
			self::Waiver->value => 'Fravigelse',
			self::ConsentForm->value => 'Samtykkeerklæring',

			// Intellectual Property
			self::Patent->value => 'Patent',
			self::Trademark->value => 'Varemærke',
			self::Copyright->value => 'Ophavsret',
			self::NDA->value => 'Fortrolighedsaftale',
			self::ConfidentialityAgreement->value => 'Fortrolighedsaftale',
			self::NonCompete->value => 'Konkurrenceklausul',
			self::IPAssignment->value => 'IP-overdragelse',

			// Reports/Records
			self::MeetingMinutes->value => 'Referat',
			self::Report->value => 'Rapport',
			self::Record->value => 'Optegnelse',
			self::Log->value => 'Logbog',
			self::Transcript->value => 'Transskript',
			self::Verification->value => 'Verifikation',
			self::Certification->value => 'Certificering',

			// Proof/Evidence
			self::ProofOfPayment->value => 'Betalingsbevis',
			self::ProofOfAddress->value => 'Adressebevis',
			self::ProofOfIdentity->value => 'Identitetsbevis',
			self::ProofOfInsurance->value => 'Forsikringsbevis',
			self::ProofOfEmployment->value => 'Ansættelsesbevis',
			self::Evidence->value => 'Bevis',

			// Other
			self::Other->value => 'Andet',
			self::Miscellaneous->value => 'Diverse',
			self::Reference->value => 'Reference',
			self::SupportingDocument->value => 'Støttedokument',
			self::Backup->value => 'Sikkerhedskopi',
			self::Draft->value => 'Udkast',
			self::Template->value => 'Skabelon',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			// Legal/Contractual
			self::Contract->value => '合同',
			self::Agreement->value => '协议',
			self::Addendum->value => '补充协议',
			self::Amendment->value => '修正案',
			self::Schedule->value => '附表',
			self::Annex->value => '附件',
			self::Exhibit->value => '附件',
			self::Appendix->value => '附录',

			// Financial
			self::Invoice->value => '发票',
			self::Receipt->value => '收据',
			self::Quote->value => '报价单',
			self::Proposal->value => '提案',
			self::PurchaseOrder->value => '采购订单',
			self::PaymentConfirmation->value => '付款确认',
			self::BankStatement->value => '银行对账单',
			self::FinancialStatement->value => '财务报表',
			self::TaxDocument->value => '税务文件',
			self::AuditReport->value => '审计报告',

			// Legal Proceedings
			self::LegalCase->value => '法律案件',
			self::CourtDocument->value => '法庭文件',
			self::Pleading->value => '诉状',
			self::Motion->value => '动议',
			self::Deposition->value => '证词',
			self::Affidavit->value => '宣誓书',
			self::Subpoena->value => '传票',
			self::Summons->value => '传票',
			self::Judgment->value => '判决',
			self::Settlement->value => '和解协议',

			// Corporate/Compliance
			self::CorporateResolution->value => '公司决议',
			self::Bylaws->value => '公司章程',
			self::ArticlesOfIncorporation->value => '公司章程',
			self::ShareholderAgreement->value => '股东协议',
			self::ComplianceDocument->value => '合规文件',
			self::RegulatoryFiling->value => '监管备案',
			self::License->value => '许可证',
			self::Permit->value => '许可证',
			self::Certificate->value => '证书',

			// Property/Real Estate
			self::Deed->value => '地契',
			self::Title->value => '产权证',
			self::Lease->value => '租赁合同',
			self::Mortgage->value => '抵押贷款',
			self::Survey->value => '测量图',
			self::Appraisal->value => '评估报告',
			self::InsurancePolicy->value => '保险单',
			self::PropertyDocument->value => '房产文件',

			// Identification/Personal
			self::Identification->value => '身份证明',
			self::Passport->value => '护照',
			self::DriverLicense->value => '驾驶证',
			self::BirthCertificate->value => '出生证明',
			self::MarriageCertificate->value => '结婚证',
			self::PowerOfAttorney->value => '授权书',
			self::Will->value => '遗嘱',
			self::TrustDocument->value => '信托文件',

			// Communication/Correspondence
			self::Correspondence->value => '信函',
			self::Email->value => '电子邮件',
			self::Letter->value => '信件',
			self::Memo->value => '备忘录',
			self::Notice->value => '通知',
			self::Disclosure->value => '披露',
			self::Waiver->value => '放弃声明',
			self::ConsentForm->value => '同意书',

			// Intellectual Property
			self::Patent->value => '专利',
			self::Trademark->value => '商标',
			self::Copyright->value => '版权',
			self::NDA->value => '保密协议',
			self::ConfidentialityAgreement->value => '保密协议',
			self::NonCompete->value => '竞业禁止协议',
			self::IPAssignment->value => '知识产权转让',

			// Reports/Records
			self::MeetingMinutes->value => '会议纪要',
			self::Report->value => '报告',
			self::Record->value => '记录',
			self::Log->value => '日志',
			self::Transcript->value => '转录',
			self::Verification->value => '验证',
			self::Certification->value => '认证',

			// Proof/Evidence
			self::ProofOfPayment->value => '付款证明',
			self::ProofOfAddress->value => '地址证明',
			self::ProofOfIdentity->value => '身份证明',
			self::ProofOfInsurance->value => '保险证明',
			self::ProofOfEmployment->value => '雇佣证明',
			self::Evidence->value => '证据',

			// Other
			self::Other->value => '其他',
			self::Miscellaneous->value => '杂项',
			self::Reference->value => '参考',
			self::SupportingDocument->value => '支持文件',
			self::Backup->value => '备份',
			self::Draft->value => '草稿',
			self::Template->value => '模板',
		];
	}
}
