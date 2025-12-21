<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum ContactRole: string
{
	// Finance & Accounting
	case AccountManager = 'account_manager';
	case BillingContact = 'billing_contact';
	case PaymentContact = 'payment_contact';
	case InvoiceContact = 'invoice_contact';
	case CreditController = 'credit_controller';
	case FinancialAdvisor = 'financial_advisor';
	case TaxContact = 'tax_contact';
	case AuditContact = 'audit_contact';
	case BudgetManager = 'budget_manager';
	case CostController = 'cost_controller';

		// CRM & Sales
	case PrimaryContact = 'primary_contact';
	case SalesContact = 'sales_contact';
	case KeyAccountManager = 'key_account_manager';
	case LeadContact = 'lead_contact';
	case OpportunityOwner = 'opportunity_owner';
	case CustomerSuccessManager = 'customer_success_manager';
	case SupportContact = 'support_contact';
	case RenewalContact = 'renewal_contact';
	case UpsellContact = 'upsell_contact';
	case ReferralContact = 'referral_contact';

		// HRM
	case HRManager = 'hr_manager';
	case Recruiter = 'recruiter';
	case PayrollContact = 'payroll_contact';
	case BenefitsAdmin = 'benefits_admin';
	case TrainingCoordinator = 'training_coordinator';
	case PerformanceManager = 'performance_manager';
	case OnboardingContact = 'onboarding_contact';
	case OffboardingContact = 'offboarding_contact';
	case ComplianceOfficer = 'compliance_officer';
	case SafetyOfficer = 'safety_officer';

		// IT Support
	case SystemAdmin = 'system_admin';
	case ITManager = 'it_manager';
	case HelpDeskContact = 'help_desk_contact';
	case NetworkAdmin = 'network_admin';
	case SecurityOfficer = 'security_officer';
	case DatabaseAdmin = 'database_admin';
	case DevelopmentLead = 'development_lead';
	case TechnicalSupport = 'technical_support';
	case InfrastructureManager = 'infrastructure_manager';
	case ProcurementContact = 'procurement_contact';

		// Project Management
	case ProjectManager = 'project_manager';
	case TeamLead = 'team_lead';
	case TeamMember = 'team_member';
	case Stakeholder = 'stakeholder';
	case Sponsor = 'sponsor';
	case ProductOwner = 'product_owner';
	case ScrumMaster = 'scrum_master';
	case DeliveryManager = 'delivery_manager';
	case QualityAssurance = 'quality_assurance';
	case ResourceManager = 'resource_manager';

		// Administrative & Operations
	case OfficeManager = 'office_manager';
	case ExecutiveAssistant = 'executive_assistant';
	case FacilityManager = 'facility_manager';
	case OperationsManager = 'operations_manager';
	case LogisticsContact = 'logistics_contact';
	case ProcurementManager = 'procurement_manager';
	case InventoryManager = 'inventory_manager';
	case SupplyChainContact = 'supply_chain_contact';
	case VendorManager = 'vendor_manager';
	case LegalContact = 'legal_contact';

		// Personal & Emergency
	case EmergencyContact = 'emergency_contact';
	case Spouse = 'spouse';
	case Partner = 'partner';
	case Fiancee = 'fiancee';
	case Wife = 'wife';
	case Husband = 'husband';
	case Parent = 'parent';
	case Mother = 'mother';
	case Father = 'father';
	case Guardian = 'guardian';
	case Child = 'child';
	case Son = 'son';
	case Daughter = 'daughter';
	case Sibling = 'sibling';
	case Brother = 'brother';
	case Sister = 'sister';
	case Friend = 'friend';
	case Neighbor = 'neighbor';
	case Lawyer = 'lawyer';
	case Doctor = 'doctor';
	case Notary = 'notary';
	case Executor = 'executor';
	case PowerOfAttorney = 'power_of_attorney';

		// Medical & Care
	case PrimaryPhysician = 'primary_physician';
	case Specialist = 'specialist';
	case Dentist = 'dentist';
	case Therapist = 'therapist';
	case Pharmacist = 'pharmacist';
	case Caregiver = 'caregiver';
	case HomeCareAide = 'home_care_aide';
	case NursingContact = 'nursing_contact';

		// Education
	case Teacher = 'teacher';
	case Professor = 'professor';
	case Principal = 'principal';
	case Counselor = 'counselor';
	case Tutor = 'tutor';
	case Coach = 'coach';

		// Other Business
	case Consultant = 'consultant';
	case Contractor = 'contractor';
	case Freelancer = 'freelancer';
	case BusinessPartner = 'business_partner';
	case Investor = 'investor';
	case BoardMember = 'board_member';
	case Shareholder = 'shareholder';
	case Auditor = 'auditor';
	case Banker = 'banker';
	case InsuranceAgent = 'insurance_agent';
	case RealEstateAgent = 'real_estate_agent';

		// Default/Catch-all
	case Other = 'other';
	case Unspecified = 'unspecified';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Unspecified;

		$normalizedValue = preg_replace('/[^a-z0-9]/', '', strtolower(trim($value ?? '')));
		return match ($normalizedValue) {
			// Finance
			'accountmanager', 'accountmanager' => self::AccountManager,
			'billingcontact', 'billing' => self::BillingContact,
			'paymentcontact', 'payment' => self::PaymentContact,
			'invoicecontact', 'invoice' => self::InvoiceContact,
			'creditcontroller', 'credit' => self::CreditController,
			'financialadvisor', 'financial' => self::FinancialAdvisor,
			'taxcontact', 'tax' => self::TaxContact,
			'auditcontact', 'auditorcontact' => self::AuditContact,
			'budgetmanager', 'budget' => self::BudgetManager,
			'costcontroller', 'costcontrol' => self::CostController,

			// CRM
			'primarycontact', 'maincontact' => self::PrimaryContact,
			'salescontact', 'salesrep' => self::SalesContact,
			'keyaccountmanager', 'kam' => self::KeyAccountManager,
			'leadcontact', 'lead' => self::LeadContact,
			'opportunityowner', 'opportunity' => self::OpportunityOwner,
			'customersuccessmanager', 'csm' => self::CustomerSuccessManager,
			'supportcontact', 'support' => self::SupportContact,
			'renewalcontact', 'renewal' => self::RenewalContact,
			'upsellcontact', 'upsell' => self::UpsellContact,
			'referralcontact', 'referral' => self::ReferralContact,

			// HRM
			'hrmanager', 'humanresourcesmanager' => self::HRManager,
			'recruiter', 'recruitment' => self::Recruiter,
			'payrollcontact', 'payroll' => self::PayrollContact,
			'benefitsadmin', 'benefitsadministrator' => self::BenefitsAdmin,
			'trainingcoordinator', 'training' => self::TrainingCoordinator,
			'performancemanager', 'performance' => self::PerformanceManager,
			'onboardingcontact', 'onboarding' => self::OnboardingContact,
			'offboardingcontact', 'offboarding' => self::OffboardingContact,
			'complianceofficer', 'compliance' => self::ComplianceOfficer,
			'safetyofficer', 'safety' => self::SafetyOfficer,

			// IT
			'systemadmin', 'sysadmin' => self::SystemAdmin,
			'itmanager', 'informationtechnologymanager' => self::ITManager,
			'helpdeskcontact', 'helpdesk' => self::HelpDeskContact,
			'networkadmin', 'networkadministrator' => self::NetworkAdmin,
			'securityofficer', 'security' => self::SecurityOfficer,
			'databaseadmin', 'dba' => self::DatabaseAdmin,
			'developmentlead', 'devlead' => self::DevelopmentLead,
			'technicalsupport', 'techsupport' => self::TechnicalSupport,
			'infrastructuremanager', 'infrastructure' => self::InfrastructureManager,
			'procurementcontact', 'itprocurement' => self::ProcurementContact,

			// Project Management
			'projectmanager', 'pm' => self::ProjectManager,
			'teamlead', 'teamleader' => self::TeamLead,
			'teammember', 'member' => self::TeamMember,
			'stakeholder' => self::Stakeholder,
			'sponsor' => self::Sponsor,
			'productowner', 'productmanager' => self::ProductOwner,
			'scrummaster', 'agilecoach' => self::ScrumMaster,
			'deliverymanager', 'deliverylead' => self::DeliveryManager,
			'qualityassurance', 'qa', 'tester' => self::QualityAssurance,
			'resourcemanager', 'resourceplanner' => self::ResourceManager,

			// Administrative
			'officemanager', 'officeadmin' => self::OfficeManager,
			'executiveassistant', 'ea', 'administrativeassistant' => self::ExecutiveAssistant,
			'facilitymanager', 'facilitiesmanager' => self::FacilityManager,
			'operationsmanager', 'opsmanager' => self::OperationsManager,
			'logisticscontact', 'logistics' => self::LogisticsContact,
			'procurementmanager', 'purchasingmanager' => self::ProcurementManager,
			'inventorymanager', 'stockmanager' => self::InventoryManager,
			'supplychaincontact', 'supplychain' => self::SupplyChainContact,
			'vendormanager', 'vendormanagement' => self::VendorManager,
			'legalcontact', 'legal' => self::LegalContact,

			// Personal
			'emergencycontact', 'emergency' => self::EmergencyContact,
			'spouse', 'wifehusband' => self::Spouse,
			'partner', 'lifepartner', 'domesticpartner' => self::Partner,
			'fiancee', 'fiance', 'betrothed' => self::Fiancee,
			'wife', 'spousewife' => self::Wife,
			'husband', 'spousehusband' => self::Husband,
			'parent', 'fathermother' => self::Parent,
			'mother', 'mom', 'mum' => self::Mother,
			'father', 'dad' => self::Father,
			'guardian', 'legalguardian' => self::Guardian,
			'child', 'kid', 'offspring' => self::Child,
			'son', 'boy' => self::Son,
			'daughter', 'girl' => self::Daughter,
			'sibling', 'brothersister' => self::Sibling,
			'brother' => self::Brother,
			'sister' => self::Sister,
			'friend', 'buddy', 'pal' => self::Friend,
			'neighbor', 'neighbour' => self::Neighbor,
			'lawyer', 'attorney' => self::Lawyer,
			'doctor', 'physician', 'md' => self::Doctor,
			'notary', 'notarypublic' => self::Notary,
			'executor', 'estateexecutor' => self::Executor,
			'powerofattorney', 'poa', 'attorneyinfact' => self::PowerOfAttorney,

			// Medical
			'primaryphysician', 'primarycarephysician', 'pcp' => self::PrimaryPhysician,
			'specialist', 'medicalspecialist' => self::Specialist,
			'dentist', 'dentaldoctor' => self::Dentist,
			'therapist', 'psychotherapist' => self::Therapist,
			'pharmacist', 'pharmacy' => self::Pharmacist,
			'caregiver', 'caretaker' => self::Caregiver,
			'homecareaide', 'homehealthaide' => self::HomeCareAide,
			'nursingcontact', 'nurse' => self::NursingContact,

			// Education
			'teacher', 'educator' => self::Teacher,
			'professor', 'lecturer' => self::Professor,
			'principal', 'headmaster', 'headmistress' => self::Principal,
			'counselor', 'guidancecounselor' => self::Counselor,
			'tutor', 'mentor' => self::Tutor,
			'coach', 'trainer' => self::Coach,

			// Other Business
			'consultant' => self::Consultant,
			'contractor', 'independentcontractor' => self::Contractor,
			'freelancer', 'freelance' => self::Freelancer,
			'businesspartner', 'partnerbusiness' => self::BusinessPartner,
			'investor', 'shareholder' => self::Investor,
			'boardmember', 'director' => self::BoardMember,
			'shareholder', 'stockholder' => self::Shareholder,
			'auditor', 'externalauditor' => self::Auditor,
			'banker', 'bankrepresentative' => self::Banker,
			'insuranceagent', 'insurancerep' => self::InsuranceAgent,
			'realestateagent', 'realtor' => self::RealEstateAgent,

			// Default
			'other', 'custom', 'miscellaneous' => self::Other,
			'unspecified', 'unknown', 'notset', 'none' => self::Unspecified,

			default => self::Unspecified,
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

	/**
	 * Get the label for a specific relationship role in the specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? '';
	}

	/**
	 * Get the category of this relationship role
	 */
	public function getCategory(): string
	{
		return match ($this) {
			// Finance
			self::AccountManager, self::BillingContact, self::PaymentContact,
			self::InvoiceContact, self::CreditController, self::FinancialAdvisor,
			self::TaxContact, self::AuditContact, self::BudgetManager, self::CostController => 'finance',

			// CRM
			self::PrimaryContact, self::SalesContact, self::KeyAccountManager,
			self::LeadContact, self::OpportunityOwner, self::CustomerSuccessManager,
			self::SupportContact, self::RenewalContact, self::UpsellContact, self::ReferralContact => 'crm',

			// HRM
			self::HRManager, self::Recruiter, self::PayrollContact, self::BenefitsAdmin,
			self::TrainingCoordinator, self::PerformanceManager, self::OnboardingContact,
			self::OffboardingContact, self::ComplianceOfficer, self::SafetyOfficer => 'hrm',

			// IT
			self::SystemAdmin, self::ITManager, self::HelpDeskContact, self::NetworkAdmin,
			self::SecurityOfficer, self::DatabaseAdmin, self::DevelopmentLead,
			self::TechnicalSupport, self::InfrastructureManager, self::ProcurementContact => 'it',

			// Project Management
			self::ProjectManager, self::TeamLead, self::TeamMember, self::Stakeholder,
			self::Sponsor, self::ProductOwner, self::ScrumMaster, self::DeliveryManager,
			self::QualityAssurance, self::ResourceManager => 'project_management',

			// Administrative
			self::OfficeManager, self::ExecutiveAssistant, self::FacilityManager,
			self::OperationsManager, self::LogisticsContact, self::ProcurementManager,
			self::InventoryManager, self::SupplyChainContact, self::VendorManager,
			self::LegalContact => 'administrative',

			// Personal
			self::EmergencyContact, self::Spouse, self::Partner, self::Fiancee,
			self::Wife, self::Husband, self::Parent, self::Mother, self::Father,
			self::Guardian, self::Child, self::Son, self::Daughter, self::Sibling,
			self::Brother, self::Sister, self::Friend, self::Neighbor => 'personal_family',

			// Professional Services
			self::Lawyer, self::Doctor, self::Notary, self::Executor, self::PowerOfAttorney,
			self::PrimaryPhysician, self::Specialist, self::Dentist, self::Therapist,
			self::Pharmacist, self::Caregiver, self::HomeCareAide, self::NursingContact,
			self::Teacher, self::Professor, self::Principal, self::Counselor,
			self::Tutor, self::Coach => 'professional_services',

			// Business
			self::Consultant, self::Contractor, self::Freelancer, self::BusinessPartner,
			self::Investor, self::BoardMember, self::Shareholder, self::Auditor,
			self::Banker, self::InsuranceAgent, self::RealEstateAgent => 'business',

			// Default
			self::Other, self::Unspecified => 'general',
		};
	}

	/**
	 * Get the icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this->getCategory()) {
			'finance' => 'currency-dollar',
			'crm' => 'user-group',
			'hrm' => 'briefcase',
			'it' => 'cpu-chip',
			'project_management' => 'clipboard-document-check',
			'administrative' => 'building-office',
			'personal_family' => 'heart',
			'professional_services' => 'academic-cap',
			'business' => 'handshake',
			default => 'user',
		};
	}

	/**
	 * Get the color for UI display
	 */
	public function getColor(): string
	{
		return match ($this->getCategory()) {
			'finance' => '#10b981', // green
			'crm' => '#3b82f6', // blue
			'hrm' => '#8b5cf6', // purple
			'it' => '#f59e0b', // amber
			'project_management' => '#ef4444', // red
			'administrative' => '#6b7280', // gray
			'personal_family' => '#ec4899', // pink
			'professional_services' => '#0ea5e9', // sky blue
			'business' => '#84cc16', // lime
			default => '#9ca3af', // cool gray
		};
	}

	/**
	 * Check if this is a business role
	 */
	public function isBusinessRole(): bool
	{
		$businessCategories = ['finance', 'crm', 'hrm', 'it', 'project_management', 'administrative', 'business'];
		return in_array($this->getCategory(), $businessCategories);
	}

	/**
	 * Check if this is a personal role
	 */
	public function isPersonalRole(): bool
	{
		$personalCategories = ['personal_family', 'professional_services'];
		return in_array($this->getCategory(), $personalCategories);
	}

	/**
	 * Get the priority level for this role
	 */
	public function getPriority(): int
	{
		return match ($this) {
			// High priority
			self::EmergencyContact, self::PrimaryContact, self::AccountManager => 1,

			// Medium priority
			self::Spouse, self::Parent, self::Doctor, self::Lawyer,
			self::HRManager, self::ITManager, self::ProjectManager => 2,

			// Normal priority
			self::BillingContact, self::SupportContact, self::PayrollContact,
			self::TeamLead, self::OfficeManager, self::Friend, self::Neighbor => 3,

			// Low priority
			self::Other, self::Unspecified, self::Shareholder, self::Investor => 4,

			default => 3,
		};
	}

	/**
	 * Get roles by category for filtering
	 */
	public static function getRolesByCategory(string $category): array
	{
		return array_filter(
			self::cases(),
			fn($role) => $role->getCategory() === $category
		);
	}

	/**
	 * Get all categories with their display names
	 */
	public static function getCategories($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		return match ($lang) {
			'pt-br', 'pt' => [
				'finance' => 'Financeiro',
				'crm' => 'CRM',
				'hrm' => 'Recursos Humanos',
				'it' => 'TI',
				'project_management' => 'Gestão de Projetos',
				'administrative' => 'Administrativo',
				'personal_family' => 'Pessoal & Familiar',
				'professional_services' => 'Serviços Profissionais',
				'business' => 'Negócios',
				'general' => 'Geral',
			],
			default => [
				'finance' => 'Finance',
				'crm' => 'CRM',
				'hrm' => 'Human Resources',
				'it' => 'IT',
				'project_management' => 'Project Management',
				'administrative' => 'Administrative',
				'personal_family' => 'Personal & Family',
				'professional_services' => 'Professional Services',
				'business' => 'Business',
				'general' => 'General',
			],
		};
	}

	/**
	 * Get a short description of the role
	 */
	public function getDescription($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$descriptions = self::descriptions($lang);
		return $descriptions[$this->value] ?? '';
	}

	public static function descriptions($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		return match ($lang) {
			'pt-br', 'pt' => self::descriptionsPtBr(),
			'es', 'es-es' => self::descriptionsEs(),
			default => self::descriptionsEn(),
		};
	}

	public static function labelsEn(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Account Manager',
			self::BillingContact->value => 'Billing Contact',
			self::PaymentContact->value => 'Payment Contact',
			self::InvoiceContact->value => 'Invoice Contact',
			self::CreditController->value => 'Credit Controller',
			self::FinancialAdvisor->value => 'Financial Advisor',
			self::TaxContact->value => 'Tax Contact',
			self::AuditContact->value => 'Audit Contact',
			self::BudgetManager->value => 'Budget Manager',
			self::CostController->value => 'Cost Controller',

			// CRM
			self::PrimaryContact->value => 'Primary Contact',
			self::SalesContact->value => 'Sales Contact',
			self::KeyAccountManager->value => 'Key Account Manager',
			self::LeadContact->value => 'Lead Contact',
			self::OpportunityOwner->value => 'Opportunity Owner',
			self::CustomerSuccessManager->value => 'Customer Success Manager',
			self::SupportContact->value => 'Support Contact',
			self::RenewalContact->value => 'Renewal Contact',
			self::UpsellContact->value => 'Upsell Contact',
			self::ReferralContact->value => 'Referral Contact',

			// HRM
			self::HRManager->value => 'HR Manager',
			self::Recruiter->value => 'Recruiter',
			self::PayrollContact->value => 'Payroll Contact',
			self::BenefitsAdmin->value => 'Benefits Administrator',
			self::TrainingCoordinator->value => 'Training Coordinator',
			self::PerformanceManager->value => 'Performance Manager',
			self::OnboardingContact->value => 'Onboarding Contact',
			self::OffboardingContact->value => 'Offboarding Contact',
			self::ComplianceOfficer->value => 'Compliance Officer',
			self::SafetyOfficer->value => 'Safety Officer',

			// IT
			self::SystemAdmin->value => 'System Administrator',
			self::ITManager->value => 'IT Manager',
			self::HelpDeskContact->value => 'Help Desk Contact',
			self::NetworkAdmin->value => 'Network Administrator',
			self::SecurityOfficer->value => 'Security Officer',
			self::DatabaseAdmin->value => 'Database Administrator',
			self::DevelopmentLead->value => 'Development Lead',
			self::TechnicalSupport->value => 'Technical Support',
			self::InfrastructureManager->value => 'Infrastructure Manager',
			self::ProcurementContact->value => 'IT Procurement Contact',

			// Project Management
			self::ProjectManager->value => 'Project Manager',
			self::TeamLead->value => 'Team Lead',
			self::TeamMember->value => 'Team Member',
			self::Stakeholder->value => 'Stakeholder',
			self::Sponsor->value => 'Sponsor',
			self::ProductOwner->value => 'Product Owner',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'Delivery Manager',
			self::QualityAssurance->value => 'Quality Assurance',
			self::ResourceManager->value => 'Resource Manager',

			// Administrative
			self::OfficeManager->value => 'Office Manager',
			self::ExecutiveAssistant->value => 'Executive Assistant',
			self::FacilityManager->value => 'Facility Manager',
			self::OperationsManager->value => 'Operations Manager',
			self::LogisticsContact->value => 'Logistics Contact',
			self::ProcurementManager->value => 'Procurement Manager',
			self::InventoryManager->value => 'Inventory Manager',
			self::SupplyChainContact->value => 'Supply Chain Contact',
			self::VendorManager->value => 'Vendor Manager',
			self::LegalContact->value => 'Legal Contact',

			// Personal
			self::EmergencyContact->value => 'Emergency Contact',
			self::Spouse->value => 'Spouse',
			self::Partner->value => 'Partner',
			self::Fiancee->value => 'Fiancée',
			self::Wife->value => 'Wife',
			self::Husband->value => 'Husband',
			self::Parent->value => 'Parent',
			self::Mother->value => 'Mother',
			self::Father->value => 'Father',
			self::Guardian->value => 'Guardian',
			self::Child->value => 'Child',
			self::Son->value => 'Son',
			self::Daughter->value => 'Daughter',
			self::Sibling->value => 'Sibling',
			self::Brother->value => 'Brother',
			self::Sister->value => 'Sister',
			self::Friend->value => 'Friend',
			self::Neighbor->value => 'Neighbor',
			self::Lawyer->value => 'Lawyer',
			self::Doctor->value => 'Doctor',
			self::Notary->value => 'Notary',
			self::Executor->value => 'Executor',
			self::PowerOfAttorney->value => 'Power of Attorney',

			// Medical
			self::PrimaryPhysician->value => 'Primary Physician',
			self::Specialist->value => 'Specialist',
			self::Dentist->value => 'Dentist',
			self::Therapist->value => 'Therapist',
			self::Pharmacist->value => 'Pharmacist',
			self::Caregiver->value => 'Caregiver',
			self::HomeCareAide->value => 'Home Care Aide',
			self::NursingContact->value => 'Nursing Contact',

			// Education
			self::Teacher->value => 'Teacher',
			self::Professor->value => 'Professor',
			self::Principal->value => 'Principal',
			self::Counselor->value => 'Counselor',
			self::Tutor->value => 'Tutor',
			self::Coach->value => 'Coach',

			// Other Business
			self::Consultant->value => 'Consultant',
			self::Contractor->value => 'Contractor',
			self::Freelancer->value => 'Freelancer',
			self::BusinessPartner->value => 'Business Partner',
			self::Investor->value => 'Investor',
			self::BoardMember->value => 'Board Member',
			self::Shareholder->value => 'Shareholder',
			self::Auditor->value => 'Auditor',
			self::Banker->value => 'Banker',
			self::InsuranceAgent->value => 'Insurance Agent',
			self::RealEstateAgent->value => 'Real Estate Agent',

			// Default
			self::Other->value => 'Other',
			self::Unspecified->value => 'Unspecified',
		];
	}

	public static function labelsPtBr(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Gerente de Conta',
			self::BillingContact->value => 'Contato de Cobrança',
			self::PaymentContact->value => 'Contato de Pagamento',
			self::InvoiceContact->value => 'Contato de Faturamento',
			self::CreditController->value => 'Controlador de Crédito',
			self::FinancialAdvisor->value => 'Consultor Financeiro',
			self::TaxContact->value => 'Contato Fiscal',
			self::AuditContact->value => 'Contato de Auditoria',
			self::BudgetManager->value => 'Gerente de Orçamento',
			self::CostController->value => 'Controlador de Custos',

			// CRM
			self::PrimaryContact->value => 'Contato Principal',
			self::SalesContact->value => 'Contato Comercial',
			self::KeyAccountManager->value => 'Gerente de Conta-Chave',
			self::LeadContact->value => 'Contato de Lead',
			self::OpportunityOwner->value => 'Responsável pela Oportunidade',
			self::CustomerSuccessManager->value => 'Gerente de Sucesso do Cliente',
			self::SupportContact->value => 'Contato de Suporte',
			self::RenewalContact->value => 'Contato de Renovação',
			self::UpsellContact->value => 'Contato de Venda Adicional',
			self::ReferralContact->value => 'Contato de Indicação',

			// HRM
			self::HRManager->value => 'Gerente de RH',
			self::Recruiter->value => 'Recrutador',
			self::PayrollContact->value => 'Contato de Folha de Pagamento',
			self::BenefitsAdmin->value => 'Administrador de Benefícios',
			self::TrainingCoordinator->value => 'Coordenador de Treinamento',
			self::PerformanceManager->value => 'Gerente de Performance',
			self::OnboardingContact->value => 'Contato de Integração',
			self::OffboardingContact->value => 'Contato de Desligamento',
			self::ComplianceOfficer->value => 'Oficial de Conformidade',
			self::SafetyOfficer->value => 'Oficial de Segurança',

			// IT
			self::SystemAdmin->value => 'Administrador do Sistema',
			self::ITManager->value => 'Gerente de TI',
			self::HelpDeskContact->value => 'Contato do Help Desk',
			self::NetworkAdmin->value => 'Administrador de Rede',
			self::SecurityOfficer->value => 'Oficial de Segurança',
			self::DatabaseAdmin->value => 'Administrador de Banco de Dados',
			self::DevelopmentLead->value => 'Líder de Desenvolvimento',
			self::TechnicalSupport->value => 'Suporte Técnico',
			self::InfrastructureManager->value => 'Gerente de Infraestrutura',
			self::ProcurementContact->value => 'Contato de Aquisições de TI',

			// Project Management
			self::ProjectManager->value => 'Gerente de Projeto',
			self::TeamLead->value => 'Líder de Equipe',
			self::TeamMember->value => 'Membro da Equipe',
			self::Stakeholder->value => 'Parte Interessada',
			self::Sponsor->value => 'Patrocinador',
			self::ProductOwner->value => 'Proprietário do Produto',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'Gerente de Entrega',
			self::QualityAssurance->value => 'Garantia de Qualidade',
			self::ResourceManager->value => 'Gerente de Recursos',

			// Administrative
			self::OfficeManager->value => 'Gerente de Escritório',
			self::ExecutiveAssistant->value => 'Assistente Executivo',
			self::FacilityManager->value => 'Gerente de Instalações',
			self::OperationsManager->value => 'Gerente de Operações',
			self::LogisticsContact->value => 'Contato de Logística',
			self::ProcurementManager->value => 'Gerente de Compras',
			self::InventoryManager->value => 'Gerente de Estoque',
			self::SupplyChainContact->value => 'Contato da Cadeia de Suprimentos',
			self::VendorManager->value => 'Gerente de Fornecedores',
			self::LegalContact->value => 'Contato Jurídico',

			// Personal
			self::EmergencyContact->value => 'Contato de Emergência',
			self::Spouse->value => 'Cônjuge',
			self::Partner->value => 'Parceiro',
			self::Fiancee->value => 'Noiva',
			self::Wife->value => 'Esposa',
			self::Husband->value => 'Marido',
			self::Parent->value => 'Pai/Mãe',
			self::Mother->value => 'Mãe',
			self::Father->value => 'Pai',
			self::Guardian->value => 'Guardião',
			self::Child->value => 'Filho(a)',
			self::Son->value => 'Filho',
			self::Daughter->value => 'Filha',
			self::Sibling->value => 'Irmão(ã)',
			self::Brother->value => 'Irmão',
			self::Sister->value => 'Irmã',
			self::Friend->value => 'Amigo(a)',
			self::Neighbor->value => 'Vizinho(a)',
			self::Lawyer->value => 'Advogado(a)',
			self::Doctor->value => 'Médico(a)',
			self::Notary->value => 'Tabelião',
			self::Executor->value => 'Executor',
			self::PowerOfAttorney->value => 'Procurador',

			// Medical
			self::PrimaryPhysician->value => 'Médico de Família',
			self::Specialist->value => 'Especialista',
			self::Dentist->value => 'Dentista',
			self::Therapist->value => 'Terapeuta',
			self::Pharmacist->value => 'Farmacêutico',
			self::Caregiver->value => 'Cuidador',
			self::HomeCareAide->value => 'Auxiliar de Cuidados Domésticos',
			self::NursingContact->value => 'Contato de Enfermagem',

			// Education
			self::Teacher->value => 'Professor(a)',
			self::Professor->value => 'Professor(a) Universitário',
			self::Principal->value => 'Diretor(a)',
			self::Counselor->value => 'Conselheiro(a)',
			self::Tutor->value => 'Tutor',
			self::Coach->value => 'Treinador',

			// Other Business
			self::Consultant->value => 'Consultor',
			self::Contractor->value => 'Contratante',
			self::Freelancer->value => 'Freelancer',
			self::BusinessPartner->value => 'Parceiro de Negócios',
			self::Investor->value => 'Investidor',
			self::BoardMember->value => 'Membro do Conselho',
			self::Shareholder->value => 'Acionista',
			self::Auditor->value => 'Auditor',
			self::Banker->value => 'Banqueiro',
			self::InsuranceAgent->value => 'Corretor de Seguros',
			self::RealEstateAgent->value => 'Corretor de Imóveis',

			// Default
			self::Other->value => 'Outro',
			self::Unspecified->value => 'Não Especificado',
		];
	}

	public static function labelsEs(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Gerente de Cuenta',
			self::BillingContact->value => 'Contacto de Facturación',
			self::PaymentContact->value => 'Contacto de Pago',
			self::InvoiceContact->value => 'Contacto de Factura',
			self::CreditController->value => 'Controlador de Crédito',
			self::FinancialAdvisor->value => 'Asesor Financiero',
			self::TaxContact->value => 'Contacto Fiscal',
			self::AuditContact->value => 'Contacto de Auditoría',
			self::BudgetManager->value => 'Gerente de Presupuesto',
			self::CostController->value => 'Controlador de Costos',

			// CRM
			self::PrimaryContact->value => 'Contacto Principal',
			self::SalesContact->value => 'Contacto Comercial',
			self::KeyAccountManager->value => 'Gerente de Cuentas Clave',
			self::LeadContact->value => 'Contacto de Lead',
			self::OpportunityOwner->value => 'Responsable de Oportunidad',
			self::CustomerSuccessManager->value => 'Gerente de Éxito del Cliente',
			self::SupportContact->value => 'Contacto de Soporte',
			self::RenewalContact->value => 'Contacto de Renovación',
			self::UpsellContact->value => 'Contacto de Venta Adicional',
			self::ReferralContact->value => 'Contacto de Referencia',

			// HRM
			self::HRManager->value => 'Gerente de RRHH',
			self::Recruiter->value => 'Reclutador',
			self::PayrollContact->value => 'Contacto de Nómina',
			self::BenefitsAdmin->value => 'Administrador de Beneficios',
			self::TrainingCoordinator->value => 'Coordinador de Capacitación',
			self::PerformanceManager->value => 'Gerente de Rendimiento',
			self::OnboardingContact->value => 'Contacto de Incorporación',
			self::OffboardingContact->value => 'Contacto de Desvinculación',
			self::ComplianceOfficer->value => 'Oficial de Cumplimiento',
			self::SafetyOfficer->value => 'Oficial de Seguridad',

			// IT
			self::SystemAdmin->value => 'Administrador del Sistema',
			self::ITManager->value => 'Gerente de TI',
			self::HelpDeskContact->value => 'Contacto del Servicio de Ayuda',
			self::NetworkAdmin->value => 'Administrador de Red',
			self::SecurityOfficer->value => 'Oficial de Seguridad',
			self::DatabaseAdmin->value => 'Administrador de Base de Datos',
			self::DevelopmentLead->value => 'Líder de Desarrollo',
			self::TechnicalSupport->value => 'Soporte Técnico',
			self::InfrastructureManager->value => 'Gerente de Infraestructura',
			self::ProcurementContact->value => 'Contacto de Adquisiciones de TI',

			// Project Management
			self::ProjectManager->value => 'Gerente de Proyecto',
			self::TeamLead->value => 'Líder de Equipo',
			self::TeamMember->value => 'Miembro del Equipo',
			self::Stakeholder->value => 'Parte Interesada',
			self::Sponsor->value => 'Patrocinador',
			self::ProductOwner->value => 'Propietario del Producto',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'Gerente de Entrega',
			self::QualityAssurance->value => 'Garantía de Calidad',
			self::ResourceManager->value => 'Gerente de Recursos',

			// Administrative
			self::OfficeManager->value => 'Gerente de Oficina',
			self::ExecutiveAssistant->value => 'Asistente Ejecutivo',
			self::FacilityManager->value => 'Gerente de Instalaciones',
			self::OperationsManager->value => 'Gerente de Operaciones',
			self::LogisticsContact->value => 'Contacto de Logística',
			self::ProcurementManager->value => 'Gerente de Compras',
			self::InventoryManager->value => 'Gerente de Inventario',
			self::SupplyChainContact->value => 'Contacto de Cadena de Suministro',
			self::VendorManager->value => 'Gerente de Proveedores',
			self::LegalContact->value => 'Contacto Legal',

			// Personal
			self::EmergencyContact->value => 'Contacto de Emergencia',
			self::Spouse->value => 'Cónyuge',
			self::Partner->value => 'Pareja',
			self::Fiancee->value => 'Prometida',
			self::Wife->value => 'Esposa',
			self::Husband->value => 'Esposo',
			self::Parent->value => 'Padre/Madre',
			self::Mother->value => 'Madre',
			self::Father->value => 'Padre',
			self::Guardian->value => 'Tutor',
			self::Child->value => 'Hijo(a)',
			self::Son->value => 'Hijo',
			self::Daughter->value => 'Hija',
			self::Sibling->value => 'Hermano(a)',
			self::Brother->value => 'Hermano',
			self::Sister->value => 'Hermana',
			self::Friend->value => 'Amigo(a)',
			self::Neighbor->value => 'Vecino(a)',
			self::Lawyer->value => 'Abogado(a)',
			self::Doctor->value => 'Médico(a)',
			self::Notary->value => 'Notario',
			self::Executor->value => 'Ejecutor',
			self::PowerOfAttorney->value => 'Apoderado',

			// Medical
			self::PrimaryPhysician->value => 'Médico de Cabecera',
			self::Specialist->value => 'Especialista',
			self::Dentist->value => 'Dentista',
			self::Therapist->value => 'Terapeuta',
			self::Pharmacist->value => 'Farmacéutico',
			self::Caregiver->value => 'Cuidador',
			self::HomeCareAide->value => 'Asistente de Cuidado en el Hogar',
			self::NursingContact->value => 'Contacto de Enfermería',

			// Education
			self::Teacher->value => 'Profesor(a)',
			self::Professor->value => 'Profesor(a) Universitario',
			self::Principal->value => 'Director(a)',
			self::Counselor->value => 'Consejero(a)',
			self::Tutor->value => 'Tutor',
			self::Coach->value => 'Entrenador',

			// Other Business
			self::Consultant->value => 'Consultor',
			self::Contractor->value => 'Contratista',
			self::Freelancer->value => 'Freelance',
			self::BusinessPartner->value => 'Socio Comercial',
			self::Investor->value => 'Inversor',
			self::BoardMember->value => 'Miembro del Consejo',
			self::Shareholder->value => 'Accionista',
			self::Auditor->value => 'Auditor',
			self::Banker->value => 'Banquero',
			self::InsuranceAgent->value => 'Agente de Seguros',
			self::RealEstateAgent->value => 'Agente Inmobiliario',

			// Default
			self::Other->value => 'Otro',
			self::Unspecified->value => 'No Especificado',
		];
	}

	public static function labelsAr(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'مدير الحساب',
			self::BillingContact->value => 'جهة اتصال الفواتير',
			self::PaymentContact->value => 'جهة اتصال الدفع',
			self::InvoiceContact->value => 'جهة اتصال الفاتورة',
			self::CreditController->value => 'مراقب الائتمان',
			self::FinancialAdvisor->value => 'المستشار المالي',
			self::TaxContact->value => 'جهة اتصال الضرائب',
			self::AuditContact->value => 'جهة اتصال التدقيق',
			self::BudgetManager->value => 'مدير الميزانية',
			self::CostController->value => 'مراقب التكاليف',

			// CRM
			self::PrimaryContact->value => 'جهة الاتصال الرئيسية',
			self::SalesContact->value => 'جهة اتصال المبيعات',
			self::KeyAccountManager->value => 'مدير الحسابات الرئيسية',
			self::LeadContact->value => 'جهة اتصال العميل المحتمل',
			self::OpportunityOwner->value => 'مالك الفرصة',
			self::CustomerSuccessManager->value => 'مدير نجاح العملاء',
			self::SupportContact->value => 'جهة اتصال الدعم',
			self::RenewalContact->value => 'جهة اتصال التجديد',
			self::UpsellContact->value => 'جهة اتصال البيع الإضافي',
			self::ReferralContact->value => 'جهة اتصال الإحالة',

			// HRM
			self::HRManager->value => 'مدير الموارد البشرية',
			self::Recruiter->value => 'مسؤول التوظيف',
			self::PayrollContact->value => 'جهة اتصال كشوف المرتبات',
			self::BenefitsAdmin->value => 'مسؤول المزايا',
			self::TrainingCoordinator->value => 'منسق التدريب',
			self::PerformanceManager->value => 'مدير الأداء',
			self::OnboardingContact->value => 'جهة اتصال الانضمام',
			self::OffboardingContact->value => 'جهة اتصال المغادرة',
			self::ComplianceOfficer->value => 'مسؤول الامتثال',
			self::SafetyOfficer->value => 'مسؤول السلامة',

			// IT
			self::SystemAdmin->value => 'مسؤول النظام',
			self::ITManager->value => 'مدير تكنولوجيا المعلومات',
			self::HelpDeskContact->value => 'جهة اتصال مكتب المساعدة',
			self::NetworkAdmin->value => 'مسؤول الشبكة',
			self::SecurityOfficer->value => 'مسؤول الأمن',
			self::DatabaseAdmin->value => 'مسؤول قاعدة البيانات',
			self::DevelopmentLead->value => 'قائد التطوير',
			self::TechnicalSupport->value => 'الدعم الفني',
			self::InfrastructureManager->value => 'مدير البنية التحتية',
			self::ProcurementContact->value => 'جهة اتصال مشتريات تكنولوجيا المعلومات',

			// Project Management
			self::ProjectManager->value => 'مدير المشروع',
			self::TeamLead->value => 'قائد الفريق',
			self::TeamMember->value => 'عضو الفريق',
			self::Stakeholder->value => 'صاحب المصلحة',
			self::Sponsor->value => 'الراعي',
			self::ProductOwner->value => 'مالك المنتج',
			self::ScrumMaster->value => 'سكروم ماستر',
			self::DeliveryManager->value => 'مدير التسليم',
			self::QualityAssurance->value => 'ضمان الجودة',
			self::ResourceManager->value => 'مدير الموارد',

			// Administrative
			self::OfficeManager->value => 'مدير المكتب',
			self::ExecutiveAssistant->value => 'المساعد التنفيذي',
			self::FacilityManager->value => 'مدير المرافق',
			self::OperationsManager->value => 'مدير العمليات',
			self::LogisticsContact->value => 'جهة اتصال الخدمات اللوجستية',
			self::ProcurementManager->value => 'مدير المشتريات',
			self::InventoryManager->value => 'مدير المخزون',
			self::SupplyChainContact->value => 'جهة اتصال سلسلة التوريد',
			self::VendorManager->value => 'مدير الموردين',
			self::LegalContact->value => 'جهة اتصال قانونية',

			// Personal
			self::EmergencyContact->value => 'جهة اتصال الطوارئ',
			self::Spouse->value => 'الزوج/الزوجة',
			self::Partner->value => 'الشريك',
			self::Fiancee->value => 'العروس',
			self::Wife->value => 'الزوجة',
			self::Husband->value => 'الزوج',
			self::Parent->value => 'الأب/الأم',
			self::Mother->value => 'الأم',
			self::Father->value => 'الأب',
			self::Guardian->value => 'الوصي',
			self::Child->value => 'الطفل/الطفلة',
			self::Son->value => 'الابن',
			self::Daughter->value => 'الابنة',
			self::Sibling->value => 'الأخ/الأخت',
			self::Brother->value => 'الأخ',
			self::Sister->value => 'الأخت',
			self::Friend->value => 'الصديق/الصديقة',
			self::Neighbor->value => 'الجار/الجارة',
			self::Lawyer->value => 'المحامي/المحامية',
			self::Doctor->value => 'الطبيب/الطبيبة',
			self::Notary->value => 'الموثق',
			self::Executor->value => 'المنفذ',
			self::PowerOfAttorney->value => 'الوكيل بالتفويض',

			// Medical
			self::PrimaryPhysician->value => 'الطبيب الأساسي',
			self::Specialist->value => 'الأخصائي',
			self::Dentist->value => 'طبيب الأسنان',
			self::Therapist->value => 'المعالج',
			self::Pharmacist->value => 'الصيدلي',
			self::Caregiver->value => 'مقدم الرعاية',
			self::HomeCareAide->value => 'مساعد الرعاية المنزلية',
			self::NursingContact->value => 'جهة اتصال التمريض',

			// Education
			self::Teacher->value => 'المعلم/المعلمة',
			self::Professor->value => 'الأستاذ/الأستاذة',
			self::Principal->value => 'المدير/المديرة',
			self::Counselor->value => 'المستشار/المستشارة',
			self::Tutor->value => 'المدرس',
			self::Coach->value => 'المدرب',

			// Other Business
			self::Consultant->value => 'المستشار',
			self::Contractor->value => 'المقاول',
			self::Freelancer->value => 'العامل الحر',
			self::BusinessPartner->value => 'الشريك التجاري',
			self::Investor->value => 'المستثمر',
			self::BoardMember->value => 'عضو مجلس الإدارة',
			self::Shareholder->value => 'المساهم',
			self::Auditor->value => 'المدقق',
			self::Banker->value => 'المصرفي',
			self::InsuranceAgent->value => 'وكيل التأمين',
			self::RealEstateAgent->value => 'وسيط العقارات',

			// Default
			self::Other->value => 'آخر',
			self::Unspecified->value => 'غير محدد',
		];
	}

	public static function labelsDa(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Kontomanager',
			self::BillingContact->value => 'Fakturamodtager',
			self::PaymentContact->value => 'Betalingskontakt',
			self::InvoiceContact->value => 'Fakturakontakt',
			self::CreditController->value => 'Kreditkontrol',
			self::FinancialAdvisor->value => 'Finansiel Rådgiver',
			self::TaxContact->value => 'Skattekontakt',
			self::AuditContact->value => 'Revisionskontakt',
			self::BudgetManager->value => 'Budgetmanager',
			self::CostController->value => 'Omkostningskontrol',

			// CRM
			self::PrimaryContact->value => 'Primær Kontakt',
			self::SalesContact->value => 'Salgskontakt',
			self::KeyAccountManager->value => 'Nøglekontomanager',
			self::LeadContact->value => 'Lead Kontakt',
			self::OpportunityOwner->value => 'Mulighedsejer',
			self::CustomerSuccessManager->value => 'Kundesuccess Manager',
			self::SupportContact->value => 'Supportkontakt',
			self::RenewalContact->value => 'Fornyelseskontakt',
			self::UpsellContact->value => 'Upsell Kontakt',
			self::ReferralContact->value => 'Henvisningskontakt',

			// HRM
			self::HRManager->value => 'HR Manager',
			self::Recruiter->value => 'Rekrutteringsmedarbejder',
			self::PayrollContact->value => 'Lønkontakt',
			self::BenefitsAdmin->value => 'Fordelsadministrator',
			self::TrainingCoordinator->value => 'Træningskoordinator',
			self::PerformanceManager->value => 'Performance Manager',
			self::OnboardingContact->value => 'Onboarding Kontakt',
			self::OffboardingContact->value => 'Offboarding Kontakt',
			self::ComplianceOfficer->value => 'Compliance Officer',
			self::SafetyOfficer->value => 'Sikkerhedsofficer',

			// IT
			self::SystemAdmin->value => 'Systemadministrator',
			self::ITManager->value => 'IT Manager',
			self::HelpDeskContact->value => 'Help Desk Kontakt',
			self::NetworkAdmin->value => 'Netværksadministrator',
			self::SecurityOfficer->value => 'Sikkerhedsofficer',
			self::DatabaseAdmin->value => 'Databaseadministrator',
			self::DevelopmentLead->value => 'Udviklingsleder',
			self::TechnicalSupport->value => 'Teknisk Support',
			self::InfrastructureManager->value => 'Infrastruktur Manager',
			self::ProcurementContact->value => 'IT Indkøbskontakt',

			// Project Management
			self::ProjectManager->value => 'Projektleder',
			self::TeamLead->value => 'Teamleder',
			self::TeamMember->value => 'Teammedlem',
			self::Stakeholder->value => 'Interessent',
			self::Sponsor->value => 'Sponsor',
			self::ProductOwner->value => 'Produktejer',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'Leveringsmanager',
			self::QualityAssurance->value => 'Kvalitetssikring',
			self::ResourceManager->value => 'Ressourcemanager',

			// Administrative
			self::OfficeManager->value => 'Kontorchef',
			self::ExecutiveAssistant->value => 'Økonomiassistent',
			self::FacilityManager->value => 'Facilitetsmanager',
			self::OperationsManager->value => 'Operations Manager',
			self::LogisticsContact->value => 'Logistikkontakt',
			self::ProcurementManager->value => 'Indkøbschef',
			self::InventoryManager->value => 'Lagerchef',
			self::SupplyChainContact->value => 'Forsyningskædekontakt',
			self::VendorManager->value => 'Leverandørmanager',
			self::LegalContact->value => 'Juridisk Kontakt',

			// Personal
			self::EmergencyContact->value => 'Nødkontakt',
			self::Spouse->value => 'Ægtefælle',
			self::Partner->value => 'Partner',
			self::Fiancee->value => 'Forlovede',
			self::Wife->value => 'Kone',
			self::Husband->value => 'Mand',
			self::Parent->value => 'Forælder',
			self::Mother->value => 'Mor',
			self::Father->value => 'Far',
			self::Guardian->value => 'Værge',
			self::Child->value => 'Barn',
			self::Son->value => 'Søn',
			self::Daughter->value => 'Datter',
			self::Sibling->value => 'Søskende',
			self::Brother->value => 'Bror',
			self::Sister->value => 'Søster',
			self::Friend->value => 'Ven',
			self::Neighbor->value => 'Nabo',
			self::Lawyer->value => 'Advokat',
			self::Doctor->value => 'Læge',
			self::Notary->value => 'Notar',
			self::Executor->value => 'Eksekutor',
			self::PowerOfAttorney->value => 'Fuldmægtig',

			// Medical
			self::PrimaryPhysician->value => 'Primær Læge',
			self::Specialist->value => 'Specialist',
			self::Dentist->value => 'Tandlæge',
			self::Therapist->value => 'Terapeut',
			self::Pharmacist->value => 'Farmaceut',
			self::Caregiver->value => 'Omsorgsperson',
			self::HomeCareAide->value => 'Hjemmehjælper',
			self::NursingContact->value => 'Sygeplejekontakt',

			// Education
			self::Teacher->value => 'Lærer',
			self::Professor->value => 'Professor',
			self::Principal->value => 'Skoleleder',
			self::Counselor->value => 'Rådgiver',
			self::Tutor->value => 'Tutor',
			self::Coach->value => 'Træner',

			// Other Business
			self::Consultant->value => 'Konsulent',
			self::Contractor->value => 'Entreprenør',
			self::Freelancer->value => 'Freelance',
			self::BusinessPartner->value => 'Forretningspartner',
			self::Investor->value => 'Investor',
			self::BoardMember->value => 'Bestyrelsesmedlem',
			self::Shareholder->value => 'Aktionær',
			self::Auditor->value => 'Revisor',
			self::Banker->value => 'Bankmand',
			self::InsuranceAgent->value => 'Forsikringsagent',
			self::RealEstateAgent->value => 'Ejendomsmægler',

			// Default
			self::Other->value => 'Andet',
			self::Unspecified->value => 'Ikke Angivet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Account Manager',
			self::BillingContact->value => 'Rechnungskontakt',
			self::PaymentContact->value => 'Zahlungskontakt',
			self::InvoiceContact->value => 'Rechnungskontakt',
			self::CreditController->value => 'Kreditkontrolleur',
			self::FinancialAdvisor->value => 'Finanzberater',
			self::TaxContact->value => 'Steuerkontakt',
			self::AuditContact->value => 'Prüfungskontakt',
			self::BudgetManager->value => 'Budgetmanager',
			self::CostController->value => 'Kostenkontrolleur',

			// CRM
			self::PrimaryContact->value => 'Primärkontakt',
			self::SalesContact->value => 'Vertriebskontakt',
			self::KeyAccountManager->value => 'Key Account Manager',
			self::LeadContact->value => 'Lead Kontakt',
			self::OpportunityOwner->value => 'Opportunity Inhaber',
			self::CustomerSuccessManager->value => 'Customer Success Manager',
			self::SupportContact->value => 'Support Kontakt',
			self::RenewalContact->value => 'Verlängerungskontakt',
			self::UpsellContact->value => 'Upsell Kontakt',
			self::ReferralContact->value => 'Empfehlungskontakt',

			// HRM
			self::HRManager->value => 'HR Manager',
			self::Recruiter->value => 'Recruiter',
			self::PayrollContact->value => 'Lohnkontakt',
			self::BenefitsAdmin->value => 'Benefits Administrator',
			self::TrainingCoordinator->value => 'Trainingskoordinator',
			self::PerformanceManager->value => 'Performance Manager',
			self::OnboardingContact->value => 'Onboarding Kontakt',
			self::OffboardingContact->value => 'Offboarding Kontakt',
			self::ComplianceOfficer->value => 'Compliance Officer',
			self::SafetyOfficer->value => 'Sicherheitsbeauftragter',

			// IT
			self::SystemAdmin->value => 'Systemadministrator',
			self::ITManager->value => 'IT Manager',
			self::HelpDeskContact->value => 'Help Desk Kontakt',
			self::NetworkAdmin->value => 'Netzwerkadministrator',
			self::SecurityOfficer->value => 'Sicherheitsbeauftragter',
			self::DatabaseAdmin->value => 'Datenbankadministrator',
			self::DevelopmentLead->value => 'Entwicklungsleiter',
			self::TechnicalSupport->value => 'Technischer Support',
			self::InfrastructureManager->value => 'Infrastruktur Manager',
			self::ProcurementContact->value => 'IT-Beschaffungskontakt',

			// Project Management
			self::ProjectManager->value => 'Projektmanager',
			self::TeamLead->value => 'Teamleiter',
			self::TeamMember->value => 'Teammitglied',
			self::Stakeholder->value => 'Stakeholder',
			self::Sponsor->value => 'Sponsor',
			self::ProductOwner->value => 'Product Owner',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'Delivery Manager',
			self::QualityAssurance->value => 'Qualitätssicherung',
			self::ResourceManager->value => 'Ressourcenmanager',

			// Administrative
			self::OfficeManager->value => 'Büromanager',
			self::ExecutiveAssistant->value => 'Executive Assistant',
			self::FacilityManager->value => 'Facility Manager',
			self::OperationsManager->value => 'Operations Manager',
			self::LogisticsContact->value => 'Logistik Kontakt',
			self::ProcurementManager->value => 'Beschaffungsmanager',
			self::InventoryManager->value => 'Lagerverwalter',
			self::SupplyChainContact->value => 'Lieferkettenkontakt',
			self::VendorManager->value => 'Lieferantenmanager',
			self::LegalContact->value => 'Rechtlicher Kontakt',

			// Personal
			self::EmergencyContact->value => 'Notfallkontakt',
			self::Spouse->value => 'Ehepartner',
			self::Partner->value => 'Partner',
			self::Fiancee->value => 'Verlobte',
			self::Wife->value => 'Ehefrau',
			self::Husband->value => 'Ehemann',
			self::Parent->value => 'Elternteil',
			self::Mother->value => 'Mutter',
			self::Father->value => 'Vater',
			self::Guardian->value => 'Vormund',
			self::Child->value => 'Kind',
			self::Son->value => 'Sohn',
			self::Daughter->value => 'Tochter',
			self::Sibling->value => 'Geschwister',
			self::Brother->value => 'Bruder',
			self::Sister->value => 'Schwester',
			self::Friend->value => 'Freund',
			self::Neighbor->value => 'Nachbar',
			self::Lawyer->value => 'Rechtsanwalt',
			self::Doctor->value => 'Arzt',
			self::Notary->value => 'Notar',
			self::Executor->value => 'Testamentsvollstrecker',
			self::PowerOfAttorney->value => 'Bevollmächtigter',

			// Medical
			self::PrimaryPhysician->value => 'Hausarzt',
			self::Specialist->value => 'Facharzt',
			self::Dentist->value => 'Zahnarzt',
			self::Therapist->value => 'Therapeut',
			self::Pharmacist->value => 'Apotheker',
			self::Caregiver->value => 'Pflegeperson',
			self::HomeCareAide->value => 'Hauspflegehelfer',
			self::NursingContact->value => 'Pflegekontakt',

			// Education
			self::Teacher->value => 'Lehrer',
			self::Professor->value => 'Professor',
			self::Principal->value => 'Schulleiter',
			self::Counselor->value => 'Berater',
			self::Tutor->value => 'Nachhilfelehrer',
			self::Coach->value => 'Trainer',

			// Other Business
			self::Consultant->value => 'Berater',
			self::Contractor->value => 'Auftragnehmer',
			self::Freelancer->value => 'Freiberufler',
			self::BusinessPartner->value => 'Geschäftspartner',
			self::Investor->value => 'Investor',
			self::BoardMember->value => 'Vorstandsmitglied',
			self::Shareholder->value => 'Aktionär',
			self::Auditor->value => 'Wirtschaftsprüfer',
			self::Banker->value => 'Banker',
			self::InsuranceAgent->value => 'Versicherungsagent',
			self::RealEstateAgent->value => 'Immobilienmakler',

			// Default
			self::Other->value => 'Andere',
			self::Unspecified->value => 'Nicht Angegeben',
		];
	}

	public static function labelsFr(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Responsable de Compte',
			self::BillingContact->value => 'Contact Facturation',
			self::PaymentContact->value => 'Contact Paiement',
			self::InvoiceContact->value => 'Contact Facture',
			self::CreditController->value => 'Contrôleur de Crédit',
			self::FinancialAdvisor->value => 'Conseiller Financier',
			self::TaxContact->value => 'Contact Fiscal',
			self::AuditContact->value => 'Contact Audit',
			self::BudgetManager->value => 'Responsable Budget',
			self::CostController->value => 'Contrôleur de Coûts',

			// CRM
			self::PrimaryContact->value => 'Contact Principal',
			self::SalesContact->value => 'Contact Commercial',
			self::KeyAccountManager->value => 'Responsable Grands Comptes',
			self::LeadContact->value => 'Contact Prospect',
			self::OpportunityOwner->value => 'Responsable Opportunité',
			self::CustomerSuccessManager->value => 'Responsable Succès Client',
			self::SupportContact->value => 'Contact Support',
			self::RenewalContact->value => 'Contact Renouvellement',
			self::UpsellContact->value => 'Contact Vente Additionnelle',
			self::ReferralContact->value => 'Contact Parrainage',

			// HRM
			self::HRManager->value => 'Responsable RH',
			self::Recruiter->value => 'Recruteur',
			self::PayrollContact->value => 'Contact Paie',
			self::BenefitsAdmin->value => 'Administrateur Avantages',
			self::TrainingCoordinator->value => 'Coordinateur Formation',
			self::PerformanceManager->value => 'Responsable Performance',
			self::OnboardingContact->value => 'Contact Intégration',
			self::OffboardingContact->value => 'Contact Départ',
			self::ComplianceOfficer->value => 'Responsable Conformité',
			self::SafetyOfficer->value => 'Responsable Sécurité',

			// IT
			self::SystemAdmin->value => 'Administrateur Système',
			self::ITManager->value => 'Responsable IT',
			self::HelpDeskContact->value => 'Contact Help Desk',
			self::NetworkAdmin->value => 'Administrateur Réseau',
			self::SecurityOfficer->value => 'Responsable Sécurité',
			self::DatabaseAdmin->value => 'Administrateur Base de Données',
			self::DevelopmentLead->value => 'Lead Développement',
			self::TechnicalSupport->value => 'Support Technique',
			self::InfrastructureManager->value => 'Responsable Infrastructure',
			self::ProcurementContact->value => 'Contact Achats IT',

			// Project Management
			self::ProjectManager->value => 'Chef de Projet',
			self::TeamLead->value => 'Lead d\'Équipe',
			self::TeamMember->value => 'Membre d\'Équipe',
			self::Stakeholder->value => 'Partie Prenante',
			self::Sponsor->value => 'Sponsor',
			self::ProductOwner->value => 'Product Owner',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'Responsable Livraison',
			self::QualityAssurance->value => 'Assurance Qualité',
			self::ResourceManager->value => 'Responsable Ressources',

			// Administrative
			self::OfficeManager->value => 'Responsable Bureau',
			self::ExecutiveAssistant->value => 'Assistante de Direction',
			self::FacilityManager->value => 'Responsable Installations',
			self::OperationsManager->value => 'Responsable Opérations',
			self::LogisticsContact->value => 'Contact Logistique',
			self::ProcurementManager->value => 'Responsable Achats',
			self::InventoryManager->value => 'Responsable Inventaire',
			self::SupplyChainContact->value => 'Contact Chaîne d\'Approvisionnement',
			self::VendorManager->value => 'Responsable Fournisseurs',
			self::LegalContact->value => 'Contact Juridique',

			// Personal
			self::EmergencyContact->value => 'Contact d\'Urgence',
			self::Spouse->value => 'Conjoint',
			self::Partner->value => 'Partenaire',
			self::Fiancee->value => 'Fiancée',
			self::Wife->value => 'Épouse',
			self::Husband->value => 'Mari',
			self::Parent->value => 'Parent',
			self::Mother->value => 'Mère',
			self::Father->value => 'Père',
			self::Guardian->value => 'Tuteur',
			self::Child->value => 'Enfant',
			self::Son->value => 'Fils',
			self::Daughter->value => 'Fille',
			self::Sibling->value => 'Frère/Sœur',
			self::Brother->value => 'Frère',
			self::Sister->value => 'Sœur',
			self::Friend->value => 'Ami',
			self::Neighbor->value => 'Voisin',
			self::Lawyer->value => 'Avocat',
			self::Doctor->value => 'Médecin',
			self::Notary->value => 'Notaire',
			self::Executor->value => 'Exécuteur Testamentaire',
			self::PowerOfAttorney->value => 'Mandataire',

			// Medical
			self::PrimaryPhysician->value => 'Médecin Traitant',
			self::Specialist->value => 'Spécialiste',
			self::Dentist->value => 'Dentiste',
			self::Therapist->value => 'Thérapeute',
			self::Pharmacist->value => 'Pharmacien',
			self::Caregiver->value => 'Aidant',
			self::HomeCareAide->value => 'Aide à Domicile',
			self::NursingContact->value => 'Contact Infirmier',

			// Education
			self::Teacher->value => 'Enseignant',
			self::Professor->value => 'Professeur',
			self::Principal->value => 'Directeur',
			self::Counselor->value => 'Conseiller',
			self::Tutor->value => 'Tuteur',
			self::Coach->value => 'Entraîneur',

			// Other Business
			self::Consultant->value => 'Consultant',
			self::Contractor->value => 'Contractant',
			self::Freelancer->value => 'Freelance',
			self::BusinessPartner->value => 'Partenaire Commercial',
			self::Investor->value => 'Investisseur',
			self::BoardMember->value => 'Membre du Conseil',
			self::Shareholder->value => 'Actionnaire',
			self::Auditor->value => 'Auditeur',
			self::Banker->value => 'Banquier',
			self::InsuranceAgent->value => 'Agent d\'Assurance',
			self::RealEstateAgent->value => 'Agent Immobilier',

			// Default
			self::Other->value => 'Autre',
			self::Unspecified->value => 'Non Spécifié',
		];
	}

	public static function labelsHe(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'מנהל חשבון',
			self::BillingContact->value => 'איש קשר לחיוב',
			self::PaymentContact->value => 'איש קשר לתשלום',
			self::InvoiceContact->value => 'איש קשר לחשבונית',
			self::CreditController->value => 'בקרת אשראי',
			self::FinancialAdvisor->value => 'יועץ פיננסי',
			self::TaxContact->value => 'איש קשר למס',
			self::AuditContact->value => 'איש קשר לביקורת',
			self::BudgetManager->value => 'מנהל תקציב',
			self::CostController->value => 'בקרת עלויות',

			// CRM
			self::PrimaryContact->value => 'איש קשר ראשי',
			self::SalesContact->value => 'איש קשר מכירות',
			self::KeyAccountManager->value => 'מנהל חשבונות מפתח',
			self::LeadContact->value => 'איש קשר ליד',
			self::OpportunityOwner->value => 'בעל הזדמנות',
			self::CustomerSuccessManager->value => 'מנהל הצלחת לקוחות',
			self::SupportContact->value => 'איש קשר תמיכה',
			self::RenewalContact->value => 'איש קשר לחידוש',
			self::UpsellContact->value => 'איש קשר למכירות נוספות',
			self::ReferralContact->value => 'איש קשר להמלצות',

			// HRM
			self::HRManager->value => 'מנהל משאבי אנוש',
			self::Recruiter->value => 'מגייס',
			self::PayrollContact->value => 'איש קשר לשכר',
			self::BenefitsAdmin->value => 'מנהל הטבות',
			self::TrainingCoordinator->value => 'רכז הדרכה',
			self::PerformanceManager->value => 'מנהל ביצועים',
			self::OnboardingContact->value => 'איש קשר קליטה',
			self::OffboardingContact->value => 'איש קשר סיום עבודה',
			self::ComplianceOfficer->value => 'מנהל תאימות',
			self::SafetyOfficer->value => 'מנהל בטיחות',

			// IT
			self::SystemAdmin->value => 'מנהל מערכת',
			self::ITManager->value => 'מנהל IT',
			self::HelpDeskContact->value => 'איש קשר שירות',
			self::NetworkAdmin->value => 'מנהל רשת',
			self::SecurityOfficer->value => 'מנהל אבטחה',
			self::DatabaseAdmin->value => 'מנהל מסד נתונים',
			self::DevelopmentLead->value => 'ראש צוות פיתוח',
			self::TechnicalSupport->value => 'תמיכה טכנית',
			self::InfrastructureManager->value => 'מנהל תשתית',
			self::ProcurementContact->value => 'איש קשר רכש IT',

			// Project Management
			self::ProjectManager->value => 'מנהל פרויקט',
			self::TeamLead->value => 'ראש צוות',
			self::TeamMember->value => 'חבר צוות',
			self::Stakeholder->value => 'בעל עניין',
			self::Sponsor->value => 'ספונסר',
			self::ProductOwner->value => 'בעל מוצר',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'מנהל אספקה',
			self::QualityAssurance->value => 'בקרת איכות',
			self::ResourceManager->value => 'מנהל משאבים',

			// Administrative
			self::OfficeManager->value => 'מנהל משרד',
			self::ExecutiveAssistant->value => 'עוזר מנכ"ל',
			self::FacilityManager->value => 'מנהל מתקנים',
			self::OperationsManager->value => 'מנהל פעילות',
			self::LogisticsContact->value => 'איש קשר לוגיסטיקה',
			self::ProcurementManager->value => 'מנהל רכש',
			self::InventoryManager->value => 'מנהל מלאי',
			self::SupplyChainContact->value => 'איש קשר שרשרת אספקה',
			self::VendorManager->value => 'מנהל ספקים',
			self::LegalContact->value => 'איש קשר משפטי',

			// Personal
			self::EmergencyContact->value => 'איש קשר חירום',
			self::Spouse->value => 'בן/בת זוג',
			self::Partner->value => 'שותף',
			self::Fiancee->value => 'ארוסה',
			self::Wife->value => 'אשה',
			self::Husband->value => 'בעל',
			self::Parent->value => 'הורה',
			self::Mother->value => 'אם',
			self::Father->value => 'אב',
			self::Guardian->value => 'אפוטרופוס',
			self::Child->value => 'ילד',
			self::Son->value => 'בן',
			self::Daughter->value => 'בת',
			self::Sibling->value => 'אח/אחות',
			self::Brother->value => 'אח',
			self::Sister->value => 'אחות',
			self::Friend->value => 'חבר',
			self::Neighbor->value => 'שכן',
			self::Lawyer->value => 'עורך דין',
			self::Doctor->value => 'רופא',
			self::Notary->value => 'נוטריון',
			self::Executor->value => 'מבצע צוואה',
			self::PowerOfAttorney->value => 'מיופה כוח',

			// Medical
			self::PrimaryPhysician->value => 'רופא משפחה',
			self::Specialist->value => 'מומחה',
			self::Dentist->value => 'רופא שיניים',
			self::Therapist->value => 'מטפל',
			self::Pharmacist->value => 'רוקח',
			self::Caregiver->value => 'מטפל',
			self::HomeCareAide->value => 'עוזר טיפול ביתי',
			self::NursingContact->value => 'איש קשר סיעוד',

			// Education
			self::Teacher->value => 'מורה',
			self::Professor->value => 'פרופסור',
			self::Principal->value => 'מנהל בית ספר',
			self::Counselor->value => 'יועץ',
			self::Tutor->value => 'מורה פרטי',
			self::Coach->value => 'מאמן',

			// Other Business
			self::Consultant->value => 'יועץ',
			self::Contractor->value => 'קבלן',
			self::Freelancer->value => 'פרילנסר',
			self::BusinessPartner->value => 'שותף עסקי',
			self::Investor->value => 'משקיע',
			self::BoardMember->value => 'חבר דירקטוריון',
			self::Shareholder->value => 'בעל מניות',
			self::Auditor->value => 'רואה חשבון',
			self::Banker->value => 'בנקאי',
			self::InsuranceAgent->value => 'סוכן ביטוח',
			self::RealEstateAgent->value => 'מתווך נדל"ן',

			// Default
			self::Other->value => 'אחר',
			self::Unspecified->value => 'לא מוגדר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Account Manager',
			self::BillingContact->value => 'Contatto Fatturazione',
			self::PaymentContact->value => 'Contatto Pagamento',
			self::InvoiceContact->value => 'Contatto Fattura',
			self::CreditController->value => 'Controllore Credito',
			self::FinancialAdvisor->value => 'Consulente Finanziario',
			self::TaxContact->value => 'Contatto Fiscale',
			self::AuditContact->value => 'Contatto Audit',
			self::BudgetManager->value => 'Responsabile Budget',
			self::CostController->value => 'Controllore Costi',

			// CRM
			self::PrimaryContact->value => 'Contatto Principale',
			self::SalesContact->value => 'Contatto Commerciale',
			self::KeyAccountManager->value => 'Key Account Manager',
			self::LeadContact->value => 'Contatto Lead',
			self::OpportunityOwner->value => 'Proprietario Opportunità',
			self::CustomerSuccessManager->value => 'Customer Success Manager',
			self::SupportContact->value => 'Contatto Supporto',
			self::RenewalContact->value => 'Contatto Rinnovo',
			self::UpsellContact->value => 'Contatto Vendita Aggiuntiva',
			self::ReferralContact->value => 'Contatto Riferimento',

			// HRM
			self::HRManager->value => 'HR Manager',
			self::Recruiter->value => 'Reclutatore',
			self::PayrollContact->value => 'Contatto Retribuzioni',
			self::BenefitsAdmin->value => 'Amministratore Benefit',
			self::TrainingCoordinator->value => 'Coordinatore Formazione',
			self::PerformanceManager->value => 'Performance Manager',
			self::OnboardingContact->value => 'Contatto Onboarding',
			self::OffboardingContact->value => 'Contatto Offboarding',
			self::ComplianceOfficer->value => 'Responsabile Conformità',
			self::SafetyOfficer->value => 'Responsabile Sicurezza',

			// IT
			self::SystemAdmin->value => 'Amministratore di Sistema',
			self::ITManager->value => 'IT Manager',
			self::HelpDeskContact->value => 'Contatto Help Desk',
			self::NetworkAdmin->value => 'Amministratore di Rete',
			self::SecurityOfficer->value => 'Responsabile Sicurezza',
			self::DatabaseAdmin->value => 'Amministratore Database',
			self::DevelopmentLead->value => 'Lead Sviluppo',
			self::TechnicalSupport->value => 'Supporto Tecnico',
			self::InfrastructureManager->value => 'Responsabile Infrastruttura',
			self::ProcurementContact->value => 'Contatto Acquisti IT',

			// Project Management
			self::ProjectManager->value => 'Project Manager',
			self::TeamLead->value => 'Team Lead',
			self::TeamMember->value => 'Membro del Team',
			self::Stakeholder->value => 'Stakeholder',
			self::Sponsor->value => 'Sponsor',
			self::ProductOwner->value => 'Product Owner',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'Delivery Manager',
			self::QualityAssurance->value => 'Assicurazione Qualità',
			self::ResourceManager->value => 'Responsabile Risorse',

			// Administrative
			self::OfficeManager->value => 'Office Manager',
			self::ExecutiveAssistant->value => 'Assistente Esecutivo',
			self::FacilityManager->value => 'Facility Manager',
			self::OperationsManager->value => 'Operations Manager',
			self::LogisticsContact->value => 'Contatto Logistica',
			self::ProcurementManager->value => 'Responsabile Acquisti',
			self::InventoryManager->value => 'Responsabile Inventario',
			self::SupplyChainContact->value => 'Contatto Catena di Approvvigionamento',
			self::VendorManager->value => 'Responsabile Fornitori',
			self::LegalContact->value => 'Contatto Legale',

			// Personal
			self::EmergencyContact->value => 'Contatto di Emergenza',
			self::Spouse->value => 'Coniuge',
			self::Partner->value => 'Partner',
			self::Fiancee->value => 'Fidanzata',
			self::Wife->value => 'Moglie',
			self::Husband->value => 'Marito',
			self::Parent->value => 'Genitore',
			self::Mother->value => 'Madre',
			self::Father->value => 'Padre',
			self::Guardian->value => 'Tutore',
			self::Child->value => 'Figlio/Figlia',
			self::Son->value => 'Figlio',
			self::Daughter->value => 'Figlia',
			self::Sibling->value => 'Fratello/Sorella',
			self::Brother->value => 'Fratello',
			self::Sister->value => 'Sorella',
			self::Friend->value => 'Amico/Amica',
			self::Neighbor->value => 'Vicino/Vicina',
			self::Lawyer->value => 'Avvocato',
			self::Doctor->value => 'Medico',
			self::Notary->value => 'Notaio',
			self::Executor->value => 'Esecutore Testamentario',
			self::PowerOfAttorney->value => 'Procura',

			// Medical
			self::PrimaryPhysician->value => 'Medico di Base',
			self::Specialist->value => 'Specialista',
			self::Dentist->value => 'Dentista',
			self::Therapist->value => 'Terapista',
			self::Pharmacist->value => 'Farmacista',
			self::Caregiver->value => 'Badante',
			self::HomeCareAide->value => 'Assistente Domiciliare',
			self::NursingContact->value => 'Contatto Infermieristico',

			// Education
			self::Teacher->value => 'Insegnante',
			self::Professor->value => 'Professore',
			self::Principal->value => 'Preside',
			self::Counselor->value => 'Consulente',
			self::Tutor->value => 'Tutor',
			self::Coach->value => 'Allenatore',

			// Other Business
			self::Consultant->value => 'Consulente',
			self::Contractor->value => 'Contraente',
			self::Freelancer->value => 'Freelancer',
			self::BusinessPartner->value => 'Partner Commerciale',
			self::Investor->value => 'Investitore',
			self::BoardMember->value => 'Membro del Consiglio',
			self::Shareholder->value => 'Azionista',
			self::Auditor->value => 'Revisore',
			self::Banker->value => 'Banchiere',
			self::InsuranceAgent->value => 'Agente Assicurativo',
			self::RealEstateAgent->value => 'Agente Immobiliare',

			// Default
			self::Other->value => 'Altro',
			self::Unspecified->value => 'Non Specificato',
		];
	}

	public static function labelsJa(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'アカウントマネージャー',
			self::BillingContact->value => '請求連絡先',
			self::PaymentContact->value => '支払い連絡先',
			self::InvoiceContact->value => '請求書連絡先',
			self::CreditController->value => '与信管理',
			self::FinancialAdvisor->value => 'ファイナンシャルアドバイザー',
			self::TaxContact->value => '税務連絡先',
			self::AuditContact->value => '監査連絡先',
			self::BudgetManager->value => '予算管理者',
			self::CostController->value => 'コスト管理',

			// CRM
			self::PrimaryContact->value => '主な連絡先',
			self::SalesContact->value => '営業連絡先',
			self::KeyAccountManager->value => 'キーアカウントマネージャー',
			self::LeadContact->value => 'リード連絡先',
			self::OpportunityOwner->value => '商談所有者',
			self::CustomerSuccessManager->value => 'カスタマーサクセスマネージャー',
			self::SupportContact->value => 'サポート連絡先',
			self::RenewalContact->value => '更新連絡先',
			self::UpsellContact->value => 'アップセル連絡先',
			self::ReferralContact->value => '紹介連絡先',

			// HRM
			self::HRManager->value => '人事マネージャー',
			self::Recruiter->value => '採用担当者',
			self::PayrollContact->value => '給与連絡先',
			self::BenefitsAdmin->value => '福利厚生管理者',
			self::TrainingCoordinator->value => '研修コーディネーター',
			self::PerformanceManager->value => 'パフォーマンスマネージャー',
			self::OnboardingContact->value => 'オンボーディング連絡先',
			self::OffboardingContact->value => '退職手続き連絡先',
			self::ComplianceOfficer->value => 'コンプライアンス責任者',
			self::SafetyOfficer->value => '安全責任者',

			// IT
			self::SystemAdmin->value => 'システム管理者',
			self::ITManager->value => 'ITマネージャー',
			self::HelpDeskContact->value => 'ヘルプデスク連絡先',
			self::NetworkAdmin->value => 'ネットワーク管理者',
			self::SecurityOfficer->value => 'セキュリティ責任者',
			self::DatabaseAdmin->value => 'データベース管理者',
			self::DevelopmentLead->value => '開発リーダー',
			self::TechnicalSupport->value => '技術サポート',
			self::InfrastructureManager->value => 'インフラストラクチャーマネージャー',
			self::ProcurementContact->value => 'IT調達連絡先',

			// Project Management
			self::ProjectManager->value => 'プロジェクトマネージャー',
			self::TeamLead->value => 'チームリーダー',
			self::TeamMember->value => 'チームメンバー',
			self::Stakeholder->value => 'ステークホルダー',
			self::Sponsor->value => 'スポンサー',
			self::ProductOwner->value => 'プロダクトオーナー',
			self::ScrumMaster->value => 'スクラムマスター',
			self::DeliveryManager->value => 'デリバリーマネージャー',
			self::QualityAssurance->value => '品質保証',
			self::ResourceManager->value => 'リソースマネージャー',

			// Administrative
			self::OfficeManager->value => 'オフィスマネージャー',
			self::ExecutiveAssistant->value => 'エグゼクティブアシスタント',
			self::FacilityManager->value => '施設管理者',
			self::OperationsManager->value => 'オペレーションマネージャー',
			self::LogisticsContact->value => '物流連絡先',
			self::ProcurementManager->value => '調達マネージャー',
			self::InventoryManager->value => '在庫管理者',
			self::SupplyChainContact->value => 'サプライチェーン連絡先',
			self::VendorManager->value => 'ベンダーマネージャー',
			self::LegalContact->value => '法務連絡先',

			// Personal
			self::EmergencyContact->value => '緊急連絡先',
			self::Spouse->value => '配偶者',
			self::Partner->value => 'パートナー',
			self::Fiancee->value => '婚約者',
			self::Wife->value => '妻',
			self::Husband->value => '夫',
			self::Parent->value => '親',
			self::Mother->value => '母',
			self::Father->value => '父',
			self::Guardian->value => '保護者',
			self::Child->value => '子供',
			self::Son->value => '息子',
			self::Daughter->value => '娘',
			self::Sibling->value => '兄弟姉妹',
			self::Brother->value => '兄弟',
			self::Sister->value => '姉妹',
			self::Friend->value => '友人',
			self::Neighbor->value => '隣人',
			self::Lawyer->value => '弁護士',
			self::Doctor->value => '医師',
			self::Notary->value => '公証人',
			self::Executor->value => '遺言執行者',
			self::PowerOfAttorney->value => '委任状受任者',

			// Medical
			self::PrimaryPhysician->value => 'かかりつけ医',
			self::Specialist->value => '専門医',
			self::Dentist->value => '歯科医',
			self::Therapist->value => 'セラピスト',
			self::Pharmacist->value => '薬剤師',
			self::Caregiver->value => '介護者',
			self::HomeCareAide->value => 'ホームケア助手',
			self::NursingContact->value => '看護連絡先',

			// Education
			self::Teacher->value => '教師',
			self::Professor->value => '教授',
			self::Principal->value => '校長',
			self::Counselor->value => 'カウンセラー',
			self::Tutor->value => '家庭教師',
			self::Coach->value => 'コーチ',

			// Other Business
			self::Consultant->value => 'コンサルタント',
			self::Contractor->value => '請負業者',
			self::Freelancer->value => 'フリーランス',
			self::BusinessPartner->value => 'ビジネスパートナー',
			self::Investor->value => '投資家',
			self::BoardMember->value => '取締役',
			self::Shareholder->value => '株主',
			self::Auditor->value => '監査人',
			self::Banker->value => '銀行家',
			self::InsuranceAgent->value => '保険代理店',
			self::RealEstateAgent->value => '不動産エージェント',

			// Default
			self::Other->value => 'その他',
			self::Unspecified->value => '指定なし',
		];
	}

	public static function labelsNl(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Accountmanager',
			self::BillingContact->value => 'Facturatiecontact',
			self::PaymentContact->value => 'Betalingscontact',
			self::InvoiceContact->value => 'Factuurcontact',
			self::CreditController->value => 'Kredietcontroleur',
			self::FinancialAdvisor->value => 'Financieel Adviseur',
			self::TaxContact->value => 'Belastingcontact',
			self::AuditContact->value => 'Auditcontact',
			self::BudgetManager->value => 'Budgetmanager',
			self::CostController->value => 'Kostenbeheerder',

			// CRM
			self::PrimaryContact->value => 'Primair Contact',
			self::SalesContact->value => 'Verkoopcontact',
			self::KeyAccountManager->value => 'Key Account Manager',
			self::LeadContact->value => 'Lead Contact',
			self::OpportunityOwner->value => 'Opportunity Eigenaar',
			self::CustomerSuccessManager->value => 'Customer Success Manager',
			self::SupportContact->value => 'Supportcontact',
			self::RenewalContact->value => 'Verlengingscontact',
			self::UpsellContact->value => 'Upsell Contact',
			self::ReferralContact->value => 'Verwijzingscontact',

			// HRM
			self::HRManager->value => 'HR Manager',
			self::Recruiter->value => 'Recruiter',
			self::PayrollContact->value => 'Looncontact',
			self::BenefitsAdmin->value => 'Benefits Administrator',
			self::TrainingCoordinator->value => 'Trainingscoördinator',
			self::PerformanceManager->value => 'Performance Manager',
			self::OnboardingContact->value => 'Onboarding Contact',
			self::OffboardingContact->value => 'Offboarding Contact',
			self::ComplianceOfficer->value => 'Compliance Officer',
			self::SafetyOfficer->value => 'Veiligheidsofficier',

			// IT
			self::SystemAdmin->value => 'Systeembeheerder',
			self::ITManager->value => 'IT Manager',
			self::HelpDeskContact->value => 'Helpdesk Contact',
			self::NetworkAdmin->value => 'Netwerkbeheerder',
			self::SecurityOfficer->value => 'Veiligheidsofficier',
			self::DatabaseAdmin->value => 'Databasebeheerder',
			self::DevelopmentLead->value => 'Ontwikkelingsleider',
			self::TechnicalSupport->value => 'Technische Ondersteuning',
			self::InfrastructureManager->value => 'Infrastructuur Manager',
			self::ProcurementContact->value => 'IT Inkoopcontact',

			// Project Management
			self::ProjectManager->value => 'Projectmanager',
			self::TeamLead->value => 'Teamleider',
			self::TeamMember->value => 'Teamlid',
			self::Stakeholder->value => 'Stakeholder',
			self::Sponsor->value => 'Sponsor',
			self::ProductOwner->value => 'Product Owner',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'Delivery Manager',
			self::QualityAssurance->value => 'Kwaliteitsborging',
			self::ResourceManager->value => 'Resource Manager',

			// Administrative
			self::OfficeManager->value => 'Kantoormanager',
			self::ExecutiveAssistant->value => 'Executive Assistant',
			self::FacilityManager->value => 'Facility Manager',
			self::OperationsManager->value => 'Operations Manager',
			self::LogisticsContact->value => 'Logistiek Contact',
			self::ProcurementManager->value => 'Inkoopmanager',
			self::InventoryManager->value => 'Voorraadbeheerder',
			self::SupplyChainContact->value => 'Supply Chain Contact',
			self::VendorManager->value => 'Leveranciersmanager',
			self::LegalContact->value => 'Juridisch Contact',

			// Personal
			self::EmergencyContact->value => 'Noodcontact',
			self::Spouse->value => 'Echtgenoot',
			self::Partner->value => 'Partner',
			self::Fiancee->value => 'Verloofde',
			self::Wife->value => 'Vrouw',
			self::Husband->value => 'Man',
			self::Parent->value => 'Ouder',
			self::Mother->value => 'Moeder',
			self::Father->value => 'Vader',
			self::Guardian->value => 'Voogd',
			self::Child->value => 'Kind',
			self::Son->value => 'Zoon',
			self::Daughter->value => 'Dochter',
			self::Sibling->value => 'Broer/Zus',
			self::Brother->value => 'Broer',
			self::Sister->value => 'Zus',
			self::Friend->value => 'Vriend',
			self::Neighbor->value => 'Buurtbewoner',
			self::Lawyer->value => 'Advocaat',
			self::Doctor->value => 'Arts',
			self::Notary->value => 'Notaris',
			self::Executor->value => 'Uitvoerder',
			self::PowerOfAttorney->value => 'Gevolmachtigde',

			// Medical
			self::PrimaryPhysician->value => 'Huisarts',
			self::Specialist->value => 'Specialist',
			self::Dentist->value => 'Tandarts',
			self::Therapist->value => 'Therapeut',
			self::Pharmacist->value => 'Apotheker',
			self::Caregiver->value => 'Verzorger',
			self::HomeCareAide->value => 'Thuiszorgassistent',
			self::NursingContact->value => 'Verpleegkundig Contact',

			// Education
			self::Teacher->value => 'Docent',
			self::Professor->value => 'Professor',
			self::Principal->value => 'Schoolhoofd',
			self::Counselor->value => 'Counselor',
			self::Tutor->value => 'Tutor',
			self::Coach->value => 'Coach',

			// Other Business
			self::Consultant->value => 'Consultant',
			self::Contractor->value => 'Aannemer',
			self::Freelancer->value => 'Freelancer',
			self::BusinessPartner->value => 'Zakelijke Partner',
			self::Investor->value => 'Investeerder',
			self::BoardMember->value => 'Bestuurslid',
			self::Shareholder->value => 'Aandeelhouder',
			self::Auditor->value => 'Auditor',
			self::Banker->value => 'Bankier',
			self::InsuranceAgent->value => 'Verzekeringsagent',
			self::RealEstateAgent->value => 'Makelaar',

			// Default
			self::Other->value => 'Ander',
			self::Unspecified->value => 'Niet Gespecificeerd',
		];
	}

	public static function labelsPl(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Account Manager',
			self::BillingContact->value => 'Kontakt Rozliczeniowy',
			self::PaymentContact->value => 'Kontakt Płatności',
			self::InvoiceContact->value => 'Kontakt Fakturowania',
			self::CreditController->value => 'Kontroler Kredytowy',
			self::FinancialAdvisor->value => 'Doradca Finansowy',
			self::TaxContact->value => 'Kontakt Podatkowy',
			self::AuditContact->value => 'Kontakt Audytowy',
			self::BudgetManager->value => 'Menedżer Budżetu',
			self::CostController->value => 'Kontroler Kosztów',

			// CRM
			self::PrimaryContact->value => 'Główny Kontakt',
			self::SalesContact->value => 'Kontakt Sprzedażowy',
			self::KeyAccountManager->value => 'Key Account Manager',
			self::LeadContact->value => 'Kontakt Lead',
			self::OpportunityOwner->value => 'Właściciel Oportunności',
			self::CustomerSuccessManager->value => 'Customer Success Manager',
			self::SupportContact->value => 'Kontakt Wsparcia',
			self::RenewalContact->value => 'Kontakt Odnowienia',
			self::UpsellContact->value => 'Kontakt Sprzedaży Dodatkowej',
			self::ReferralContact->value => 'Kontakt Polecenia',

			// HRM
			self::HRManager->value => 'HR Manager',
			self::Recruiter->value => 'Rekruter',
			self::PayrollContact->value => 'Kontakt Płacowy',
			self::BenefitsAdmin->value => 'Administrator Benefitów',
			self::TrainingCoordinator->value => 'Koordynator Szkoleń',
			self::PerformanceManager->value => 'Performance Manager',
			self::OnboardingContact->value => 'Kontakt Onboarding',
			self::OffboardingContact->value => 'Kontakt Offboarding',
			self::ComplianceOfficer->value => 'Oficer Zgodności',
			self::SafetyOfficer->value => 'Oficer Bezpieczeństwa',

			// IT
			self::SystemAdmin->value => 'Administrator Systemu',
			self::ITManager->value => 'IT Manager',
			self::HelpDeskContact->value => 'Kontakt Help Desk',
			self::NetworkAdmin->value => 'Administrator Sieci',
			self::SecurityOfficer->value => 'Oficer Bezpieczeństwa',
			self::DatabaseAdmin->value => 'Administrator Bazy Danych',
			self::DevelopmentLead->value => 'Lider Rozwoju',
			self::TechnicalSupport->value => 'Wsparcie Techniczne',
			self::InfrastructureManager->value => 'Menedżer Infrastruktury',
			self::ProcurementContact->value => 'Kontakt Zakupów IT',

			// Project Management
			self::ProjectManager->value => 'Menedżer Projektu',
			self::TeamLead->value => 'Lider Zespołu',
			self::TeamMember->value => 'Członek Zespołu',
			self::Stakeholder->value => 'Interesariusz',
			self::Sponsor->value => 'Sponsor',
			self::ProductOwner->value => 'Product Owner',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'Delivery Manager',
			self::QualityAssurance->value => 'Zapewnienie Jakości',
			self::ResourceManager->value => 'Menedżer Zasobów',

			// Administrative
			self::OfficeManager->value => 'Office Manager',
			self::ExecutiveAssistant->value => 'Asystent Wykonawczy',
			self::FacilityManager->value => 'Facility Manager',
			self::OperationsManager->value => 'Operations Manager',
			self::LogisticsContact->value => 'Kontakt Logistyczny',
			self::ProcurementManager->value => 'Menedżer Zakupów',
			self::InventoryManager->value => 'Menedżer Zapasów',
			self::SupplyChainContact->value => 'Kontakt Łańcucha Dostaw',
			self::VendorManager->value => 'Menedżer Dostawców',
			self::LegalContact->value => 'Kontakt Prawny',

			// Personal
			self::EmergencyContact->value => 'Kontakt Awaryjny',
			self::Spouse->value => 'Małżonek',
			self::Partner->value => 'Partner',
			self::Fiancee->value => 'Narzeczona',
			self::Wife->value => 'Żona',
			self::Husband->value => 'Mąż',
			self::Parent->value => 'Rodzic',
			self::Mother->value => 'Matka',
			self::Father->value => 'Ojciec',
			self::Guardian->value => 'Opiekun',
			self::Child->value => 'Dziecko',
			self::Son->value => 'Syn',
			self::Daughter->value => 'Córka',
			self::Sibling->value => 'Rodzeństwo',
			self::Brother->value => 'Brat',
			self::Sister->value => 'Siostra',
			self::Friend->value => 'Przyjaciel',
			self::Neighbor->value => 'Sąsiad',
			self::Lawyer->value => 'Adwokat',
			self::Doctor->value => 'Lekarz',
			self::Notary->value => 'Notariusz',
			self::Executor->value => 'Egzekutor',
			self::PowerOfAttorney->value => 'Pełnomocnik',

			// Medical
			self::PrimaryPhysician->value => 'Lekarz Podstawowej Opieki',
			self::Specialist->value => 'Specjalista',
			self::Dentist->value => 'Dentysta',
			self::Therapist->value => 'Terapeuta',
			self::Pharmacist->value => 'Farmaceuta',
			self::Caregiver->value => 'Opiekun',
			self::HomeCareAide->value => 'Asystent Opieki Domowej',
			self::NursingContact->value => 'Kontakt Pielęgniarski',

			// Education
			self::Teacher->value => 'Nauczyciel',
			self::Professor->value => 'Profesor',
			self::Principal->value => 'Dyrektor Szkoły',
			self::Counselor->value => 'Doradca',
			self::Tutor->value => 'Korepetytor',
			self::Coach->value => 'Trener',

			// Other Business
			self::Consultant->value => 'Konsultant',
			self::Contractor->value => 'Wykonawca',
			self::Freelancer->value => 'Freelancer',
			self::BusinessPartner->value => 'Partner Biznesowy',
			self::Investor->value => 'Inwestor',
			self::BoardMember->value => 'Członek Zarządu',
			self::Shareholder->value => 'Akcjonariusz',
			self::Auditor->value => 'Audytor',
			self::Banker->value => 'Bankier',
			self::InsuranceAgent->value => 'Agent Ubezpieczeniowy',
			self::RealEstateAgent->value => 'Agent Nieruchomości',

			// Default
			self::Other->value => 'Inny',
			self::Unspecified->value => 'Nieokreślony',
		];
	}

	public static function labelsRu(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Аккаунт-менеджер',
			self::BillingContact->value => 'Контакт по выставлению счетов',
			self::PaymentContact->value => 'Контакт по платежам',
			self::InvoiceContact->value => 'Контакт по счетам',
			self::CreditController->value => 'Контроллер кредита',
			self::FinancialAdvisor->value => 'Финансовый консультант',
			self::TaxContact->value => 'Налоговый контакт',
			self::AuditContact->value => 'Аудиторский контакт',
			self::BudgetManager->value => 'Менеджер по бюджету',
			self::CostController->value => 'Контроллер затрат',

			// CRM
			self::PrimaryContact->value => 'Основной контакт',
			self::SalesContact->value => 'Контакт по продажам',
			self::KeyAccountManager->value => 'Ключевой аккаунт-менеджер',
			self::LeadContact->value => 'Контакт по лиду',
			self::OpportunityOwner->value => 'Владелец возможности',
			self::CustomerSuccessManager->value => 'Менеджер по успеху клиентов',
			self::SupportContact->value => 'Контакт поддержки',
			self::RenewalContact->value => 'Контакт по продлению',
			self::UpsellContact->value => 'Контакт по допродажам',
			self::ReferralContact->value => 'Контакт по рекомендациям',

			// HRM
			self::HRManager->value => 'HR-менеджер',
			self::Recruiter->value => 'Рекрутер',
			self::PayrollContact->value => 'Контакт по заработной плате',
			self::BenefitsAdmin->value => 'Администратор льгот',
			self::TrainingCoordinator->value => 'Координатор обучения',
			self::PerformanceManager->value => 'Менеджер по производительности',
			self::OnboardingContact->value => 'Контакт по адаптации',
			self::OffboardingContact->value => 'Контакт по увольнению',
			self::ComplianceOfficer->value => 'Специалист по соответствию',
			self::SafetyOfficer->value => 'Специалист по безопасности',

			// IT
			self::SystemAdmin->value => 'Системный администратор',
			self::ITManager->value => 'IT-менеджер',
			self::HelpDeskContact->value => 'Контакт службы поддержки',
			self::NetworkAdmin->value => 'Сетевой администратор',
			self::SecurityOfficer->value => 'Специалист по безопасности',
			self::DatabaseAdmin->value => 'Администратор базы данных',
			self::DevelopmentLead->value => 'Ведущий разработчик',
			self::TechnicalSupport->value => 'Техническая поддержка',
			self::InfrastructureManager->value => 'Менеджер по инфраструктуре',
			self::ProcurementContact->value => 'Контакт по закупкам IT',

			// Project Management
			self::ProjectManager->value => 'Менеджер проекта',
			self::TeamLead->value => 'Руководитель команды',
			self::TeamMember->value => 'Член команды',
			self::Stakeholder->value => 'Заинтересованная сторона',
			self::Sponsor->value => 'Спонсор',
			self::ProductOwner->value => 'Владелец продукта',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'Менеджер по доставке',
			self::QualityAssurance->value => 'Обеспечение качества',
			self::ResourceManager->value => 'Менеджер по ресурсам',

			// Administrative
			self::OfficeManager->value => 'Офис-менеджер',
			self::ExecutiveAssistant->value => 'Исполнительный помощник',
			self::FacilityManager->value => 'Менеджер по объектам',
			self::OperationsManager->value => 'Менеджер по операциям',
			self::LogisticsContact->value => 'Контакт по логистике',
			self::ProcurementManager->value => 'Менеджер по закупкам',
			self::InventoryManager->value => 'Менеджер по запасам',
			self::SupplyChainContact->value => 'Контакт по цепочке поставок',
			self::VendorManager->value => 'Менеджер по поставщикам',
			self::LegalContact->value => 'Юридический контакт',

			// Personal
			self::EmergencyContact->value => 'Контакт для экстренных случаев',
			self::Spouse->value => 'Супруг/Супруга',
			self::Partner->value => 'Партнер',
			self::Fiancee->value => 'Невеста',
			self::Wife->value => 'Жена',
			self::Husband->value => 'Муж',
			self::Parent->value => 'Родитель',
			self::Mother->value => 'Мать',
			self::Father->value => 'Отец',
			self::Guardian->value => 'Опекун',
			self::Child->value => 'Ребенок',
			self::Son->value => 'Сын',
			self::Daughter->value => 'Дочь',
			self::Sibling->value => 'Брат/Сестра',
			self::Brother->value => 'Брат',
			self::Sister->value => 'Сестра',
			self::Friend->value => 'Друг',
			self::Neighbor->value => 'Сосед',
			self::Lawyer->value => 'Адвокат',
			self::Doctor->value => 'Врач',
			self::Notary->value => 'Нотариус',
			self::Executor->value => 'Исполнитель завещания',
			self::PowerOfAttorney->value => 'Доверенное лицо',

			// Medical
			self::PrimaryPhysician->value => 'Основной врач',
			self::Specialist->value => 'Специалист',
			self::Dentist->value => 'Стоматолог',
			self::Therapist->value => 'Терапевт',
			self::Pharmacist->value => 'Фармацевт',
			self::Caregiver->value => 'Сиделка',
			self::HomeCareAide->value => 'Помощник по уходу на дому',
			self::NursingContact->value => 'Медсестринский контакт',

			// Education
			self::Teacher->value => 'Учитель',
			self::Professor->value => 'Профессор',
			self::Principal->value => 'Директор школы',
			self::Counselor->value => 'Консультант',
			self::Tutor->value => 'Репетитор',
			self::Coach->value => 'Тренер',

			// Other Business
			self::Consultant->value => 'Консультант',
			self::Contractor->value => 'Подрядчик',
			self::Freelancer->value => 'Фрилансер',
			self::BusinessPartner->value => 'Бизнес-партнер',
			self::Investor->value => 'Инвестор',
			self::BoardMember->value => 'Член правления',
			self::Shareholder->value => 'Акционер',
			self::Auditor->value => 'Аудитор',
			self::Banker->value => 'Банкир',
			self::InsuranceAgent->value => 'Страховой агент',
			self::RealEstateAgent->value => 'Агент по недвижимости',

			// Default
			self::Other->value => 'Другое',
			self::Unspecified->value => 'Не указано',
		];
	}

	public static function labelsTr(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Hesap Yöneticisi',
			self::BillingContact->value => 'Fatura İrtibatı',
			self::PaymentContact->value => 'Ödeme İrtibatı',
			self::InvoiceContact->value => 'Fatura İrtibatı',
			self::CreditController->value => 'Kredi Kontrolörü',
			self::FinancialAdvisor->value => 'Finansal Danışman',
			self::TaxContact->value => 'Vergi İrtibatı',
			self::AuditContact->value => 'Denetim İrtibatı',
			self::BudgetManager->value => 'Bütçe Yöneticisi',
			self::CostController->value => 'Maliyet Kontrolörü',

			// CRM
			self::PrimaryContact->value => 'Birincil İrtibat',
			self::SalesContact->value => 'Satış İrtibatı',
			self::KeyAccountManager->value => 'Ana Hesap Yöneticisi',
			self::LeadContact->value => 'Lead İrtibatı',
			self::OpportunityOwner->value => 'Fırsat Sahibi',
			self::CustomerSuccessManager->value => 'Müşteri Başarı Yöneticisi',
			self::SupportContact->value => 'Destek İrtibatı',
			self::RenewalContact->value => 'Yenileme İrtibatı',
			self::UpsellContact->value => 'Üst Satış İrtibatı',
			self::ReferralContact->value => 'Referans İrtibatı',

			// HRM
			self::HRManager->value => 'İK Yöneticisi',
			self::Recruiter->value => 'İşe Alım Uzmanı',
			self::PayrollContact->value => 'Maaş İrtibatı',
			self::BenefitsAdmin->value => 'Yan Haklar Yöneticisi',
			self::TrainingCoordinator->value => 'Eğitim Koordinatörü',
			self::PerformanceManager->value => 'Performans Yöneticisi',
			self::OnboardingContact->value => 'İşe Alıştırma İrtibatı',
			self::OffboardingContact->value => 'İşten Çıkış İrtibatı',
			self::ComplianceOfficer->value => 'Uyum Görevlisi',
			self::SafetyOfficer->value => 'Güvenlik Görevlisi',

			// IT
			self::SystemAdmin->value => 'Sistem Yöneticisi',
			self::ITManager->value => 'BT Yöneticisi',
			self::HelpDeskContact->value => 'Yardım Masası İrtibatı',
			self::NetworkAdmin->value => 'Ağ Yöneticisi',
			self::SecurityOfficer->value => 'Güvenlik Görevlisi',
			self::DatabaseAdmin->value => 'Veritabanı Yöneticisi',
			self::DevelopmentLead->value => 'Geliştirme Lideri',
			self::TechnicalSupport->value => 'Teknik Destek',
			self::InfrastructureManager->value => 'Altyapı Yöneticisi',
			self::ProcurementContact->value => 'BT Tedarik İrtibatı',

			// Project Management
			self::ProjectManager->value => 'Proje Yöneticisi',
			self::TeamLead->value => 'Takım Lideri',
			self::TeamMember->value => 'Takım Üyesi',
			self::Stakeholder->value => 'Paydaş',
			self::Sponsor->value => 'Sponsor',
			self::ProductOwner->value => 'Ürün Sahibi',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => 'Teslimat Yöneticisi',
			self::QualityAssurance->value => 'Kalite Güvence',
			self::ResourceManager->value => 'Kaynak Yöneticisi',

			// Administrative
			self::OfficeManager->value => 'Ofis Yöneticisi',
			self::ExecutiveAssistant->value => 'Yönetici Asistanı',
			self::FacilityManager->value => 'Tesis Yöneticisi',
			self::OperationsManager->value => 'Operasyon Yöneticisi',
			self::LogisticsContact->value => 'Lojistik İrtibatı',
			self::ProcurementManager->value => 'Tedarik Yöneticisi',
			self::InventoryManager->value => 'Envanter Yöneticisi',
			self::SupplyChainContact->value => 'Tedarik Zinciri İrtibatı',
			self::VendorManager->value => 'Tedarikçi Yöneticisi',
			self::LegalContact->value => 'Hukuk İrtibatı',

			// Personal
			self::EmergencyContact->value => 'Acil Durum İrtibatı',
			self::Spouse->value => 'Eş',
			self::Partner->value => 'Partner',
			self::Fiancee->value => 'Nişanlı',
			self::Wife->value => 'Karı',
			self::Husband->value => 'Koca',
			self::Parent->value => 'Ebeveyn',
			self::Mother->value => 'Anne',
			self::Father->value => 'Baba',
			self::Guardian->value => 'Vasi',
			self::Child->value => 'Çocuk',
			self::Son->value => 'Oğul',
			self::Daughter->value => 'Kız',
			self::Sibling->value => 'Kardeş',
			self::Brother->value => 'Erkek Kardeş',
			self::Sister->value => 'Kız Kardeş',
			self::Friend->value => 'Arkadaş',
			self::Neighbor->value => 'Komşu',
			self::Lawyer->value => 'Avukat',
			self::Doctor->value => 'Doktor',
			self::Notary->value => 'Noter',
			self::Executor->value => 'Vasiyet Yürütücüsü',
			self::PowerOfAttorney->value => 'Vekil',

			// Medical
			self::PrimaryPhysician->value => 'Birinci Basamak Hekimi',
			self::Specialist->value => 'Uzman',
			self::Dentist->value => 'Diş Hekimi',
			self::Therapist->value => 'Terapist',
			self::Pharmacist->value => 'Eczacı',
			self::Caregiver->value => 'Bakıcı',
			self::HomeCareAide->value => 'Evde Bakım Asistanı',
			self::NursingContact->value => 'Hemşirelik İrtibatı',

			// Education
			self::Teacher->value => 'Öğretmen',
			self::Professor->value => 'Profesör',
			self::Principal->value => 'Okul Müdürü',
			self::Counselor->value => 'Danışman',
			self::Tutor->value => 'Özel Öğretmen',
			self::Coach->value => 'Koç',

			// Other Business
			self::Consultant->value => 'Danışman',
			self::Contractor->value => 'Müteahhit',
			self::Freelancer->value => 'Serbest Çalışan',
			self::BusinessPartner->value => 'İş Ortağı',
			self::Investor->value => 'Yatırımcı',
			self::BoardMember->value => 'Yönetim Kurulu Üyesi',
			self::Shareholder->value => 'Hissedar',
			self::Auditor->value => 'Denetçi',
			self::Banker->value => 'Bankacı',
			self::InsuranceAgent->value => 'Sigorta Acentesi',
			self::RealEstateAgent->value => 'Emlakçı',

			// Default
			self::Other->value => 'Diğer',
			self::Unspecified->value => 'Belirtilmemiş',
		];
	}

	public static function labelsZh(): array
	{
		return [
			// Finance
			self::AccountManager->value => '客户经理',
			self::BillingContact->value => '计费联系人',
			self::PaymentContact->value => '付款联系人',
			self::InvoiceContact->value => '发票联系人',
			self::CreditController->value => '信用控制员',
			self::FinancialAdvisor->value => '财务顾问',
			self::TaxContact->value => '税务联系人',
			self::AuditContact->value => '审计联系人',
			self::BudgetManager->value => '预算经理',
			self::CostController->value => '成本控制员',

			// CRM
			self::PrimaryContact->value => '主要联系人',
			self::SalesContact->value => '销售联系人',
			self::KeyAccountManager->value => '重点客户经理',
			self::LeadContact->value => '潜在客户联系人',
			self::OpportunityOwner->value => '商机负责人',
			self::CustomerSuccessManager->value => '客户成功经理',
			self::SupportContact->value => '支持联系人',
			self::RenewalContact->value => '续订联系人',
			self::UpsellContact->value => '追加销售联系人',
			self::ReferralContact->value => '推荐联系人',

			// HRM
			self::HRManager->value => '人力资源经理',
			self::Recruiter->value => '招聘人员',
			self::PayrollContact->value => '薪资联系人',
			self::BenefitsAdmin->value => '福利管理员',
			self::TrainingCoordinator->value => '培训协调员',
			self::PerformanceManager->value => '绩效经理',
			self::OnboardingContact->value => '入职联系人',
			self::OffboardingContact->value => '离职联系人',
			self::ComplianceOfficer->value => '合规官',
			self::SafetyOfficer->value => '安全官',

			// IT
			self::SystemAdmin->value => '系统管理员',
			self::ITManager->value => 'IT经理',
			self::HelpDeskContact->value => '服务台联系人',
			self::NetworkAdmin->value => '网络管理员',
			self::SecurityOfficer->value => '安全官',
			self::DatabaseAdmin->value => '数据库管理员',
			self::DevelopmentLead->value => '开发负责人',
			self::TechnicalSupport->value => '技术支持',
			self::InfrastructureManager->value => '基础设施经理',
			self::ProcurementContact->value => 'IT采购联系人',

			// Project Management
			self::ProjectManager->value => '项目经理',
			self::TeamLead->value => '团队负责人',
			self::TeamMember->value => '团队成员',
			self::Stakeholder->value => '利益相关者',
			self::Sponsor->value => '赞助人',
			self::ProductOwner->value => '产品负责人',
			self::ScrumMaster->value => 'Scrum Master',
			self::DeliveryManager->value => '交付经理',
			self::QualityAssurance->value => '质量保证',
			self::ResourceManager->value => '资源经理',

			// Administrative
			self::OfficeManager->value => '办公室经理',
			self::ExecutiveAssistant->value => '行政助理',
			self::FacilityManager->value => '设施经理',
			self::OperationsManager->value => '运营经理',
			self::LogisticsContact->value => '物流联系人',
			self::ProcurementManager->value => '采购经理',
			self::InventoryManager->value => '库存经理',
			self::SupplyChainContact->value => '供应链联系人',
			self::VendorManager->value => '供应商经理',
			self::LegalContact->value => '法律联系人',

			// Personal
			self::EmergencyContact->value => '紧急联系人',
			self::Spouse->value => '配偶',
			self::Partner->value => '伴侣',
			self::Fiancee->value => '未婚妻',
			self::Wife->value => '妻子',
			self::Husband->value => '丈夫',
			self::Parent->value => '父母',
			self::Mother->value => '母亲',
			self::Father->value => '父亲',
			self::Guardian->value => '监护人',
			self::Child->value => '孩子',
			self::Son->value => '儿子',
			self::Daughter->value => '女儿',
			self::Sibling->value => '兄弟姐妹',
			self::Brother->value => '兄弟',
			self::Sister->value => '姐妹',
			self::Friend->value => '朋友',
			self::Neighbor->value => '邻居',
			self::Lawyer->value => '律师',
			self::Doctor->value => '医生',
			self::Notary->value => '公证人',
			self::Executor->value => '遗嘱执行人',
			self::PowerOfAttorney->value => '授权代理人',

			// Medical
			self::PrimaryPhysician->value => '主治医生',
			self::Specialist->value => '专科医生',
			self::Dentist->value => '牙医',
			self::Therapist->value => '治疗师',
			self::Pharmacist->value => '药剂师',
			self::Caregiver->value => '护理员',
			self::HomeCareAide->value => '家庭护理助理',
			self::NursingContact->value => '护理联系人',

			// Education
			self::Teacher->value => '老师',
			self::Professor->value => '教授',
			self::Principal->value => '校长',
			self::Counselor->value => '辅导员',
			self::Tutor->value => '家教',
			self::Coach->value => '教练',

			// Other Business
			self::Consultant->value => '顾问',
			self::Contractor->value => '承包商',
			self::Freelancer->value => '自由职业者',
			self::BusinessPartner->value => '商业伙伴',
			self::Investor->value => '投资者',
			self::BoardMember->value => '董事会成员',
			self::Shareholder->value => '股东',
			self::Auditor->value => '审计师',
			self::Banker->value => '银行家',
			self::InsuranceAgent->value => '保险代理人',
			self::RealEstateAgent->value => '房地产经纪人',

			// Default
			self::Other->value => '其他',
			self::Unspecified->value => '未指定',
		];
	}

	public static function descriptionsEn(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Manages financial accounts and relationships with clients',
			self::BillingContact->value => 'Primary contact for billing, invoicing, and payment collection',
			self::PaymentContact->value => 'Responsible for payment processing, approvals, and financial transactions',
			self::InvoiceContact->value => 'Handles invoice creation, distribution, and payment tracking',
			self::CreditController->value => 'Manages credit limits, credit risk assessment, and credit approvals',
			self::FinancialAdvisor->value => 'Provides financial planning, investment advice, and wealth management',
			self::TaxContact->value => 'Handles tax-related matters, filings, and compliance',
			self::AuditContact->value => 'Point of contact for internal and external audit processes',
			self::BudgetManager->value => 'Oversees budget planning, allocation, and financial forecasting',
			self::CostController->value => 'Monitors and controls organizational costs and expenses',

			// CRM
			self::PrimaryContact->value => 'Main point of contact for all business communications',
			self::SalesContact->value => 'Handles sales inquiries, negotiations, and deal closures',
			self::KeyAccountManager->value => 'Manages strategic relationships with important clients',
			self::LeadContact->value => 'Primary contact for potential customers and lead nurturing',
			self::OpportunityOwner->value => 'Responsible for managing and closing sales opportunities',
			self::CustomerSuccessManager->value => 'Ensures customer satisfaction and long-term success',
			self::SupportContact->value => 'Provides technical and customer support services',
			self::RenewalContact->value => 'Manages contract renewals and subscription extensions',
			self::UpsellContact->value => 'Identifies and executes opportunities for additional sales',
			self::ReferralContact->value => 'Handles referral programs and partner relationships',

			// HRM
			self::HRManager->value => 'Oversees human resources policies, procedures, and staff management',
			self::Recruiter->value => 'Responsible for talent acquisition and recruitment processes',
			self::PayrollContact->value => 'Manages salary processing, benefits, and compensation',
			self::BenefitsAdmin->value => 'Administers employee benefits programs and insurance',
			self::TrainingCoordinator->value => 'Organizes and coordinates employee training programs',
			self::PerformanceManager->value => 'Manages employee performance reviews and development',
			self::OnboardingContact->value => 'Facilitates new employee integration and orientation',
			self::OffboardingContact->value => 'Manages employee exit processes and documentation',
			self::ComplianceOfficer->value => 'Ensures organizational compliance with laws and regulations',
			self::SafetyOfficer->value => 'Oversees workplace safety and health regulations',

			// IT
			self::SystemAdmin->value => 'Manages computer systems, servers, and IT infrastructure',
			self::ITManager->value => 'Oversees information technology strategy and operations',
			self::HelpDeskContact->value => 'Provides technical assistance and user support',
			self::NetworkAdmin->value => 'Manages network infrastructure and connectivity',
			self::SecurityOfficer->value => 'Responsible for information security and data protection',
			self::DatabaseAdmin->value => 'Manages database systems and data storage',
			self::DevelopmentLead->value => 'Leads software development teams and projects',
			self::TechnicalSupport->value => 'Provides technical troubleshooting and assistance',
			self::InfrastructureManager->value => 'Oversees IT infrastructure and hardware management',
			self::ProcurementContact->value => 'Handles IT equipment and software purchases',

			// Project Management
			self::ProjectManager->value => 'Leads and manages project execution and delivery',
			self::TeamLead->value => 'Supervises team members and coordinates work activities',
			self::TeamMember->value => 'Participates in project work and team activities',
			self::Stakeholder->value => 'Has interest or investment in project outcomes',
			self::Sponsor->value => 'Provides funding and support for projects',
			self::ProductOwner->value => 'Defines product vision and manages product backlog',
			self::ScrumMaster->value => 'Facilitates agile processes and removes impediments',
			self::DeliveryManager->value => 'Ensures project delivery and client satisfaction',
			self::QualityAssurance->value => 'Tests products and ensures quality standards',
			self::ResourceManager->value => 'Allocates and manages project resources',

			// Administrative
			self::OfficeManager->value => 'Manages office operations and administrative staff',
			self::ExecutiveAssistant->value => 'Provides administrative support to executives',
			self::FacilityManager->value => 'Oversees building maintenance and facility operations',
			self::OperationsManager->value => 'Manages daily business operations and processes',
			self::LogisticsContact->value => 'Handles transportation, shipping, and logistics',
			self::ProcurementManager->value => 'Manages purchasing and supplier relationships',
			self::InventoryManager->value => 'Oversees stock levels and inventory control',
			self::SupplyChainContact->value => 'Manages supply chain and distribution networks',
			self::VendorManager->value => 'Oversees vendor relationships and performance',
			self::LegalContact->value => 'Handles legal matters and contract review',

			// Personal & Family
			self::EmergencyContact->value => 'Person to contact in case of emergency or urgent situations',
			self::Spouse->value => 'Legally married partner for personal and legal matters',
			self::Partner->value => 'Domestic or life partner for personal relationship',
			self::Fiancee->value => 'Person engaged to be married',
			self::Wife->value => 'Female spouse in a marriage',
			self::Husband->value => 'Male spouse in a marriage',
			self::Parent->value => 'Biological or adoptive parent for family matters',
			self::Mother->value => 'Female parent for family and emergency contact',
			self::Father->value => 'Male parent for family and emergency contact',
			self::Guardian->value => 'Legal guardian for minor children or dependents',
			self::Child->value => 'Son or daughter for family relationship',
			self::Son->value => 'Male child for family relationship',
			self::Daughter->value => 'Female child for family relationship',
			self::Sibling->value => 'Brother or sister for family relationship',
			self::Brother->value => 'Male sibling for family relationship',
			self::Sister->value => 'Female sibling for family relationship',
			self::Friend->value => 'Personal friend for social and emergency contact',
			self::Neighbor->value => 'Person living nearby for local contact',
			self::Lawyer->value => 'Legal professional for legal advice and representation',
			self::Doctor->value => 'Medical professional for healthcare needs',
			self::Notary->value => 'Official authorized to witness and certify documents',
			self::Executor->value => 'Person appointed to execute a will after death',
			self::PowerOfAttorney->value => 'Person authorized to act on another\'s behalf',

			// Medical & Care
			self::PrimaryPhysician->value => 'Main doctor for general healthcare and referrals',
			self::Specialist->value => 'Medical specialist for specific health conditions',
			self::Dentist->value => 'Dental professional for oral health care',
			self::Therapist->value => 'Mental health or physical therapy professional',
			self::Pharmacist->value => 'Medication dispensing and pharmaceutical advice',
			self::Caregiver->value => 'Provides care and assistance for daily living',
			self::HomeCareAide->value => 'Assists with home-based care and support',
			self::NursingContact->value => 'Nursing professional for medical care',

			// Education
			self::Teacher->value => 'Educational professional for teaching and instruction',
			self::Professor->value => 'University-level educator and researcher',
			self::Principal->value => 'School administrator and educational leader',
			self::Counselor->value => 'Provides guidance and counseling services',
			self::Tutor->value => 'Provides individualized academic instruction',
			self::Coach->value => 'Sports or performance trainer and mentor',

			// Other Business
			self::Consultant->value => 'Provides expert advice and consulting services',
			self::Contractor->value => 'Independent worker contracted for specific projects',
			self::Freelancer->value => 'Self-employed professional offering services',
			self::BusinessPartner->value => 'Strategic partner in business ventures',
			self::Investor->value => 'Provides funding for business growth',
			self::BoardMember->value => 'Member of company board of directors',
			self::Shareholder->value => 'Owner of company shares or stock',
			self::Auditor->value => 'Independent financial examiner',
			self::Banker->value => 'Financial institution representative',
			self::InsuranceAgent->value => 'Insurance policy sales and service',
			self::RealEstateAgent->value => 'Property sales and rental services',

			// Default
			self::Other->value => 'Other relationship not covered by specific categories',
			self::Unspecified->value => 'Relationship type not specified or unknown',
		];
	}

	public static function descriptionsPtBr(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Gerencia contas financeiras e relacionamentos com clientes',
			self::BillingContact->value => 'Contato principal para faturamento, cobrança e recebimento',
			self::PaymentContact->value => 'Responsável pelo processamento de pagamentos e transações financeiras',
			self::InvoiceContact->value => 'Lida com criação, distribuição e acompanhamento de faturas',
			self::CreditController->value => 'Gerencia limites de crédito e avaliação de risco',
			self::FinancialAdvisor->value => 'Fornece planejamento financeiro e assessoria de investimentos',
			self::TaxContact->value => 'Lida com questões fiscais e conformidade tributária',
			self::AuditContact->value => 'Ponto de contato para processos de auditoria',
			self::BudgetManager->value => 'Supervisiona planejamento orçamentário e previsões financeiras',
			self::CostController->value => 'Monitora e controla custos organizacionais',

			// CRM
			self::PrimaryContact->value => 'Principal ponto de contato para comunicações comerciais',
			self::SalesContact->value => 'Lida com consultas de vendas, negociações e fechamentos',
			self::KeyAccountManager->value => 'Gerencia relacionamentos com clientes importantes',
			self::LeadContact->value => 'Contato principal para potenciais clientes',
			self::OpportunityOwner->value => 'Responsável por gerenciar oportunidades de vendas',
			self::CustomerSuccessManager->value => 'Garante satisfação e sucesso do cliente',
			self::SupportContact->value => 'Fornece suporte técnico e atendimento ao cliente',
			self::RenewalContact->value => 'Gerencia renovações de contratos',
			self::UpsellContact->value => 'Identifica oportunidades de vendas adicionais',
			self::ReferralContact->value => 'Lida com programas de indicação e parcerias',

			// HRM
			self::HRManager->value => 'Supervisiona políticas de RH e gestão de pessoal',
			self::Recruiter->value => 'Responsável por aquisição de talentos e recrutamento',
			self::PayrollContact->value => 'Gerencia processamento salarial e benefícios',
			self::BenefitsAdmin->value => 'Administra programas de benefícios para funcionários',
			self::TrainingCoordinator->value => 'Organiza programas de treinamento',
			self::PerformanceManager->value => 'Gerencia avaliações de desempenho',
			self::OnboardingContact->value => 'Facilita integração de novos funcionários',
			self::OffboardingContact->value => 'Gerencia processos de desligamento',
			self::ComplianceOfficer->value => 'Garante conformidade com leis e regulamentos',
			self::SafetyOfficer->value => 'Supervisiona segurança no trabalho',

			// IT
			self::SystemAdmin->value => 'Gerencia sistemas de computador e infraestrutura de TI',
			self::ITManager->value => 'Supervisiona estratégia e operações de TI',
			self::HelpDeskContact->value => 'Fornece assistência técnica e suporte',
			self::NetworkAdmin->value => 'Gerencia infraestrutura de rede',
			self::SecurityOfficer->value => 'Responsável por segurança da informação',
			self::DatabaseAdmin->value => 'Gerencia sistemas de banco de dados',
			self::DevelopmentLead->value => 'Lidera equipes de desenvolvimento de software',
			self::TechnicalSupport->value => 'Fornece suporte técnico e solução de problemas',
			self::InfrastructureManager->value => 'Supervisiona infraestrutura de TI',
			self::ProcurementContact->value => 'Lida com compras de equipamentos de TI',

			// Project Management
			self::ProjectManager->value => 'Lidera e gerencia execução de projetos',
			self::TeamLead->value => 'Supervisiona membros da equipe e coordena atividades',
			self::TeamMember->value => 'Participa em trabalhos de projeto',
			self::Stakeholder->value => 'Tem interesse nos resultados do projeto',
			self::Sponsor->value => 'Fornece financiamento para projetos',
			self::ProductOwner->value => 'Define visão do produto e gerencia backlog',
			self::ScrumMaster->value => 'Facilita processos ágeis',
			self::DeliveryManager->value => 'Garante entrega de projetos',
			self::QualityAssurance->value => 'Testa produtos e garante qualidade',
			self::ResourceManager->value => 'Aloca e gerencia recursos de projeto',

			// Administrative
			self::OfficeManager->value => 'Gerencia operações de escritório',
			self::ExecutiveAssistant->value => 'Fornece suporte administrativo a executivos',
			self::FacilityManager->value => 'Supervisiona manutenção de instalações',
			self::OperationsManager->value => 'Gerencia operações diárias do negócio',
			self::LogisticsContact->value => 'Lida com transporte e logística',
			self::ProcurementManager->value => 'Gerencia compras e fornecedores',
			self::InventoryManager->value => 'Supervisiona controle de estoque',
			self::SupplyChainContact->value => 'Gerencia cadeia de suprimentos',
			self::VendorManager->value => 'Supervisiona relacionamento com fornecedores',
			self::LegalContact->value => 'Lida com questões legais',

			// Personal & Family
			self::EmergencyContact->value => 'Pessoa para contatar em caso de emergência',
			self::Spouse->value => 'Cônjuge legal para assuntos pessoais',
			self::Partner->value => 'Parceiro de vida para relacionamento pessoal',
			self::Fiancee->value => 'Pessoa noiva para casamento',
			self::Wife->value => 'Esposa em um casamento',
			self::Husband->value => 'Marido em um casamento',
			self::Parent->value => 'Pai ou mãe para assuntos familiares',
			self::Mother->value => 'Mãe para contato familiar',
			self::Father->value => 'Pai para contato familiar',
			self::Guardian->value => 'Guardião legal para menores',
			self::Child->value => 'Filho ou filha para relacionamento familiar',
			self::Son->value => 'Filho para relacionamento familiar',
			self::Daughter->value => 'Filha para relacionamento familiar',
			self::Sibling->value => 'Irmão ou irmã para relacionamento familiar',
			self::Brother->value => 'Irmão para relacionamento familiar',
			self::Sister->value => 'Irmã para relacionamento familiar',
			self::Friend->value => 'Amigo para contato social',
			self::Neighbor->value => 'Vizinho para contato local',
			self::Lawyer->value => 'Profissional legal para assessoria jurídica',
			self::Doctor->value => 'Profissional médico para cuidados de saúde',
			self::Notary->value => 'Autorizado a testemunhar e certificar documentos',
			self::Executor->value => 'Pessoa designada para executar testamento',
			self::PowerOfAttorney->value => 'Autorizado a agir em nome de outra pessoa',

			// Medical & Care
			self::PrimaryPhysician->value => 'Médico principal para cuidados gerais de saúde',
			self::Specialist->value => 'Especialista médico para condições específicas',
			self::Dentist->value => 'Profissional dental para saúde bucal',
			self::Therapist->value => 'Profissional de saúde mental ou fisioterapia',
			self::Pharmacist->value => 'Dispensação de medicamentos e aconselhamento',
			self::Caregiver->value => 'Fornece cuidados e assistência diária',
			self::HomeCareAide->value => 'Assiste com cuidados domiciliares',
			self::NursingContact->value => 'Profissional de enfermagem para cuidados médicos',

			// Education
			self::Teacher->value => 'Profissional educacional para ensino',
			self::Professor->value => 'Educador universitário e pesquisador',
			self::Principal->value => 'Administrador escolar e líder educacional',
			self::Counselor->value => 'Fornece serviços de orientação',
			self::Tutor->value => 'Fornece instrução acadêmica individualizada',
			self::Coach->value => 'Treinador esportivo ou de performance',

			// Other Business
			self::Consultant->value => 'Fornece assessoria especializada',
			self::Contractor->value => 'Trabalhador independente para projetos específicos',
			self::Freelancer->value => 'Profissional autônomo oferecendo serviços',
			self::BusinessPartner->value => 'Parceiro estratégico em empreendimentos',
			self::Investor->value => 'Fornece financiamento para crescimento empresarial',
			self::BoardMember->value => 'Membro do conselho de administração',
			self::Shareholder->value => 'Proprietário de ações da empresa',
			self::Auditor->value => 'Examinador financeiro independente',
			self::Banker->value => 'Representante de instituição financeira',
			self::InsuranceAgent->value => 'Vendas e serviços de seguros',
			self::RealEstateAgent->value => 'Serviços de venda e aluguel de propriedades',

			// Default
			self::Other->value => 'Outro relacionamento não coberto por categorias específicas',
			self::Unspecified->value => 'Tipo de relacionamento não especificado ou desconhecido',
		];
	}

	public static function descriptionsEs(): array
	{
		return [
			// Finance
			self::AccountManager->value => 'Gestiona cuentas financieras y relaciones con clientes',
			self::BillingContact->value => 'Contacto principal para facturación y cobro de pagos',
			self::PaymentContact->value => 'Responsable del procesamiento de pagos y transacciones',
			self::InvoiceContact->value => 'Maneja creación, distribución y seguimiento de facturas',
			self::CreditController->value => 'Gestiona límites de crédito y evaluación de riesgo',
			self::FinancialAdvisor->value => 'Proporciona planificación financiera y asesoramiento',
			self::TaxContact->value => 'Maneja asuntos fiscales y cumplimiento tributario',
			self::AuditContact->value => 'Punto de contacto para procesos de auditoría',
			self::BudgetManager->value => 'Supervisa planificación presupuestaria y pronósticos',
			self::CostController->value => 'Controla costos y gastos organizacionales',

			// CRM
			self::PrimaryContact->value => 'Punto de contacto principal para comunicaciones',
			self::SalesContact->value => 'Maneja consultas de ventas y negociaciones',
			self::KeyAccountManager->value => 'Gestiona relaciones con clientes importantes',
			self::LeadContact->value => 'Contacto principal para clientes potenciales',
			self::OpportunityOwner->value => 'Responsable de gestionar oportunidades de venta',
			self::CustomerSuccessManager->value => 'Garantiza satisfacción del cliente',
			self::SupportContact->value => 'Proporciona soporte técnico y atención al cliente',
			self::RenewalContact->value => 'Gestiona renovaciones de contratos',
			self::UpsellContact->value => 'Identifica oportunidades de ventas adicionales',
			self::ReferralContact->value => 'Maneja programas de referencias y socios',

			// HRM
			self::HRManager->value => 'Supervisa políticas de recursos humanos',
			self::Recruiter->value => 'Responsable de adquisición de talento',
			self::PayrollContact->value => 'Gestiona procesamiento de nóminas',
			self::BenefitsAdmin->value => 'Administra programas de beneficios',
			self::TrainingCoordinator->value => 'Organiza programas de capacitación',
			self::PerformanceManager->value => 'Gestiona evaluaciones de desempeño',
			self::OnboardingContact->value => 'Facilita integración de nuevos empleados',
			self::OffboardingContact->value => 'Gestiona procesos de salida',
			self::ComplianceOfficer->value => 'Garantiza cumplimiento normativo',
			self::SafetyOfficer->value => 'Supervisa seguridad laboral',

			// IT
			self::SystemAdmin->value => 'Gestiona sistemas informáticos e infraestructura',
			self::ITManager->value => 'Supervisa estrategia y operaciones de TI',
			self::HelpDeskContact->value => 'Proporciona asistencia técnica',
			self::NetworkAdmin->value => 'Gestiona infraestructura de red',
			self::SecurityOfficer->value => 'Responsable de seguridad de información',
			self::DatabaseAdmin->value => 'Gestiona sistemas de base de datos',
			self::DevelopmentLead->value => 'Lidera equipos de desarrollo de software',
			self::TechnicalSupport->value => 'Proporciona soporte técnico',
			self::InfrastructureManager->value => 'Supervisa infraestructura de TI',
			self::ProcurementContact->value => 'Maneja compras de equipos de TI',

			// Project Management
			self::ProjectManager->value => 'Lidera y gestiona ejecución de proyectos',
			self::TeamLead->value => 'Supervisa miembros del equipo',
			self::TeamMember->value => 'Participa en trabajos de proyecto',
			self::Stakeholder->value => 'Tiene interés en resultados del proyecto',
			self::Sponsor->value => 'Proporciona financiamiento para proyectos',
			self::ProductOwner->value => 'Define visión del producto',
			self::ScrumMaster->value => 'Facilita procesos ágiles',
			self::DeliveryManager->value => 'Garantiza entrega de proyectos',
			self::QualityAssurance->value => 'Prueba productos y garantiza calidad',
			self::ResourceManager->value => 'Asigna y gestiona recursos',

			// Administrative
			self::OfficeManager->value => 'Gestiona operaciones de oficina',
			self::ExecutiveAssistant->value => 'Proporciona apoyo administrativo',
			self::FacilityManager->value => 'Supervisa mantenimiento de instalaciones',
			self::OperationsManager->value => 'Gestiona operaciones diarias',
			self::LogisticsContact->value => 'Maneja transporte y logística',
			self::ProcurementManager->value => 'Gestiona compras y proveedores',
			self::InventoryManager->value => 'Supervisa control de inventario',
			self::SupplyChainContact->value => 'Gestiona cadena de suministro',
			self::VendorManager->value => 'Supervisa relaciones con proveedores',
			self::LegalContact->value => 'Maneja asuntos legales',

			// Personal & Family
			self::EmergencyContact->value => 'Persona para contactar en emergencias',
			self::Spouse->value => 'Cónyuge legal para asuntos personales',
			self::Partner->value => 'Compañero de vida para relación personal',
			self::Fiancee->value => 'Persona comprometida para matrimonio',
			self::Wife->value => 'Esposa en un matrimonio',
			self::Husband->value => 'Esposo en un matrimonio',
			self::Parent->value => 'Padre o madre para asuntos familiares',
			self::Mother->value => 'Madre para contacto familiar',
			self::Father->value => 'Padre para contacto familiar',
			self::Guardian->value => 'Tutor legal para menores',
			self::Child->value => 'Hijo o hija para relación familiar',
			self::Son->value => 'Hijo para relación familiar',
			self::Daughter->value => 'Hija para relación familiar',
			self::Sibling->value => 'Hermano o hermana para relación familiar',
			self::Brother->value => 'Hermano para relación familiar',
			self::Sister->value => 'Hermana para relación familiar',
			self::Friend->value => 'Amigo para contacto social',
			self::Neighbor->value => 'Vecino para contacto local',
			self::Lawyer->value => 'Profesional legal para asesoría jurídica',
			self::Doctor->value => 'Profesional médico para atención de salud',
			self::Notary->value => 'Autorizado para certificar documentos',
			self::Executor->value => 'Persona designada para ejecutar testamento',
			self::PowerOfAttorney->value => 'Autorizado para actuar en nombre de otro',

			// Medical & Care
			self::PrimaryPhysician->value => 'Médico principal para atención general',
			self::Specialist->value => 'Especialista médico para condiciones específicas',
			self::Dentist->value => 'Profesional dental para salud oral',
			self::Therapist->value => 'Profesional de salud mental o terapia',
			self::Pharmacist->value => 'Dispensación de medicamentos',
			self::Caregiver->value => 'Proporciona cuidados y asistencia',
			self::HomeCareAide->value => 'Asiste con cuidados en el hogar',
			self::NursingContact->value => 'Profesional de enfermería para cuidados',

			// Education
			self::Teacher->value => 'Profesional educativo para enseñanza',
			self::Professor->value => 'Educador universitario e investigador',
			self::Principal->value => 'Administrador escolar y líder educativo',
			self::Counselor->value => 'Proporciona servicios de orientación',
			self::Tutor->value => 'Proporciona instrucción académica individual',
			self::Coach->value => 'Entrenador deportivo o de rendimiento',

			// Other Business
			self::Consultant->value => 'Proporciona asesoramiento especializado',
			self::Contractor->value => 'Trabajador independiente para proyectos',
			self::Freelancer->value => 'Profesional independiente ofreciendo servicios',
			self::BusinessPartner->value => 'Socio estratégico en emprendimientos',
			self::Investor->value => 'Proporciona financiamiento para crecimiento',
			self::BoardMember->value => 'Miembro del consejo de administración',
			self::Shareholder->value => 'Propietario de acciones de la empresa',
			self::Auditor->value => 'Examinador financiero independiente',
			self::Banker->value => 'Representante de institución financiera',
			self::InsuranceAgent->value => 'Ventas y servicios de seguros',
			self::RealEstateAgent->value => 'Servicios de venta y alquiler de propiedades',

			// Default
			self::Other->value => 'Otra relación no cubierta por categorías específicas',
			self::Unspecified->value => 'Tipo de relación no especificado o desconocido',
		];
	}
}
