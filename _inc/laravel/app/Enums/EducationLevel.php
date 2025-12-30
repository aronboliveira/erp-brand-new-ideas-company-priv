<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum EducationLevel: string
{
	// No formal education
	case None = 'none';
	case Informal = 'informal';
	case SelfTaught = 'self_taught';

		// Basic education
	case ElementarySchool = 'elementary_school';
	case PrimarySchool = 'primary_school';
	case MiddleSchool = 'middle_school';
	case JuniorHigh = 'junior_high';

		// Secondary education
	case HighSchool = 'high_school';
	case SecondarySchool = 'secondary_school';
	case TechnicalSecondary = 'technical_secondary';
	case VocationalSecondary = 'vocational_secondary';
	case GED = 'ged';
	case Equivalency = 'equivalency';

		// Post-secondary non-degree
	case SomeCollege = 'some_college';
	case Certificate = 'certificate';
	case Diploma = 'diploma';
	case VocationalTraining = 'vocational_training';
	case Apprenticeship = 'apprenticeship';
	case TradeSchool = 'trade_school';

		// Associate level
	case AssociateDegree = 'associate_degree';
	case FoundationDegree = 'foundation_degree';
	case HigherNationalDiploma = 'higher_national_diploma';

		// Bachelor level
	case BachelorDegree = 'bachelor_degree';
	case UndergraduateDegree = 'undergraduate_degree';
	case FirstDegree = 'first_degree';
	case Licentiate = 'licentiate';

		// Post-bachelor non-degree
	case PostBachelorCertificate = 'post_bachelor_certificate';
	case GraduateDiploma = 'graduate_diploma';
	case BachelorHonours = 'bachelor_honours';

		// Master level
	case MasterDegree = 'master_degree';
	case GraduateDegree = 'graduate_degree';
	case MBA = 'mba';
	case MFA = 'mfa';
	case LLM = 'llm';

		// Post-master non-degree
	case PostMasterCertificate = 'post_master_certificate';
	case SpecialistDegree = 'specialist_degree';

		// Doctoral level
	case DoctoralDegree = 'doctoral_degree';
	case PhD = 'phd';
	case ProfessionalDoctorate = 'professional_doctorate';
	case EdD = 'ed_d';
	case DBA = 'dba';
	case MD = 'md';
	case JD = 'jd';

		// Post-doctoral
	case Postdoctoral = 'postdoctoral';
	case ResearchFellow = 'research_fellow';

		// Professional certifications
	case ProfessionalCertification = 'professional_certification';
	case IndustryCertification = 'industry_certification';
	case TechnicalCertification = 'technical_certification';

		// Other
	case Other = 'other';
	case InProgress = 'in_progress';

	/**
	 * Normalize input to EducationLevel
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
			// No formal education
			'none', 'noformal', 'noeducation', 'notapplicable' => self::None,
			'informal', 'informallearning', 'nonformal' => self::Informal,
			'selftaught', 'autodidact', 'selfstudy' => self::SelfTaught,

			// Basic education
			'elementary', 'elementaryschool', 'gradeschool', 'primary' => self::ElementarySchool,
			'primaryschool', 'grundschule', 'ensino fundamental' => self::PrimarySchool,
			'middleschool', 'intermediateschool' => self::MiddleSchool,
			'juniorhigh', 'juniorhighschool' => self::JuniorHigh,

			// Secondary education
			'highschool', 'high school', 'secondary', 'ensino medio' => self::HighSchool,
			'secondaryschool', 'secundaria', 'college' => self::SecondarySchool,
			'technicalsecondary', 'technicalschool', 'ensino tecnico' => self::TechnicalSecondary,
			'vocationalsecondary', 'vocationalschool' => self::VocationalSecondary,
			'ged', 'generaleducationdevelopment', 'equivalencytest' => self::GED,
			'equivalency', 'equivalente', 'equivalent' => self::Equivalency,

			// Post-secondary non-degree
			'somecollege', 'some university', 'incompletecollege' => self::SomeCollege,
			'certificate', 'cert', 'certificado', 'brevet' => self::Certificate,
			'diploma', 'diplôme', 'tecnico' => self::Diploma,
			'vocationaltraining', 'vocational', 'formationprofessionnelle' => self::VocationalTraining,
			'apprenticeship', 'apprentice', 'aprendiz' => self::Apprenticeship,
			'tradeschool', 'trades', 'escola tecnica' => self::TradeSchool,

			// Associate level
			'associate', 'associatesdegree', 'associado' => self::AssociateDegree,
			'foundationdegree', 'foundation', 'accesscourse' => self::FoundationDegree,
			'highernationaldiploma', 'hnd', 'diplomasuperior' => self::HigherNationalDiploma,

			// Bachelor level
			'bachelor', 'bachelors', 'bacharelado', 'licenciatura' => self::BachelorDegree,
			'undergraduate', 'undergrad', 'graduação' => self::UndergraduateDegree,
			'firstdegree', 'first cycle', 'cycle 1' => self::FirstDegree,
			'licentiate', 'licenciado', 'licencié' => self::Licentiate,

			// Post-bachelor
			'postbachelor', 'postbachelors', 'posgraduação' => self::PostBachelorCertificate,
			'graduatediploma', 'graduatecertificate', 'especialização' => self::GraduateDiploma,
			'bachelorhonours', 'honoursdegree', 'bacharelado com honras' => self::BachelorHonours,

			// Master level
			'master', 'masters', 'mestrado', 'maestria' => self::MasterDegree,
			'graduate', 'graduatedegree', 'posgraduado' => self::GraduateDegree,
			'mba', 'masterofbusinessadministration' => self::MBA,
			'mfa', 'masteroffinearts' => self::MFA,
			'llm', 'masteroflaws', 'master em direito' => self::LLM,

			// Post-master
			'postmaster', 'postmasters', 'posmestrado' => self::PostMasterCertificate,
			'specialist', 'specialistdegree', 'especialista' => self::SpecialistDegree,

			// Doctoral level
			'doctoral', 'doctorate', 'doutorado', 'doctorat' => self::DoctoralDegree,
			'phd', 'phddegree', 'ph d', 'ph.d.' => self::PhD,
			'professionaldoctorate', 'doctorprofissional' => self::ProfessionalDoctorate,
			'edd', 'doctorofeducation', 'doutorado em educação' => self::EdD,
			'dba', 'doctorofbusinessadministration' => self::DBA,
			'md', 'doctorofmedicine', 'medicina' => self::MD,
			'jd', 'jurisdoctor', 'direito' => self::JD,

			// Post-doctoral
			'postdoctoral', 'postdoc', 'posdoutorado' => self::Postdoctoral,
			'researchfellow', 'fellow', 'pesquisador' => self::ResearchFellow,

			// Professional certifications
			'professionalcertification', 'profissional certificado' => self::ProfessionalCertification,
			'industrycertification', 'certificação industrial' => self::IndustryCertification,
			'technicalcertification', 'certificação técnica' => self::TechnicalCertification,

			// Other
			'other', 'outro', 'otro', 'autre' => self::Other,
			'inprogress', 'em andamento', 'en cours', 'current' => self::InProgress,

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
	 * Get label for this education level in specified language
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
			// No formal education - gray
			self::None, self::Informal, self::SelfTaught => '#6b7280',

			// Basic education - blue
			self::ElementarySchool, self::PrimarySchool,
			self::MiddleSchool, self::JuniorHigh => '#3b82f6',

			// Secondary education - green
			self::HighSchool, self::SecondarySchool,
			self::TechnicalSecondary, self::VocationalSecondary,
			self::GED, self::Equivalency => '#10b981',

			// Post-secondary non-degree - teal
			self::SomeCollege, self::Certificate, self::Diploma,
			self::VocationalTraining, self::Apprenticeship,
			self::TradeSchool => '#14b8a6',

			// Associate level - indigo
			self::AssociateDegree, self::FoundationDegree,
			self::HigherNationalDiploma => '#6366f1',

			// Bachelor level - purple
			self::BachelorDegree, self::UndergraduateDegree,
			self::FirstDegree, self::Licentiate => '#8b5cf6',

			// Post-bachelor - deep purple
			self::PostBachelorCertificate, self::GraduateDiploma,
			self::BachelorHonours => '#7c3aed',

			// Master level - pink
			self::MasterDegree, self::GraduateDegree,
			self::MBA, self::MFA, self::LLM => '#ec4899',

			// Post-master - rose
			self::PostMasterCertificate, self::SpecialistDegree => '#f43f5e',

			// Doctoral level - red
			self::DoctoralDegree, self::PhD,
			self::ProfessionalDoctorate, self::EdD,
			self::DBA, self::MD, self::JD => '#ef4444',

			// Post-doctoral - orange
			self::Postdoctoral, self::ResearchFellow => '#f97316',

			// Professional certifications - amber
			self::ProfessionalCertification, self::IndustryCertification,
			self::TechnicalCertification => '#f59e0b',

			// Other - gray variants
			self::Other => '#9ca3af',
			self::InProgress => '#60a5fa',

			default => '#6b7280',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			// No formal education
			self::None, self::Informal => 'user',
			self::SelfTaught => 'book-open',

			// Basic education
			self::ElementarySchool, self::PrimarySchool => 'school',
			self::MiddleSchool, self::JuniorHigh => 'graduation-cap',

			// Secondary education
			self::HighSchool, self::SecondarySchool => 'university',
			self::TechnicalSecondary, self::VocationalSecondary => 'tools',
			self::GED, self::Equivalency => 'file-certificate',

			// Post-secondary non-degree
			self::SomeCollege => 'calendar-alt',
			self::Certificate => 'certificate',
			self::Diploma => 'scroll',
			self::VocationalTraining, self::TradeSchool => 'wrench',
			self::Apprenticeship => 'hammer',

			// Associate level
			self::AssociateDegree, self::FoundationDegree,
			self::HigherNationalDiploma => 'award',

			// Bachelor level
			self::BachelorDegree, self::UndergraduateDegree,
			self::FirstDegree, self::Licentiate => 'user-graduate',

			// Post-bachelor
			self::PostBachelorCertificate, self::GraduateDiploma,
			self::BachelorHonours => 'user-graduate',

			// Master level
			self::MasterDegree, self::GraduateDegree => 'user-tie',
			self::MBA => 'chart-line',
			self::MFA => 'palette',
			self::LLM => 'balance-scale',

			// Post-master
			self::PostMasterCertificate, self::SpecialistDegree => 'user-graduate',

			// Doctoral level
			self::DoctoralDegree, self::PhD => 'user-md',
			self::ProfessionalDoctorate => 'user-md',
			self::EdD => 'chalkboard-teacher',
			self::DBA => 'user-tie',
			self::MD => 'stethoscope',
			self::JD => 'gavel',

			// Post-doctoral
			self::Postdoctoral, self::ResearchFellow => 'flask',

			// Professional certifications
			self::ProfessionalCertification, self::IndustryCertification,
			self::TechnicalCertification => 'id-badge',

			// Other
			self::Other => 'question-circle',
			self::InProgress => 'spinner',

			default => 'graduation-cap',
		};
	}

	/**
	 * Check if this is a higher education level (associate degree or above)
	 */
	public function isHigherEducation(): bool
	{
		return in_array($this, [
			self::AssociateDegree,
			self::FoundationDegree,
			self::HigherNationalDiploma,
			self::BachelorDegree,
			self::UndergraduateDegree,
			self::FirstDegree,
			self::Licentiate,
			self::PostBachelorCertificate,
			self::GraduateDiploma,
			self::BachelorHonours,
			self::MasterDegree,
			self::GraduateDegree,
			self::MBA,
			self::MFA,
			self::LLM,
			self::PostMasterCertificate,
			self::SpecialistDegree,
			self::DoctoralDegree,
			self::PhD,
			self::ProfessionalDoctorate,
			self::EdD,
			self::DBA,
			self::MD,
			self::JD,
			self::Postdoctoral,
			self::ResearchFellow,
		]);
	}

	/**
	 * Check if this is a technical/vocational education
	 */
	public function isTechnicalEducation(): bool
	{
		return in_array($this, [
			self::TechnicalSecondary,
			self::VocationalSecondary,
			self::VocationalTraining,
			self::TradeSchool,
			self::TechnicalCertification,
			self::IndustryCertification,
		]);
	}

	/**
	 * Check if this is a professional degree
	 */
	public function isProfessionalDegree(): bool
	{
		return in_array($this, [
			self::MBA,
			self::MFA,
			self::LLM,
			self::MD,
			self::JD,
			self::ProfessionalDoctorate,
			self::EdD,
			self::DBA,
		]);
	}

	/**
	 * Get education level category
	 */
	public function getCategory(): string
	{
		return match ($this) {
			// No formal education
			self::None, self::Informal, self::SelfTaught => 'none',

			// Basic education
			self::ElementarySchool, self::PrimarySchool,
			self::MiddleSchool, self::JuniorHigh => 'basic',

			// Secondary education
			self::HighSchool, self::SecondarySchool,
			self::TechnicalSecondary, self::VocationalSecondary,
			self::GED, self::Equivalency => 'secondary',

			// Post-secondary non-degree
			self::SomeCollege, self::Certificate, self::Diploma,
			self::VocationalTraining, self::Apprenticeship,
			self::TradeSchool => 'post_secondary',

			// Associate level
			self::AssociateDegree, self::FoundationDegree,
			self::HigherNationalDiploma => 'associate',

			// Bachelor level
			self::BachelorDegree, self::UndergraduateDegree,
			self::FirstDegree, self::Licentiate => 'bachelor',

			// Post-bachelor
			self::PostBachelorCertificate, self::GraduateDiploma,
			self::BachelorHonours => 'post_bachelor',

			// Master level
			self::MasterDegree, self::GraduateDegree,
			self::MBA, self::MFA, self::LLM => 'master',

			// Post-master
			self::PostMasterCertificate, self::SpecialistDegree => 'post_master',

			// Doctoral level
			self::DoctoralDegree, self::PhD,
			self::ProfessionalDoctorate, self::EdD,
			self::DBA, self::MD, self::JD => 'doctoral',

			// Post-doctoral
			self::Postdoctoral, self::ResearchFellow => 'post_doctoral',

			// Professional certifications
			self::ProfessionalCertification, self::IndustryCertification,
			self::TechnicalCertification => 'certification',

			// Other
			self::Other, self::InProgress => 'other',
		};
	}

	/**
	 * Get typical years of education (approximate)
	 */
	public function getTypicalYears(): int
	{
		return match ($this) {
			// No formal education
			self::None, self::Informal, self::SelfTaught => 0,

			// Basic education
			self::ElementarySchool, self::PrimarySchool => 5,
			self::MiddleSchool, self::JuniorHigh => 8,

			// Secondary education
			self::HighSchool, self::SecondarySchool,
			self::TechnicalSecondary, self::VocationalSecondary,
			self::GED, self::Equivalency => 12,

			// Post-secondary non-degree
			self::SomeCollege => 13,
			self::Certificate, self::Diploma => 13,
			self::VocationalTraining, self::TradeSchool => 13,
			self::Apprenticeship => 13,

			// Associate level
			self::AssociateDegree, self::FoundationDegree,
			self::HigherNationalDiploma => 14,

			// Bachelor level
			self::BachelorDegree, self::UndergraduateDegree,
			self::FirstDegree, self::Licentiate => 16,

			// Post-bachelor
			self::PostBachelorCertificate, self::GraduateDiploma,
			self::BachelorHonours => 17,

			// Master level
			self::MasterDegree, self::GraduateDegree,
			self::MBA, self::MFA, self::LLM => 18,

			// Post-master
			self::PostMasterCertificate, self::SpecialistDegree => 19,

			// Doctoral level
			self::DoctoralDegree, self::PhD,
			self::ProfessionalDoctorate, self::EdD,
			self::DBA => 22,
			self::MD => 24,
			self::JD => 19,

			// Post-doctoral
			self::Postdoctoral, self::ResearchFellow => 24,

			// Professional certifications
			self::ProfessionalCertification, self::IndustryCertification,
			self::TechnicalCertification => 15,

			// Other
			default => 0,
		};
	}

	/**
	 * Get ISCED level (International Standard Classification of Education)
	 */
	public function getISCEDLevel(): string
	{
		return match ($this) {
			// ISCED 0 - Early childhood education
			// ISCED 1 - Primary education
			self::ElementarySchool, self::PrimarySchool => 'ISCED 1',

			// ISCED 2 - Lower secondary education
			self::MiddleSchool, self::JuniorHigh => 'ISCED 2',

			// ISCED 3 - Upper secondary education
			self::HighSchool, self::SecondarySchool,
			self::TechnicalSecondary, self::VocationalSecondary,
			self::GED, self::Equivalency => 'ISCED 3',

			// ISCED 4 - Post-secondary non-tertiary education
			self::SomeCollege, self::Certificate, self::Diploma,
			self::VocationalTraining, self::TradeSchool,
			self::Apprenticeship => 'ISCED 4',

			// ISCED 5 - Short-cycle tertiary education
			self::AssociateDegree, self::FoundationDegree,
			self::HigherNationalDiploma => 'ISCED 5',

			// ISCED 6 - Bachelor's or equivalent level
			self::BachelorDegree, self::UndergraduateDegree,
			self::FirstDegree, self::Licentiate => 'ISCED 6',

			// ISCED 7 - Master's or equivalent level
			self::MasterDegree, self::GraduateDegree,
			self::MBA, self::MFA, self::LLM,
			self::PostMasterCertificate, self::SpecialistDegree => 'ISCED 7',

			// ISCED 8 - Doctoral or equivalent level
			self::DoctoralDegree, self::PhD,
			self::ProfessionalDoctorate, self::EdD,
			self::DBA, self::MD, self::JD,
			self::Postdoctoral, self::ResearchFellow => 'ISCED 8',

			// Not classified
			default => 'Not classified',
		};
	}

	/**
	 * Get common job requirements that typically require this level
	 */
	public function getTypicalJobRequirements(): array
	{
		return match ($this) {
			// Secondary education
			self::HighSchool, self::SecondarySchool => [
				'entry_level_positions',
				'administrative_assistants',
				'retail_workers',
				'customer_service',
			],

			// Associate level
			self::AssociateDegree, self::FoundationDegree => [
				'technicians',
				'paralegals',
				'dental_hygienists',
				'radiologic_technologists',
			],

			// Bachelor level
			self::BachelorDegree, self::UndergraduateDegree => [
				'engineers',
				'teachers',
				'accountants',
				'managers',
				'marketing_professionals',
			],

			// Master level
			self::MasterDegree, self::GraduateDegree => [
				'senior_managers',
				'consultants',
				'clinical_psychologists',
				'librarians',
				'social_workers',
			],

			// Doctoral level
			self::DoctoralDegree, self::PhD => [
				'university_professors',
				'research_scientists',
				'senior_executives',
				'policy_analysts',
			],

			// Professional degrees
			self::MBA => ['executive_management', 'business_consulting', 'entrepreneurship'],
			self::MD => ['physicians', 'surgeons', 'medical_directors'],
			self::JD => ['lawyers', 'judges', 'corporate_counsel'],

			// Technical/vocational
			self::TechnicalSecondary, self::VocationalTraining => [
				'electricians',
				'plumbers',
				'automotive_technicians',
				'chefs',
			],

			default => ['general_employment'],
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			// No formal education
			self::None->value => 'No Formal Education',
			self::Informal->value => 'Informal Education',
			self::SelfTaught->value => 'Self-Taught',

			// Basic education
			self::ElementarySchool->value => 'Elementary School',
			self::PrimarySchool->value => 'Primary School',
			self::MiddleSchool->value => 'Middle School',
			self::JuniorHigh->value => 'Junior High School',

			// Secondary education
			self::HighSchool->value => 'High School',
			self::SecondarySchool->value => 'Secondary School',
			self::TechnicalSecondary->value => 'Technical Secondary',
			self::VocationalSecondary->value => 'Vocational Secondary',
			self::GED->value => 'GED/Equivalency',
			self::Equivalency->value => 'Equivalency Diploma',

			// Post-secondary non-degree
			self::SomeCollege->value => 'Some College',
			self::Certificate->value => 'Certificate',
			self::Diploma->value => 'Diploma',
			self::VocationalTraining->value => 'Vocational Training',
			self::Apprenticeship->value => 'Apprenticeship',
			self::TradeSchool->value => 'Trade School',

			// Associate level
			self::AssociateDegree->value => 'Associate Degree',
			self::FoundationDegree->value => 'Foundation Degree',
			self::HigherNationalDiploma->value => 'Higher National Diploma',

			// Bachelor level
			self::BachelorDegree->value => 'Bachelor\'s Degree',
			self::UndergraduateDegree->value => 'Undergraduate Degree',
			self::FirstDegree->value => 'First Degree',
			self::Licentiate->value => 'Licentiate Degree',

			// Post-bachelor
			self::PostBachelorCertificate->value => 'Post-Bachelor Certificate',
			self::GraduateDiploma->value => 'Graduate Diploma',
			self::BachelorHonours->value => 'Bachelor\'s with Honours',

			// Master level
			self::MasterDegree->value => 'Master\'s Degree',
			self::GraduateDegree->value => 'Graduate Degree',
			self::MBA->value => 'MBA (Master of Business Administration)',
			self::MFA->value => 'MFA (Master of Fine Arts)',
			self::LLM->value => 'LLM (Master of Laws)',

			// Post-master
			self::PostMasterCertificate->value => 'Post-Master Certificate',
			self::SpecialistDegree->value => 'Specialist Degree',

			// Doctoral level
			self::DoctoralDegree->value => 'Doctoral Degree',
			self::PhD->value => 'PhD (Doctor of Philosophy)',
			self::ProfessionalDoctorate->value => 'Professional Doctorate',
			self::EdD->value => 'EdD (Doctor of Education)',
			self::DBA->value => 'DBA (Doctor of Business Administration)',
			self::MD->value => 'MD (Doctor of Medicine)',
			self::JD->value => 'JD (Juris Doctor)',

			// Post-doctoral
			self::Postdoctoral->value => 'Postdoctoral',
			self::ResearchFellow->value => 'Research Fellow',

			// Professional certifications
			self::ProfessionalCertification->value => 'Professional Certification',
			self::IndustryCertification->value => 'Industry Certification',
			self::TechnicalCertification->value => 'Technical Certification',

			// Other
			self::Other->value => 'Other',
			self::InProgress->value => 'In Progress',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			// No formal education
			self::None->value => 'Sem Educação Formal',
			self::Informal->value => 'Educação Informal',
			self::SelfTaught->value => 'Autodidata',

			// Basic education
			self::ElementarySchool->value => 'Ensino Fundamental',
			self::PrimarySchool->value => 'Ensino Primário',
			self::MiddleSchool->value => 'Ensino Fundamental II',
			self::JuniorHigh->value => 'Ensino Fundamental (5ª a 8ª série)',

			// Secondary education
			self::HighSchool->value => 'Ensino Médio',
			self::SecondarySchool->value => 'Ensino Secundário',
			self::TechnicalSecondary->value => 'Ensino Técnico',
			self::VocationalSecondary->value => 'Ensino Profissionalizante',
			self::GED->value => 'Supletivo/Equivalência',
			self::Equivalency->value => 'Equivalência de Ensino Médio',

			// Post-secondary non-degree
			self::SomeCollege->value => 'Ensino Superior Incompleto',
			self::Certificate->value => 'Certificado',
			self::Diploma->value => 'Diploma',
			self::VocationalTraining->value => 'Formação Profissional',
			self::Apprenticeship->value => 'Aprendizagem',
			self::TradeSchool->value => 'Escola Técnica',

			// Associate level
			self::AssociateDegree->value => 'Curso Tecnólogo',
			self::FoundationDegree->value => 'Curso Sequencial',
			self::HigherNationalDiploma->value => 'Diploma Nacional Superior',

			// Bachelor level
			self::BachelorDegree->value => 'Bacharelado',
			self::UndergraduateDegree->value => 'Graduação',
			self::FirstDegree->value => 'Primeiro Grau',
			self::Licentiate->value => 'Licenciatura',

			// Post-bachelor
			self::PostBachelorCertificate->value => 'Pós-Graduação Lato Sensu',
			self::GraduateDiploma->value => 'Diploma de Pós-Graduação',
			self::BachelorHonours->value => 'Bacharelado com Honras',

			// Master level
			self::MasterDegree->value => 'Mestrado',
			self::GraduateDegree->value => 'Pós-Graduação Stricto Sensu',
			self::MBA->value => 'MBA (Master of Business Administration)',
			self::MFA->value => 'MFA (Master of Fine Arts)',
			self::LLM->value => 'LLM (Master of Laws)',

			// Post-master
			self::PostMasterCertificate->value => 'Pós-Mestrado',
			self::SpecialistDegree->value => 'Especialização',

			// Doctoral level
			self::DoctoralDegree->value => 'Doutorado',
			self::PhD->value => 'PhD (Doutor em Filosofia)',
			self::ProfessionalDoctorate->value => 'Doutorado Profissional',
			self::EdD->value => 'EdD (Doutor em Educação)',
			self::DBA->value => 'DBA (Doutor em Administração)',
			self::MD->value => 'MD (Doutor em Medicina)',
			self::JD->value => 'JD (Doutor em Direito)',

			// Post-doctoral
			self::Postdoctoral->value => 'Pós-Doutorado',
			self::ResearchFellow->value => 'Pesquisador',

			// Professional certifications
			self::ProfessionalCertification->value => 'Certificação Profissional',
			self::IndustryCertification->value => 'Certificação do Setor',
			self::TechnicalCertification->value => 'Certificação Técnica',

			// Other
			self::Other->value => 'Outro',
			self::InProgress->value => 'Em Andamento',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			// No formal education
			self::None->value => 'Sin Educación Formal',
			self::Informal->value => 'Educación Informal',
			self::SelfTaught->value => 'Autodidacta',

			// Basic education
			self::ElementarySchool->value => 'Escuela Primaria',
			self::PrimarySchool->value => 'Educación Primaria',
			self::MiddleSchool->value => 'Educación Secundaria Básica',
			self::JuniorHigh->value => 'Educación Media Básica',

			// Secondary education
			self::HighSchool->value => 'Bachillerato',
			self::SecondarySchool->value => 'Educación Secundaria',
			self::TechnicalSecondary->value => 'Técnico Secundario',
			self::VocationalSecondary->value => 'Formación Profesional Secundaria',
			self::GED->value => 'GED/Equivalencia',
			self::Equivalency->value => 'Diploma de Equivalencia',

			// Post-secondary non-degree
			self::SomeCollege->value => 'Alguna Universidad',
			self::Certificate->value => 'Certificado',
			self::Diploma->value => 'Diploma',
			self::VocationalTraining->value => 'Formación Profesional',
			self::Apprenticeship->value => 'Aprendizaje',
			self::TradeSchool->value => 'Escuela de Oficios',

			// Associate level
			self::AssociateDegree->value => 'Grado Asociado',
			self::FoundationDegree->value => 'Grado de Fundación',
			self::HigherNationalDiploma->value => 'Diploma Nacional Superior',

			// Bachelor level
			self::BachelorDegree->value => 'Grado Universitario',
			self::UndergraduateDegree->value => 'Licenciatura',
			self::FirstDegree->value => 'Primer Grado',
			self::Licentiate->value => 'Licenciatura',

			// Post-bachelor
			self::PostBachelorCertificate->value => 'Certificado de Posgrado',
			self::GraduateDiploma->value => 'Diploma de Graduado',
			self::BachelorHonours->value => 'Licenciatura con Honores',

			// Master level
			self::MasterDegree->value => 'Maestría',
			self::GraduateDegree->value => 'Posgrado',
			self::MBA->value => 'MBA (Master of Business Administration)',
			self::MFA->value => 'MFA (Master of Fine Arts)',
			self::LLM->value => 'LLM (Master of Laws)',

			// Post-master
			self::PostMasterCertificate->value => 'Certificado Post-Maestría',
			self::SpecialistDegree->value => 'Grado de Especialista',

			// Doctoral level
			self::DoctoralDegree->value => 'Doctorado',
			self::PhD->value => 'PhD (Doctor en Filosofía)',
			self::ProfessionalDoctorate->value => 'Doctorado Profesional',
			self::EdD->value => 'EdD (Doctor en Educación)',
			self::DBA->value => 'DBA (Doctor en Administración)',
			self::MD->value => 'MD (Doctor en Medicina)',
			self::JD->value => 'JD (Juris Doctor)',

			// Post-doctoral
			self::Postdoctoral->value => 'Posdoctorado',
			self::ResearchFellow->value => 'Investigador',

			// Professional certifications
			self::ProfessionalCertification->value => 'Certificación Profesional',
			self::IndustryCertification->value => 'Certificación de la Industria',
			self::TechnicalCertification->value => 'Certificación Técnica',

			// Other
			self::Other->value => 'Otro',
			self::InProgress->value => 'En Progreso',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			// No formal education
			self::None->value => 'Keine formale Ausbildung',
			self::Informal->value => 'Informelle Bildung',
			self::SelfTaught->value => 'Autodidakt',

			// Basic education
			self::ElementarySchool->value => 'Grundschule',
			self::PrimarySchool->value => 'Primarschule',
			self::MiddleSchool->value => 'Mittelschule',
			self::JuniorHigh->value => 'Realschule',

			// Secondary education
			self::HighSchool->value => 'Gymnasium',
			self::SecondarySchool->value => 'Sekundarschule',
			self::TechnicalSecondary->value => 'Fachoberschule',
			self::VocationalSecondary->value => 'Berufsschule',
			self::GED->value => 'Abitur/Äquivalent',
			self::Equivalency->value => 'Äquivalenzdiplom',

			// Post-secondary non-degree
			self::SomeCollege->value => 'Einige Hochschulbildung',
			self::Certificate->value => 'Zertifikat',
			self::Diploma->value => 'Diplom',
			self::VocationalTraining->value => 'Berufsausbildung',
			self::Apprenticeship->value => 'Lehre',
			self::TradeSchool->value => 'Berufsfachschule',

			// Associate level
			self::AssociateDegree->value => 'Associate Degree',
			self::FoundationDegree->value => 'Foundation Degree',
			self::HigherNationalDiploma->value => 'Higher National Diploma',

			// Bachelor level
			self::BachelorDegree->value => 'Bachelor-Abschluss',
			self::UndergraduateDegree->value => 'Erststudium',
			self::FirstDegree->value => 'Erster Hochschulabschluss',
			self::Licentiate->value => 'Lizentiat',

			// Post-bachelor
			self::PostBachelorCertificate->value => 'Post-Bachelor-Zertifikat',
			self::GraduateDiploma->value => 'Graduate-Diplom',
			self::BachelorHonours->value => 'Bachelor mit Auszeichnung',

			// Master level
			self::MasterDegree->value => 'Master-Abschluss',
			self::GraduateDegree->value => 'Aufbaustudium',
			self::MBA->value => 'MBA (Master of Business Administration)',
			self::MFA->value => 'MFA (Master of Fine Arts)',
			self::LLM->value => 'LLM (Master of Laws)',

			// Post-master
			self::PostMasterCertificate->value => 'Post-Master-Zertifikat',
			self::SpecialistDegree->value => 'Facharzttitel',

			// Doctoral level
			self::DoctoralDegree->value => 'Promotion',
			self::PhD->value => 'PhD (Doktor der Philosophie)',
			self::ProfessionalDoctorate->value => 'Berufsdoktorat',
			self::EdD->value => 'EdD (Doktor der Erziehungswissenschaft)',
			self::DBA->value => 'DBA (Doktor der Betriebswirtschaft)',
			self::MD->value => 'MD (Doktor der Medizin)',
			self::JD->value => 'JD (Juris Doctor)',

			// Post-doctoral
			self::Postdoctoral->value => 'Postdoktorand',
			self::ResearchFellow->value => 'Forschungsstipendiat',

			// Professional certifications
			self::ProfessionalCertification->value => 'Berufszertifizierung',
			self::IndustryCertification->value => 'Industriezertifizierung',
			self::TechnicalCertification->value => 'Technische Zertifizierung',

			// Other
			self::Other->value => 'Andere',
			self::InProgress->value => 'In Bearbeitung',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			// No formal education
			self::None->value => 'Pas d\'Éducation Formelle',
			self::Informal->value => 'Éducation Informelle',
			self::SelfTaught->value => 'Autodidacte',

			// Basic education
			self::ElementarySchool->value => 'École Primaire',
			self::PrimarySchool->value => 'Enseignement Primaire',
			self::MiddleSchool->value => 'Collège',
			self::JuniorHigh->value => 'École Secondaire Inférieur',

			// Secondary education
			self::HighSchool->value => 'Lycée',
			self::SecondarySchool->value => 'Enseignement Secondaire',
			self::TechnicalSecondary->value => 'Enseignement Technique',
			self::VocationalSecondary->value => 'Formation Professionnelle',
			self::GED->value => 'Bac/Équivalent',
			self::Equivalency->value => 'Diplôme d\'Équivalence',

			// Post-secondary non-degree
			self::SomeCollege->value => 'Quelques Études Supérieures',
			self::Certificate->value => 'Certificat',
			self::Diploma->value => 'Diplôme',
			self::VocationalTraining->value => 'Formation Professionnelle',
			self::Apprenticeship->value => 'Apprentissage',
			self::TradeSchool->value => 'École de Métiers',

			// Associate level
			self::AssociateDegree->value => 'Diplôme d\'Associé',
			self::FoundationDegree->value => 'Diplôme de Fondation',
			self::HigherNationalDiploma->value => 'Diplôme National Supérieur',

			// Bachelor level
			self::BachelorDegree->value => 'Licence',
			self::UndergraduateDegree->value => 'Premier Cycle',
			self::FirstDegree->value => 'Premier Diplôme',
			self::Licentiate->value => 'Licence',

			// Post-bachelor
			self::PostBachelorCertificate->value => 'Certificat Post-Licence',
			self::GraduateDiploma->value => 'Diplôme de Troisième Cycle',
			self::BachelorHonours->value => 'Licence avec Mention',

			// Master level
			self::MasterDegree->value => 'Master',
			self::GraduateDegree->value => 'Deuxième Cycle',
			self::MBA->value => 'MBA (Master of Business Administration)',
			self::MFA->value => 'MFA (Master of Fine Arts)',
			self::LLM->value => 'LLM (Master of Laws)',

			// Post-master
			self::PostMasterCertificate->value => 'Certificat Post-Master',
			self::SpecialistDegree->value => 'Diplôme de Spécialiste',

			// Doctoral level
			self::DoctoralDegree->value => 'Doctorat',
			self::PhD->value => 'PhD (Docteur en Philosophie)',
			self::ProfessionalDoctorate->value => 'Doctorat Professionnel',
			self::EdD->value => 'EdD (Docteur en Éducation)',
			self::DBA->value => 'DBA (Docteur en Administration)',
			self::MD->value => 'MD (Docteur en Médecine)',
			self::JD->value => 'JD (Juris Doctor)',

			// Post-doctoral
			self::Postdoctoral->value => 'Postdoctorat',
			self::ResearchFellow->value => 'Chercheur',

			// Professional certifications
			self::ProfessionalCertification->value => 'Certification Professionnelle',
			self::IndustryCertification->value => 'Certification de l\'Industrie',
			self::TechnicalCertification->value => 'Certification Technique',

			// Other
			self::Other->value => 'Autre',
			self::InProgress->value => 'En Cours',
		];
	}

	// Continue with other languages (Italian, Dutch, Polish, Russian, Turkish, Arabic, Hebrew, Japanese, Danish, Chinese)
	// following the same pattern for all 55 cases

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::None->value => 'Nessuna Istruzione Formale',
			self::Informal->value => 'Istruzione Informale',
			self::SelfTaught->value => 'Autodidatta',
			self::ElementarySchool->value => 'Scuola Elementare',
			self::PrimarySchool->value => 'Scuola Primaria',
			self::MiddleSchool->value => 'Scuola Media',
			self::JuniorHigh->value => 'Scuola Secondaria di Primo Grado',
			self::HighSchool->value => 'Scuola Superiore',
			self::SecondarySchool->value => 'Istruzione Secondaria',
			self::TechnicalSecondary->value => 'Istituto Tecnico',
			self::VocationalSecondary->value => 'Istituto Professionale',
			self::GED->value => 'Diploma Equivalente',
			self::Equivalency->value => 'Equipollenza',
			self::SomeCollege->value => 'Alcuni Studi Universitari',
			self::Certificate->value => 'Certificato',
			self::Diploma->value => 'Diploma',
			self::VocationalTraining->value => 'Formazione Professionale',
			self::Apprenticeship->value => 'Apprendistato',
			self::TradeSchool->value => 'Scuola di Mestieri',
			self::AssociateDegree->value => 'Diploma di Associato',
			self::FoundationDegree->value => 'Diploma di Fondazione',
			self::HigherNationalDiploma->value => 'Diploma Nazionale Superiore',
			self::BachelorDegree->value => 'Laurea Triennale',
			self::UndergraduateDegree->value => 'Laurea',
			self::FirstDegree->value => 'Primo Grado',
			self::Licentiate->value => 'Licenza',
			self::PostBachelorCertificate->value => 'Certificato Post-Laurea',
			self::GraduateDiploma->value => 'Diploma di Specializzazione',
			self::BachelorHonours->value => 'Laurea con Lode',
			self::MasterDegree->value => 'Laurea Magistrale',
			self::GraduateDegree->value => 'Laurea Specialistica',
			self::MBA->value => 'MBA (Master of Business Administration)',
			self::MFA->value => 'MFA (Master of Fine Arts)',
			self::LLM->value => 'LLM (Master of Laws)',
			self::PostMasterCertificate->value => 'Certificato Post-Master',
			self::SpecialistDegree->value => 'Specializzazione',
			self::DoctoralDegree->value => 'Dottorato di Ricerca',
			self::PhD->value => 'PhD (Dottore in Filosofia)',
			self::ProfessionalDoctorate->value => 'Dottorato Professionale',
			self::EdD->value => 'EdD (Dottore in Educazione)',
			self::DBA->value => 'DBA (Dottore in Amministrazione)',
			self::MD->value => 'MD (Dottore in Medicina)',
			self::JD->value => 'JD (Juris Doctor)',
			self::Postdoctoral->value => 'Postdottorato',
			self::ResearchFellow->value => 'Ricercatore',
			self::ProfessionalCertification->value => 'Certificazione Professionale',
			self::IndustryCertification->value => 'Certificazione di Settore',
			self::TechnicalCertification->value => 'Certificazione Tecnica',
			self::Other->value => 'Altro',
			self::InProgress->value => 'In Corso',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::None->value => 'Geen Formeel Onderwijs',
			self::Informal->value => 'Informeel Onderwijs',
			self::SelfTaught->value => 'Autodidact',
			self::ElementarySchool->value => 'Basisschool',
			self::PrimarySchool->value => 'Lager Onderwijs',
			self::MiddleSchool->value => 'Middelbare School',
			self::JuniorHigh->value => 'Onderbouw Voortgezet Onderwijs',
			self::HighSchool->value => 'Voortgezet Onderwijs',
			self::SecondarySchool->value => 'Middelbaar Onderwijs',
			self::TechnicalSecondary->value => 'Technisch Onderwijs',
			self::VocationalSecondary->value => 'Beroepsonderwijs',
			self::GED->value => 'VWO/HAVO Diploma',
			self::Equivalency->value => 'Equivalente Diploma',
			self::SomeCollege->value => 'Enige Hoger Onderwijs',
			self::Certificate->value => 'Certificaat',
			self::Diploma->value => 'Diploma',
			self::VocationalTraining->value => 'Beroepsopleiding',
			self::Apprenticeship->value => 'Leerlingwezen',
			self::TradeSchool->value => 'Ambachtsschool',
			self::AssociateDegree->value => 'Associate Degree',
			self::FoundationDegree->value => 'Foundation Degree',
			self::HigherNationalDiploma->value => 'Higher National Diploma',
			self::BachelorDegree->value => 'Bachelor Diploma',
			self::UndergraduateDegree->value => 'WO Bachelor',
			self::FirstDegree->value => 'Eerste Graad',
			self::Licentiate->value => 'Licentiaat',
			self::PostBachelorCertificate->value => 'Post-Bachelor Certificaat',
			self::GraduateDiploma->value => 'Graduate Diploma',
			self::BachelorHonours->value => 'Bachelor met Honours',
			self::MasterDegree->value => 'Master Diploma',
			self::GraduateDegree->value => 'Masteropleiding',
			self::MBA->value => 'MBA (Master of Business Administration)',
			self::MFA->value => 'MFA (Master of Fine Arts)',
			self::LLM->value => 'LLM (Master of Laws)',
			self::PostMasterCertificate->value => 'Post-Master Certificaat',
			self::SpecialistDegree->value => 'Specialistendiploma',
			self::DoctoralDegree->value => 'Doctoraat',
			self::PhD->value => 'PhD (Doctor in de Filosofie)',
			self::ProfessionalDoctorate->value => 'Professioneel Doctoraat',
			self::EdD->value => 'EdD (Doctor in de Onderwijskunde)',
			self::DBA->value => 'DBA (Doctor in de Bedrijfskunde)',
			self::MD->value => 'MD (Doctor in de Geneeskunde)',
			self::JD->value => 'JD (Juris Doctor)',
			self::Postdoctoral->value => 'Postdoctoraal',
			self::ResearchFellow->value => 'Onderzoeker',
			self::ProfessionalCertification->value => 'Professionele Certificering',
			self::IndustryCertification->value => 'Industriële Certificering',
			self::TechnicalCertification->value => 'Technische Certificering',
			self::Other->value => 'Anders',
			self::InProgress->value => 'In Uitvoering',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::None->value => 'Brak Wykształcenia Formalnego',
			self::Informal->value => 'Edukacja Nieformalna',
			self::SelfTaught->value => 'Samouk',
			self::ElementarySchool->value => 'Szkoła Podstawowa',
			self::PrimarySchool->value => 'Edukacja Początkowa',
			self::MiddleSchool->value => 'Gimnazjum',
			self::JuniorHigh->value => 'Szkoła Średnia Niższego Stopnia',
			self::HighSchool->value => 'Liceum',
			self::SecondarySchool->value => 'Edukacja Średnia',
			self::TechnicalSecondary->value => 'Technikum',
			self::VocationalSecondary->value => 'Szkoła Zawodowa',
			self::GED->value => 'Matura/Świadectwo Dojrzałości',
			self::Equivalency->value => 'Świadectwo Równoważności',
			self::SomeCollege->value => 'Niektóre Studia Wyższe',
			self::Certificate->value => 'Certyfikat',
			self::Diploma->value => 'Dyplom',
			self::VocationalTraining->value => 'Kształcenie Zawodowe',
			self::Apprenticeship->value => 'Praktyka Zawodowa',
			self::TradeSchool->value => 'Szkoła Rzemiosła',
			self::AssociateDegree->value => 'Stopień Asystenta',
			self::FoundationDegree->value => 'Stopień Podstawowy',
			self::HigherNationalDiploma->value => 'Wyższy Dyplom Krajowy',
			self::BachelorDegree->value => 'Licencjat',
			self::UndergraduateDegree->value => 'Studia Licencjackie',
			self::FirstDegree->value => 'Pierwszy Stopień',
			self::Licentiate->value => 'Licencjat',
			self::PostBachelorCertificate->value => 'Certyfikat Podyplomowy',
			self::GraduateDiploma->value => 'Dyplom Uzupełniający',
			self::BachelorHonours->value => 'Licencjat z Wyróżnieniem',
			self::MasterDegree->value => 'Magister',
			self::GraduateDegree->value => 'Studia Magisterskie',
			self::MBA->value => 'MBA (Master of Business Administration)',
			self::MFA->value => 'MFA (Master of Fine Arts)',
			self::LLM->value => 'LLM (Master of Laws)',
			self::PostMasterCertificate->value => 'Certyfikat Po Magisterium',
			self::SpecialistDegree->value => 'Stopień Specjalisty',
			self::DoctoralDegree->value => 'Doktorat',
			self::PhD->value => 'PhD (Doktor Filozofii)',
			self::ProfessionalDoctorate->value => 'Doktorat Zawodowy',
			self::EdD->value => 'EdD (Doktor Edukacji)',
			self::DBA->value => 'DBA (Doktor Administracji Biznesowej)',
			self::MD->value => 'MD (Doktor Medycyny)',
			self::JD->value => 'JD (Juris Doctor)',
			self::Postdoctoral->value => 'Badania Podoktorskie',
			self::ResearchFellow->value => 'Badacz',
			self::ProfessionalCertification->value => 'Certyfikacja Zawodowa',
			self::IndustryCertification->value => 'Certyfikacja Przemysłowa',
			self::TechnicalCertification->value => 'Certyfikacja Techniczna',
			self::Other->value => 'Inne',
			self::InProgress->value => 'W Trakcie',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::None->value => 'Нет Формального Образования',
			self::Informal->value => 'Неформальное Образование',
			self::SelfTaught->value => 'Самоучка',
			self::ElementarySchool->value => 'Начальная Школа',
			self::PrimarySchool->value => 'Начальное Образование',
			self::MiddleSchool->value => 'Средняя Школа',
			self::JuniorHigh->value => 'Неполная Средняя Школа',
			self::HighSchool->value => 'Старшая Школа',
			self::SecondarySchool->value => 'Среднее Образование',
			self::TechnicalSecondary->value => 'Техническое Училище',
			self::VocationalSecondary->value => 'Профессиональное Училище',
			self::GED->value => 'Аттестат/Эквивалент',
			self::Equivalency->value => 'Эквивалентный Диплом',
			self::SomeCollege->value => 'Неполное Высшее Образование',
			self::Certificate->value => 'Сертификат',
			self::Diploma->value => 'Диплом',
			self::VocationalTraining->value => 'Профессиональное Обучение',
			self::Apprenticeship->value => 'Стажировка',
			self::TradeSchool->value => 'Ремесленное Училище',
			self::AssociateDegree->value => 'Степень Младшего Специалиста',
			self::FoundationDegree->value => 'Подготовительная Степень',
			self::HigherNationalDiploma->value => 'Высший Национальный Диплом',
			self::BachelorDegree->value => 'Бакалавр',
			self::UndergraduateDegree->value => 'Высшее Образование',
			self::FirstDegree->value => 'Первая Степень',
			self::Licentiate->value => 'Лиценциат',
			self::PostBachelorCertificate->value => 'Сертификат После Бакалавриата',
			self::GraduateDiploma->value => 'Диплом О Высшем Образовании',
			self::BachelorHonours->value => 'Бакалавр с Отличием',
			self::MasterDegree->value => 'Магистр',
			self::GraduateDegree->value => 'Аспирантура',
			self::MBA->value => 'MBA (Магистр Делового Администрирования)',
			self::MFA->value => 'MFA (Магистр Изящных Искусств)',
			self::LLM->value => 'LLM (Магистр Права)',
			self::PostMasterCertificate->value => 'Сертификат После Магистратуры',
			self::SpecialistDegree->value => 'Степень Специалиста',
			self::DoctoralDegree->value => 'Докторантура',
			self::PhD->value => 'PhD (Доктор Философии)',
			self::ProfessionalDoctorate->value => 'Профессиональная Докторская',
			self::EdD->value => 'EdD (Доктор Образования)',
			self::DBA->value => 'DBA (Доктор Бизнес-Администрирования)',
			self::MD->value => 'MD (Доктор Медицины)',
			self::JD->value => 'JD (Доктор Юриспруденции)',
			self::Postdoctoral->value => 'Постдокторантура',
			self::ResearchFellow->value => 'Научный Сотрудник',
			self::ProfessionalCertification->value => 'Профессиональная Сертификация',
			self::IndustryCertification->value => 'Промышленная Сертификация',
			self::TechnicalCertification->value => 'Техническая Сертификация',
			self::Other->value => 'Другое',
			self::InProgress->value => 'В Процессе',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::None->value => 'Resmi Eğitim Yok',
			self::Informal->value => 'Gayri Resmi Eğitim',
			self::SelfTaught->value => 'Kendi Kendine Öğrenme',
			self::ElementarySchool->value => 'İlkokul',
			self::PrimarySchool->value => 'İlköğretim',
			self::MiddleSchool->value => 'Ortaokul',
			self::JuniorHigh->value => 'Ortaöğretim',
			self::HighSchool->value => 'Lise',
			self::SecondarySchool->value => 'Ortaöğretim',
			self::TechnicalSecondary->value => 'Teknik Lise',
			self::VocationalSecondary->value => 'Meslek Lisesi',
			self::GED->value => 'Lise Diploması Eşdeğeri',
			self::Equivalency->value => 'Denklik Diploması',
			self::SomeCollege->value => 'Bazı Üniversite Eğitimi',
			self::Certificate->value => 'Sertifika',
			self::Diploma->value => 'Diploma',
			self::VocationalTraining->value => 'Mesleki Eğitim',
			self::Apprenticeship->value => 'Çıraklık',
			self::TradeSchool->value => 'Meslek Okulu',
			self::AssociateDegree->value => 'Ön Lisans',
			self::FoundationDegree->value => 'Temel Derece',
			self::HigherNationalDiploma->value => 'Yüksek Ulusal Diploma',
			self::BachelorDegree->value => 'Lisans Derecesi',
			self::UndergraduateDegree->value => 'Lisans Eğitimi',
			self::FirstDegree->value => 'Birinci Derece',
			self::Licentiate->value => 'Lisans Derecesi',
			self::PostBachelorCertificate->value => 'Lisans Sonrası Sertifika',
			self::GraduateDiploma->value => 'Lisansüstü Diploma',
			self::BachelorHonours->value => 'Onur Lisansı',
			self::MasterDegree->value => 'Yüksek Lisans',
			self::GraduateDegree->value => 'Lisansüstü Derece',
			self::MBA->value => 'MBA (İşletme Yüksek Lisansı)',
			self::MFA->value => 'MFA (Güzel Sanatlar Yüksek Lisansı)',
			self::LLM->value => 'LLM (Hukuk Yüksek Lisansı)',
			self::PostMasterCertificate->value => 'Yüksek Lisans Sonrası Sertifika',
			self::SpecialistDegree->value => 'Uzmanlık Derecesi',
			self::DoctoralDegree->value => 'Doktora',
			self::PhD->value => 'PhD (Felsefe Doktoru)',
			self::ProfessionalDoctorate->value => 'Profesyonel Doktora',
			self::EdD->value => 'EdD (Eğitim Doktoru)',
			self::DBA->value => 'DBA (İşletme Doktorası)',
			self::MD->value => 'MD (Tıp Doktoru)',
			self::JD->value => 'JD (Hukuk Doktoru)',
			self::Postdoctoral->value => 'Doktora Sonrası',
			self::ResearchFellow->value => 'Araştırmacı',
			self::ProfessionalCertification->value => 'Profesyonel Sertifika',
			self::IndustryCertification->value => 'Endüstri Sertifikası',
			self::TechnicalCertification->value => 'Teknik Sertifika',
			self::Other->value => 'Diğer',
			self::InProgress->value => 'Devam Ediyor',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::None->value => 'بدون تعليم رسمي',
			self::Informal->value => 'تعليم غير رسمي',
			self::SelfTaught->value => 'تعليم ذاتي',
			self::ElementarySchool->value => 'المدرسة الابتدائية',
			self::PrimarySchool->value => 'التعليم الابتدائي',
			self::MiddleSchool->value => 'المدرسة المتوسطة',
			self::JuniorHigh->value => 'المدرسة الإعدادية',
			self::HighSchool->value => 'المدرسة الثانوية',
			self::SecondarySchool->value => 'التعليم الثانوي',
			self::TechnicalSecondary->value => 'الثانوية الفنية',
			self::VocationalSecondary->value => 'التعليم المهني الثانوي',
			self::GED->value => 'دبلوم المعادلة',
			self::Equivalency->value => 'دبلوم معادلة',
			self::SomeCollege->value => 'بعض التعليم الجامعي',
			self::Certificate->value => 'شهادة',
			self::Diploma->value => 'دبلوم',
			self::VocationalTraining->value => 'تدريب مهني',
			self::Apprenticeship->value => 'التدريب المهني',
			self::TradeSchool->value => 'مدرسة الحرف',
			self::AssociateDegree->value => 'درجة الزمالة',
			self::FoundationDegree->value => 'درجة التأسيس',
			self::HigherNationalDiploma->value => 'الدبلوم الوطني العالي',
			self::BachelorDegree->value => 'درجة البكالوريوس',
			self::UndergraduateDegree->value => 'الدرجة الجامعية',
			self::FirstDegree->value => 'الدرجة الأولى',
			self::Licentiate->value => 'الإجازة',
			self::PostBachelorCertificate->value => 'شهادة ما بعد البكالوريوس',
			self::GraduateDiploma->value => 'دبلوم الدراسات العليا',
			self::BachelorHonours->value => 'بكالوريوس مع مرتبة الشرف',
			self::MasterDegree->value => 'درجة الماجستير',
			self::GraduateDegree->value => 'درجة الدراسات العليا',
			self::MBA->value => 'MBA (ماجستير إدارة الأعمال)',
			self::MFA->value => 'MFA (ماجستير الفنون الجميلة)',
			self::LLM->value => 'LLM (ماجستير القانون)',
			self::PostMasterCertificate->value => 'شهادة ما بعد الماجستير',
			self::SpecialistDegree->value => 'درجة الاختصاص',
			self::DoctoralDegree->value => 'درجة الدكتوراه',
			self::PhD->value => 'PhD (دكتوراه في الفلسفة)',
			self::ProfessionalDoctorate->value => 'دكتوراه مهنية',
			self::EdD->value => 'EdD (دكتوراه في التربية)',
			self::DBA->value => 'DBA (دكتوراه في إدارة الأعمال)',
			self::MD->value => 'MD (دكتوراه في الطب)',
			self::JD->value => 'JD (دكتوراه في القانون)',
			self::Postdoctoral->value => 'ما بعد الدكتوراه',
			self::ResearchFellow->value => 'باحث',
			self::ProfessionalCertification->value => 'شهادة مهنية',
			self::IndustryCertification->value => 'شهادة صناعية',
			self::TechnicalCertification->value => 'شهادة تقنية',
			self::Other->value => 'أخرى',
			self::InProgress->value => 'قيد التنفيذ',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::None->value => 'ללא השכלה פורמלית',
			self::Informal->value => 'חינוך בלתי פורמלי',
			self::SelfTaught->value => 'אוטודידקט',
			self::ElementarySchool->value => 'בית ספר יסודי',
			self::PrimarySchool->value => 'חינוך יסודי',
			self::MiddleSchool->value => 'חטיבת ביניים',
			self::JuniorHigh->value => 'חטיבה צעירה',
			self::HighSchool->value => 'תיכון',
			self::SecondarySchool->value => 'חינוך על יסודי',
			self::TechnicalSecondary->value => 'בית ספר טכני',
			self::VocationalSecondary->value => 'בית ספר מקצועי',
			self::GED->value => 'תעודת בגרות/מקבילה',
			self::Equivalency->value => 'תעודת השכלה מקבילה',
			self::SomeCollege->value => 'חלק מהשכלה גבוהה',
			self::Certificate->value => 'תעודה',
			self::Diploma->value => 'דיפלומה',
			self::VocationalTraining->value => 'הכשרה מקצועית',
			self::Apprenticeship->value => 'התמחות',
			self::TradeSchool->value => 'בית ספר למקצועות',
			self::AssociateDegree->value => 'תואר מקוצר',
			self::FoundationDegree->value => 'תואר יסוד',
			self::HigherNationalDiploma->value => 'דיפלומה לאומית גבוהה',
			self::BachelorDegree->value => 'תואר ראשון',
			self::UndergraduateDegree->value => 'תואר ראשון',
			self::FirstDegree->value => 'תואר ראשון',
			self::Licentiate->value => 'רישיון',
			self::PostBachelorCertificate->value => 'תעודה לתואר ראשון',
			self::GraduateDiploma->value => 'דיפלומה לתואר שני',
			self::BachelorHonours->value => 'תואר ראשון בהצטיינות',
			self::MasterDegree->value => 'תואר שני',
			self::GraduateDegree->value => 'תואר מתקדם',
			self::MBA->value => 'MBA (תואר שני במינהל עסקים)',
			self::MFA->value => 'MFA (תואר שני באמנויות)',
			self::LLM->value => 'LLM (תואר שני במשפטים)',
			self::PostMasterCertificate->value => 'תעודה לתואר שני',
			self::SpecialistDegree->value => 'תואר מומחה',
			self::DoctoralDegree->value => 'תואר שלישי',
			self::PhD->value => 'PhD (דוקטור לפילוסופיה)',
			self::ProfessionalDoctorate->value => 'דוקטורט מקצועי',
			self::EdD->value => 'EdD (דוקטור לחינוך)',
			self::DBA->value => 'DBA (דוקטור למינהל עסקים)',
			self::MD->value => 'MD (דוקטור לרפואה)',
			self::JD->value => 'JD (דוקטור למשפטים)',
			self::Postdoctoral->value => 'פוסט דוקטורט',
			self::ResearchFellow->value => 'חוקר',
			self::ProfessionalCertification->value => 'הסמכה מקצועית',
			self::IndustryCertification->value => 'הסמכה תעשייתית',
			self::TechnicalCertification->value => 'הסמכה טכנית',
			self::Other->value => 'אחר',
			self::InProgress->value => 'בתהליך',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::None->value => '正規教育なし',
			self::Informal->value => '非公式教育',
			self::SelfTaught->value => '独学',
			self::ElementarySchool->value => '小学校',
			self::PrimarySchool->value => '初等教育',
			self::MiddleSchool->value => '中学校',
			self::JuniorHigh->value => '中等教育前期',
			self::HighSchool->value => '高等学校',
			self::SecondarySchool->value => '中等教育',
			self::TechnicalSecondary->value => '工業高校',
			self::VocationalSecondary->value => '職業高校',
			self::GED->value => '高校卒業同等資格',
			self::Equivalency->value => '同等資格',
			self::SomeCollege->value => '大学中退',
			self::Certificate->value => '修了証',
			self::Diploma->value => 'ディプロマ',
			self::VocationalTraining->value => '職業訓練',
			self::Apprenticeship->value => '徒弟制度',
			self::TradeSchool->value => '職業訓練校',
			self::AssociateDegree->value => '短期大学士',
			self::FoundationDegree->value => '基礎学位',
			self::HigherNationalDiploma->value => '高等国家ディプロマ',
			self::BachelorDegree->value => '学士号',
			self::UndergraduateDegree->value => '学士課程',
			self::FirstDegree->value => '第一次学位',
			self::Licentiate->value => '学士号',
			self::PostBachelorCertificate->value => '学士後修了証',
			self::GraduateDiploma->value => '大学院修了証',
			self::BachelorHonours->value => '優等学士号',
			self::MasterDegree->value => '修士号',
			self::GraduateDegree->value => '大学院学位',
			self::MBA->value => 'MBA (経営学修士)',
			self::MFA->value => 'MFA (美術学修士)',
			self::LLM->value => 'LLM (法学修士)',
			self::PostMasterCertificate->value => '修士後修了証',
			self::SpecialistDegree->value => '専門職学位',
			self::DoctoralDegree->value => '博士号',
			self::PhD->value => 'PhD (哲学博士)',
			self::ProfessionalDoctorate->value => '専門職博士',
			self::EdD->value => 'EdD (教育学博士)',
			self::DBA->value => 'DBA (経営学博士)',
			self::MD->value => 'MD (医学博士)',
			self::JD->value => 'JD (法務博士)',
			self::Postdoctoral->value => '博士研究員',
			self::ResearchFellow->value => '研究員',
			self::ProfessionalCertification->value => '専門資格',
			self::IndustryCertification->value => '産業資格',
			self::TechnicalCertification->value => '技術資格',
			self::Other->value => 'その他',
			self::InProgress->value => '進行中',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::None->value => 'Ingen Formel Uddannelse',
			self::Informal->value => 'Uformel Uddannelse',
			self::SelfTaught->value => 'Selvlært',
			self::ElementarySchool->value => 'Folkeskole',
			self::PrimarySchool->value => 'Grundskole',
			self::MiddleSchool->value => 'Mellemskole',
			self::JuniorHigh->value => 'Ungdomsskole',
			self::HighSchool->value => 'Gymnasium',
			self::SecondarySchool->value => 'Videregående Uddannelse',
			self::TechnicalSecondary->value => 'Teknisk Skole',
			self::VocationalSecondary->value => 'Erhvervsskole',
			self::GED->value => 'Studentereksamen/Ækvivalens',
			self::Equivalency->value => 'Ækvivalensdiplom',
			self::SomeCollege->value => 'Nogle Videregående Uddannelser',
			self::Certificate->value => 'Certifikat',
			self::Diploma->value => 'Diplom',
			self::VocationalTraining->value => 'Erhvervsuddannelse',
			self::Apprenticeship->value => 'Læreplads',
			self::TradeSchool->value => 'Håndværkerskole',
			self::AssociateDegree->value => 'Associate Degree',
			self::FoundationDegree->value => 'Foundation Degree',
			self::HigherNationalDiploma->value => 'Higher National Diploma',
			self::BachelorDegree->value => 'Bachelor Grad',
			self::UndergraduateDegree->value => 'Bacheloruddannelse',
			self::FirstDegree->value => 'Første Grad',
			self::Licentiate->value => 'Licentiat',
			self::PostBachelorCertificate->value => 'Efter-Bachelor Certifikat',
			self::GraduateDiploma->value => 'Graduate Diploma',
			self::BachelorHonours->value => 'Bachelor med Ære',
			self::MasterDegree->value => 'Master Grad',
			self::GraduateDegree->value => 'Kandidatuddannelse',
			self::MBA->value => 'MBA (Master of Business Administration)',
			self::MFA->value => 'MFA (Master of Fine Arts)',
			self::LLM->value => 'LLM (Master of Laws)',
			self::PostMasterCertificate->value => 'Efter-Master Certifikat',
			self::SpecialistDegree->value => 'Specialistgrad',
			self::DoctoralDegree->value => 'Doktorgrad',
			self::PhD->value => 'PhD (Doktor i Filosofi)',
			self::ProfessionalDoctorate->value => 'Professionel Doktorgrad',
			self::EdD->value => 'EdD (Doktor i Uddannelse)',
			self::DBA->value => 'DBA (Doktor i Forretningsadministration)',
			self::MD->value => 'MD (Doktor i Medicin)',
			self::JD->value => 'JD (Juris Doctor)',
			self::Postdoctoral->value => 'Postdoktor',
			self::ResearchFellow->value => 'Forsker',
			self::ProfessionalCertification->value => 'Professionel Certificering',
			self::IndustryCertification->value => 'Industricertificering',
			self::TechnicalCertification->value => 'Teknisk Certificering',
			self::Other->value => 'Andet',
			self::InProgress->value => 'I Gang',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			self::None->value => '无正规教育',
			self::Informal->value => '非正规教育',
			self::SelfTaught->value => '自学',
			self::ElementarySchool->value => '小学',
			self::PrimarySchool->value => '初等教育',
			self::MiddleSchool->value => '初中',
			self::JuniorHigh->value => '初级中学',
			self::HighSchool->value => '高中',
			self::SecondarySchool->value => '中等教育',
			self::TechnicalSecondary->value => '职业高中',
			self::VocationalSecondary->value => '职业技术学校',
			self::GED->value => '高中同等学历',
			self::Equivalency->value => '同等学历文凭',
			self::SomeCollege->value => '大学肄业',
			self::Certificate->value => '证书',
			self::Diploma->value => '文凭',
			self::VocationalTraining->value => '职业培训',
			self::Apprenticeship->value => '学徒制',
			self::TradeSchool->value => '技工学校',
			self::AssociateDegree->value => '副学士学位',
			self::FoundationDegree->value => '基础学位',
			self::HigherNationalDiploma->value => '国家高等文凭',
			self::BachelorDegree->value => '学士学位',
			self::UndergraduateDegree->value => '本科学位',
			self::FirstDegree->value => '第一学位',
			self::Licentiate->value => '学士学位',
			self::PostBachelorCertificate->value => '学士后证书',
			self::GraduateDiploma->value => '研究生文凭',
			self::BachelorHonours->value => '荣誉学士学位',
			self::MasterDegree->value => '硕士学位',
			self::GraduateDegree->value => '研究生学位',
			self::MBA->value => 'MBA (工商管理硕士)',
			self::MFA->value => 'MFA (艺术硕士)',
			self::LLM->value => 'LLM (法学硕士)',
			self::PostMasterCertificate->value => '硕士后证书',
			self::SpecialistDegree->value => '专业学位',
			self::DoctoralDegree->value => '博士学位',
			self::PhD->value => 'PhD (哲学博士)',
			self::ProfessionalDoctorate->value => '专业博士学位',
			self::EdD->value => 'EdD (教育博士)',
			self::DBA->value => 'DBA (工商管理博士)',
			self::MD->value => 'MD (医学博士)',
			self::JD->value => 'JD (法律博士)',
			self::Postdoctoral->value => '博士后',
			self::ResearchFellow->value => '研究员',
			self::ProfessionalCertification->value => '专业认证',
			self::IndustryCertification->value => '行业认证',
			self::TechnicalCertification->value => '技术认证',
			self::Other->value => '其他',
			self::InProgress->value => '进行中',
		];
	}
}
