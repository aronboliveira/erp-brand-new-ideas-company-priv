<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum JobStage: string
{
	// ========== RECRUITMENT PHASE ==========
	case Sourcing = 'sourcing';
	case Application = 'application';
	case Screening = 'screening';
	case PhoneScreen = 'phone_screen';
	case Assessment = 'assessment';
	case Interview = 'interview';
	case Technical = 'technical';
	case Behavioral = 'behavioral';
	case Panel = 'panel';
	case FinalInterview = 'final_interview';
	case ReferenceCheck = 'reference_check';
	case BackgroundCheck = 'background_check';
	case OfferPreparation = 'offer_preparation';
	case OfferSent = 'offer_sent';
	case OfferNegotiation = 'offer_negotiation';
	case OfferAccepted = 'offer_accepted';
	case PreEmployment = 'pre_employment';
	case ContractSigning = 'contract_signing';
	case Hired = 'hired';

		// ========== ONBOARDING & PROBATION ==========
	case Onboarding = 'onboarding';
	case Orientation = 'orientation';
	case ProbationaryPeriod = 'probationary_period';
	case ProbationActive = 'probation_active';
	case ProbationExtended = 'probation_extended';
	case ProbationCompleted = 'probation_completed';
	case ProbationFailed = 'probation_failed';
	case IncorporationPeriod = 'incorporation_period';

		// ========== REGULAR EMPLOYMENT ==========
	case ActiveEmployment = 'active_employment';
	case ConfirmedEmployee = 'confirmed_employee';
	case PermanentEmployee = 'permanent_employee';
	case RegularStatus = 'regular_status';

		// ========== PERFORMANCE & DEVELOPMENT ==========
	case PerformanceReview = 'performance_review';
	case UnderObservation = 'under_observation';
	case PerformanceMonitoring = 'performance_monitoring';
	case PerformanceImprovementPlan = 'performance_improvement_plan';
	case DevelopmentPlan = 'development_plan';
	case CareerPlanning = 'career_planning';

		// ========== CAREER PROGRESSION ==========
	case PromotionCandidate = 'promotion_candidate';
	case PromotionInProcess = 'promotion_in_process';
	case LateralMoveCandidate = 'lateral_move_candidate';
	case SuccessionCandidate = 'succession_candidate';
	case LeadershipPipeline = 'leadership_pipeline';
	case HighPotential = 'high_potential';

		// ========== ORGANIZATIONAL MOVEMENT ==========
	case InternalTransfer = 'internal_transfer';
	case RoleChange = 'role_change';
	case DepartmentTransfer = 'department_transfer';
	case Secondment = 'secondment';
	case SpecialAssignment = 'special_assignment';
	case ProjectAssignment = 'project_assignment';

		// ========== LEAVE & ABSENCE ==========
	case ExtendedLeave = 'extended_leave';
	case Sabbatical = 'sabbatical';
	case MaternityPaternity = 'maternity_paternity';
	case MedicalLeave = 'medical_leave';
	case UnpaidLeave = 'unpaid_leave';
	case StudyLeave = 'study_leave';

		// ========== CONTRACT MANAGEMENT ==========
	case ContractRenewal = 'contract_renewal';
	case ContractExpiring = 'contract_expiring';
	case ContractExtension = 'contract_extension';
	case FixedTermEnding = 'fixed_term_ending';
	case IndefiniteContract = 'indefinite_contract';

		// ========== DISCIPLINARY & COMPLIANCE ==========
	case DisciplinaryProcess = 'disciplinary_process';
	case Suspension = 'suspension';
	case InvestigationActive = 'investigation_active';
	case CorrectiveAction = 'corrective_action';
	case ComplianceReview = 'compliance_review';

		// ========== PRE-TERMINATION ==========
	case NoticePeriod = 'notice_period';
	case TransitionPeriod = 'transition_period';
	case GardeningLeave = 'gardening_leave';
	case ExitProcess = 'exit_process';
	case KnowledgeTransfer = 'knowledge_transfer';

		// ========== TERMINATION & EXIT ==========
	case ResignationAccepted = 'resignation_accepted';
	case TerminationProcess = 'termination_process';
	case RedundancyProcess = 'redundancy_process';
	case RetirementProcess = 'retirement_process';
	case ExitInterview = 'exit_interview';
	case EmploymentEnded = 'employment_ended';

		// ========== POST-EMPLOYMENT ==========
	case Alumni = 'alumni';
	case RehireEligible = 'rehire_eligible';
	case RehireIneligible = 'rehire_ineligible';
	case BoomerangCandidate = 'boomerang_candidate';

		// ========== TALENT MANAGEMENT ==========
	case BenchResource = 'bench_resource';
	case ResourceRedeployment = 'resource_redeployment';
	case TalentPool = 'talent_pool';
	case StrategicReserve = 'strategic_reserve';
	case KeyResource = 'key_resource';

		// ========== RECRUITMENT OUTCOMES ==========
	case Rejected = 'rejected';
	case Withdrawn = 'withdrawn';
	case OnHold = 'on_hold';
	case CandidateDatabase = 'candidate_database';

	/**
	 * Normalize input to EmployeeStage
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
			// Recruitment
			'sourcing', 'leads', 'leadgeneration' => self::Sourcing,
			'application', 'applied', 'applicationreceived' => self::Application,
			'screening', 'prescreening', 'resumescreening' => self::Screening,
			'phonescreen', 'phonescreening', 'prescreen' => self::PhoneScreen,
			'assessment', 'test', 'evaluation' => self::Assessment,
			'interview', 'firstinterview', 'initialinterview' => self::Interview,
			'technical', 'technicalinterview', 'techassessment' => self::Technical,
			'behavioral', 'behavioralinterview', 'softskills' => self::Behavioral,
			'panel', 'panelinterview', 'committeereview' => self::Panel,
			'finalinterview', 'finalround', 'lastinterview' => self::FinalInterview,
			'referencecheck', 'references', 'refcheck' => self::ReferenceCheck,
			'backgroundcheck', 'bgcheck', 'verification' => self::BackgroundCheck,
			'offerpreparation', 'offerprep', 'draftingoffer' => self::OfferPreparation,
			'offersent', 'offerdelivered', 'offerissued' => self::OfferSent,
			'offernegotiation', 'negotiation', 'termnegotiation' => self::OfferNegotiation,
			'offeraccepted', 'accepted', 'signedoffer' => self::OfferAccepted,
			'preemployment', 'prehire', 'onboardingprep' => self::PreEmployment,
			'contractsigning', 'signing', 'execution' => self::ContractSigning,
			'hired', 'recruited', 'selected' => self::Hired,

			// Onboarding & Probation
			'onboarding', 'induction', 'integration' => self::Onboarding,
			'orientation', 'welcome', 'inductionprogram' => self::Orientation,
			'probationaryperiod', 'probation', 'trialperiod' => self::ProbationaryPeriod,
			'probationactive', 'activeprobation', 'inprobation' => self::ProbationActive,
			'probationextended', 'extendedprobation', 'probationextension' => self::ProbationExtended,
			'probationcompleted', 'probationpassed', 'confirmed' => self::ProbationCompleted,
			'probationfailed', 'probationunsuccessful', 'terminationprobation' => self::ProbationFailed,
			'incorporationperiod', 'trainingperiod', 'rampup' => self::IncorporationPeriod,

			// Regular Employment
			'activeemployment', 'active', 'currentlyemployed' => self::ActiveEmployment,
			'confirmedemployee', 'confirmed', 'permanentstatus' => self::ConfirmedEmployee,
			'permanentemployee', 'permanent', 'indefinite' => self::PermanentEmployee,
			'regularstatus', 'regular', 'fullstatus' => self::RegularStatus,

			// Performance & Development
			'performancereview', 'review', 'appraisal' => self::PerformanceReview,
			'underobservation', 'observation', 'monitoringperiod' => self::UnderObservation,
			'performancemonitoring', 'monitoring', 'performancewatch' => self::PerformanceMonitoring,
			'performanceimprovementplan', 'pip', 'improvementplan' => self::PerformanceImprovementPlan,
			'developmentplan', 'idp', 'careerdevelopment' => self::DevelopmentPlan,
			'careerplanning', 'careerpath', 'successionplanning' => self::CareerPlanning,

			// Career Progression
			'promotioncandidate', 'promotionconsideration', 'promotionlist' => self::PromotionCandidate,
			'promotioninprocess', 'promotionongoing', 'promotionreview' => self::PromotionInProcess,
			'lateralmovecandidate', 'lateraltransfer', 'rolechangeconsideration' => self::LateralMoveCandidate,
			'successioncandidate', 'successionplan', 'futureleader' => self::SuccessionCandidate,
			'leadershippipeline', 'leadershipdevelopment', 'leadershipprogram' => self::LeadershipPipeline,
			'highpotential', 'hipo', 'talent' => self::HighPotential,

			// Organizational Movement
			'internaltransfer', 'transfer', 'internalmove' => self::InternalTransfer,
			'rolechange', 'redesignation', 'positionchange' => self::RoleChange,
			'departmenttransfer', 'depttransfer', 'crossdepartment' => self::DepartmentTransfer,
			'secondment', 'temporaryassignment', 'loaned' => self::Secondment,
			'specialassignment', 'specialproject', 'adhocassignment' => self::SpecialAssignment,
			'projectassignment', 'projectbased', 'temporaryrole' => self::ProjectAssignment,

			// Leave & Absence
			'extendedleave', 'longleave', 'leaveofabsence' => self::ExtendedLeave,
			'sabbatical', 'sabbaticalleave', 'careerbreak' => self::Sabbatical,
			'maternitypaternity', 'parentalleave', 'familyleave' => self::MaternityPaternity,
			'medicalleave', 'sickleave', 'healthleave' => self::MedicalLeave,
			'unpaidleave', 'lwop', 'leavewithoutpay' => self::UnpaidLeave,
			'studyleave', 'educationalleave', 'trainingleave' => self::StudyLeave,

			// Contract Management
			'contractrenewal', 'renewal', 'contractrenew' => self::ContractRenewal,
			'contractexpiring', 'expiring', 'endingsoon' => self::ContractExpiring,
			'contractextension', 'extension', 'extendedcontract' => self::ContractExtension,
			'fixedtermending', 'fixedtermexpiring', 'termending' => self::FixedTermEnding,
			'indefinitecontract', 'openended', 'permanentcontract' => self::IndefiniteContract,

			// Disciplinary & Compliance
			'disciplinaryprocess', 'disciplinary', 'disciplinaryaction' => self::DisciplinaryProcess,
			'suspension', 'suspended', 'temporarilysuspended' => self::Suspension,
			'investigationactive', 'underinvestigation', 'investigation' => self::InvestigationActive,
			'correctiveaction', 'correctivemeasures', 'remedialaction' => self::CorrectiveAction,
			'compliancereview', 'compliancecheck', 'regulatoryreview' => self::ComplianceReview,

			// Pre-Termination
			'noticeperiod', 'servingnotice', 'resignationnotice' => self::NoticePeriod,
			'transitionperiod', 'handover', 'knowledgehandover' => self::TransitionPeriod,
			'gardeningleave', 'gardening', 'gardenleave' => self::GardeningLeave,
			'exitprocess', 'exitprocedure', 'separationprocess' => self::ExitProcess,
			'knowledgetransfer', 'kttransfer', 'knowledgehandoff' => self::KnowledgeTransfer,

			// Termination & Exit
			'resignationaccepted', 'resignationapproved', 'resigned' => self::ResignationAccepted,
			'terminationprocess', 'termination', 'dismissalprocess' => self::TerminationProcess,
			'redundancyprocess', 'layoff', 'downsizing' => self::RedundancyProcess,
			'retirementprocess', 'retiring', 'retirement' => self::RetirementProcess,
			'exitinterview', 'exitmeeting', 'separationinterview' => self::ExitInterview,
			'employmentended', 'separated', 'terminated' => self::EmploymentEnded,

			// Post-Employment
			'alumni', 'alumnus', 'formeremployee' => self::Alumni,
			'rehireeligible', 'eligibleforrehire', 'rehireable' => self::RehireEligible,
			'rehireineligible', 'notrehireable', 'ineligibleforrehire' => self::RehireIneligible,
			'boomerangcandidate', 'boomerang', 'returncandidate' => self::BoomerangCandidate,

			// Talent Management
			'benchresource', 'onbench', 'availableresource' => self::BenchResource,
			'resourceredeployment', 'redeployment', 'reassignment' => self::ResourceRedeployment,
			'talentpool', 'talentbench', 'resourcepool' => self::TalentPool,
			'strategicreserve', 'reserve', 'strategicpool' => self::StrategicReserve,
			'keyresource', 'criticalresource', 'keystaff' => self::KeyResource,

			// Recruitment Outcomes
			'rejected', 'notselected', 'declined' => self::Rejected,
			'withdrawn', 'candidatewithdrawn', 'withdrew' => self::Withdrawn,
			'onhold', 'hold', 'paused' => self::OnHold,
			'candidatedatabase', 'talentdatabase', 'futurecandidate' => self::CandidateDatabase,

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
	 * Get label for this employee stage in specified language
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
			// Recruitment - blue tones
			self::Sourcing, self::Application, self::Screening,
			self::PhoneScreen, self::Assessment => '#3d8bfd',

			self::Interview, self::Technical, self::Behavioral,
			self::Panel, self::FinalInterview => '#4d96ff',

			self::ReferenceCheck, self::BackgroundCheck,
			self::OfferPreparation, self::OfferSent => '#5da5ff',

			self::OfferNegotiation, self::OfferAccepted,
			self::PreEmployment, self::ContractSigning => '#6db4ff',

			// Onboarding & Probation - green tones
			self::Hired, self::Onboarding, self::Orientation => '#20c997',

			self::ProbationaryPeriod, self::ProbationActive,
			self::ProbationExtended => '#28a745',

			self::ProbationCompleted, self::IncorporationPeriod => '#34ce57',
			self::ProbationFailed => '#dc3545',

			// Regular Employment - dark green
			self::ActiveEmployment, self::ConfirmedEmployee,
			self::PermanentEmployee, self::RegularStatus => '#198754',

			// Performance & Development - teal
			self::PerformanceReview, self::UnderObservation,
			self::PerformanceMonitoring => '#17a2b8',

			self::PerformanceImprovementPlan => '#fd7e14',
			self::DevelopmentPlan, self::CareerPlanning => '#138496',

			// Career Progression - purple
			self::PromotionCandidate, self::PromotionInProcess => '#6f42c1',
			self::LateralMoveCandidate, self::SuccessionCandidate => '#7952b3',
			self::LeadershipPipeline, self::HighPotential => '#8a63d2',

			// Organizational Movement - indigo
			self::InternalTransfer, self::RoleChange,
			self::DepartmentTransfer => '#6610f2',

			self::Secondment, self::SpecialAssignment,
			self::ProjectAssignment => '#7b2cf8',

			// Leave & Absence - yellow/orange
			self::ExtendedLeave, self::Sabbatical => '#ffc107',
			self::MaternityPaternity, self::MedicalLeave => '#ffca2c',
			self::UnpaidLeave, self::StudyLeave => '#e0a800',

			// Contract Management - cyan
			self::ContractRenewal, self::ContractExpiring,
			self::ContractExtension => '#0dcaf0',

			self::FixedTermEnding, self::IndefiniteContract => '#31d2f2',

			// Disciplinary & Compliance - red
			self::DisciplinaryProcess, self::Suspension,
			self::InvestigationActive => '#dc3545',

			self::CorrectiveAction, self::ComplianceReview => '#e4606d',

			// Pre-Termination - amber
			self::NoticePeriod, self::TransitionPeriod,
			self::GardeningLeave => '#ffc107',

			self::ExitProcess, self::KnowledgeTransfer => '#fd7e14',

			// Termination & Exit - dark red
			self::ResignationAccepted, self::TerminationProcess,
			self::RedundancyProcess => '#b02a37',

			self::RetirementProcess, self::ExitInterview => '#c82333',
			self::EmploymentEnded => '#842029',

			// Post-Employment - gray
			self::Alumni, self::RehireEligible,
			self::RehireIneligible => '#6c757d',

			self::BoomerangCandidate => '#adb5bd',

			// Talent Management - pink
			self::BenchResource, self::ResourceRedeployment,
			self::TalentPool => '#d63384',

			self::StrategicReserve, self::KeyResource => '#e685b5',

			// Recruitment Outcomes - gray variants
			self::Rejected => '#dc3545',
			self::Withdrawn => '#6c757d',
			self::OnHold => '#ffc107',
			self::CandidateDatabase => '#17a2b8',

			default => '#6c757d',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			// Recruitment
			self::Sourcing => 'search',
			self::Application => 'envelope',
			self::Screening => 'filter',
			self::PhoneScreen => 'phone',
			self::Assessment => 'clipboard-check',
			self::Interview, self::Technical, self::Behavioral,
			self::Panel, self::FinalInterview => 'comments',
			self::ReferenceCheck => 'user-check',
			self::BackgroundCheck => 'shield-check',
			self::OfferPreparation, self::OfferSent,
			self::OfferNegotiation => 'file-contract',
			self::OfferAccepted => 'check-circle',
			self::PreEmployment => 'clipboard-list',
			self::ContractSigning => 'signature',

			// Onboarding & Probation
			self::Hired, self::Onboarding => 'user-plus',
			self::Orientation => 'compass',
			self::ProbationaryPeriod, self::ProbationActive,
			self::ProbationExtended => 'hourglass-half',
			self::ProbationCompleted => 'award',
			self::ProbationFailed => 'times-circle',
			self::IncorporationPeriod => 'graduation-cap',

			// Regular Employment
			self::ActiveEmployment, self::ConfirmedEmployee,
			self::PermanentEmployee, self::RegularStatus => 'user-check',

			// Performance & Development
			self::PerformanceReview => 'chart-line',
			self::UnderObservation, self::PerformanceMonitoring => 'eye',
			self::PerformanceImprovementPlan => 'exclamation-triangle',
			self::DevelopmentPlan => 'project-diagram',
			self::CareerPlanning => 'route',

			// Career Progression
			self::PromotionCandidate, self::PromotionInProcess => 'arrow-up',
			self::LateralMoveCandidate => 'exchange-alt',
			self::SuccessionCandidate => 'crown',
			self::LeadershipPipeline => 'users-cog',
			self::HighPotential => 'star',

			// Organizational Movement
			self::InternalTransfer, self::DepartmentTransfer => 'random',
			self::RoleChange => 'user-tag',
			self::Secondment => 'briefcase',
			self::SpecialAssignment, self::ProjectAssignment => 'tasks',

			// Leave & Absence
			self::ExtendedLeave, self::Sabbatical => 'umbrella-beach',
			self::MaternityPaternity => 'baby',
			self::MedicalLeave => 'heartbeat',
			self::UnpaidLeave => 'money-bill-wave',
			self::StudyLeave => 'book',

			// Contract Management
			self::ContractRenewal, self::ContractExtension => 'sync',
			self::ContractExpiring, self::FixedTermEnding => 'hourglass-end',
			self::IndefiniteContract => 'infinity',

			// Disciplinary & Compliance
			self::DisciplinaryProcess => 'gavel',
			self::Suspension => 'ban',
			self::InvestigationActive => 'search',
			self::CorrectiveAction => 'tools',
			self::ComplianceReview => 'shield-alt',

			// Pre-Termination
			self::NoticePeriod => 'bell',
			self::TransitionPeriod, self::KnowledgeTransfer => 'exchange-alt',
			self::GardeningLeave => 'tree',
			self::ExitProcess => 'sign-out-alt',

			// Termination & Exit
			self::ResignationAccepted => 'file-signature',
			self::TerminationProcess => 'user-minus',
			self::RedundancyProcess => 'users-slash',
			self::RetirementProcess => 'walking',
			self::ExitInterview => 'comment-alt',
			self::EmploymentEnded => 'user-times',

			// Post-Employment
			self::Alumni => 'university',
			self::RehireEligible => 'redo',
			self::RehireIneligible => 'times',
			self::BoomerangCandidate => 'undo',

			// Talent Management
			self::BenchResource => 'couch',
			self::ResourceRedeployment => 'recycle',
			self::TalentPool => 'swimming-pool',
			self::StrategicReserve => 'gem',
			self::KeyResource => 'key',

			// Recruitment Outcomes
			self::Rejected => 'times',
			self::Withdrawn => 'hand-paper',
			self::OnHold => 'pause',
			self::CandidateDatabase => 'database',

			default => 'user',
		};
	}

	/**
	 * Check if this is a recruitment stage
	 */
	public function isRecruitmentStage(): bool
	{
		return in_array($this, [
			self::Sourcing,
			self::Application,
			self::Screening,
			self::PhoneScreen,
			self::Assessment,
			self::Interview,
			self::Technical,
			self::Behavioral,
			self::Panel,
			self::FinalInterview,
			self::ReferenceCheck,
			self::BackgroundCheck,
			self::OfferPreparation,
			self::OfferSent,
			self::OfferNegotiation,
			self::OfferAccepted,
			self::PreEmployment,
			self::ContractSigning,
			self::Hired,
			self::Rejected,
			self::Withdrawn,
			self::OnHold,
			self::CandidateDatabase,
		]);
	}

	/**
	 * Check if this is an active employment stage
	 */
	public function isActiveEmployment(): bool
	{
		return in_array($this, [
			self::ActiveEmployment,
			self::ConfirmedEmployee,
			self::PermanentEmployee,
			self::RegularStatus,
			self::Onboarding,
			self::Orientation,
			self::ProbationaryPeriod,
			self::ProbationActive,
			self::ProbationExtended,
			self::ProbationCompleted,
			self::IncorporationPeriod,
		]);
	}

	/**
	 * Check if this is a transition stage
	 */
	public function isTransitionStage(): bool
	{
		return in_array($this, [
			self::NoticePeriod,
			self::TransitionPeriod,
			self::GardeningLeave,
			self::ExitProcess,
			self::KnowledgeTransfer,
			self::ExitInterview,
		]);
	}

	/**
	 * Check if this is an end stage
	 */
	public function isEndStage(): bool
	{
		return in_array($this, [
			self::EmploymentEnded,
			self::Alumni,
			self::RehireEligible,
			self::RehireIneligible,
			self::ProbationFailed,
			self::Rejected,
			self::Withdrawn,
		]);
	}

	/**
	 * Get stage category
	 */
	public function getCategory(): string
	{
		return match ($this) {
			// Recruitment
			self::Sourcing, self::Application, self::Screening,
			self::PhoneScreen, self::Assessment, self::Interview,
			self::Technical, self::Behavioral, self::Panel,
			self::FinalInterview, self::ReferenceCheck,
			self::BackgroundCheck, self::OfferPreparation,
			self::OfferSent, self::OfferNegotiation,
			self::OfferAccepted, self::PreEmployment,
			self::ContractSigning, self::Hired,
			self::Rejected, self::Withdrawn, self::OnHold,
			self::CandidateDatabase => 'recruitment',

			// Onboarding
			self::Onboarding, self::Orientation,
			self::ProbationaryPeriod, self::ProbationActive,
			self::ProbationExtended, self::ProbationCompleted,
			self::ProbationFailed, self::IncorporationPeriod => 'onboarding',

			// Active Employment
			self::ActiveEmployment, self::ConfirmedEmployee,
			self::PermanentEmployee, self::RegularStatus => 'active',

			// Development
			self::PerformanceReview, self::UnderObservation,
			self::PerformanceMonitoring, self::PerformanceImprovementPlan,
			self::DevelopmentPlan, self::CareerPlanning,
			self::PromotionCandidate, self::PromotionInProcess,
			self::LateralMoveCandidate, self::SuccessionCandidate,
			self::LeadershipPipeline, self::HighPotential => 'development',

			// Movement
			self::InternalTransfer, self::RoleChange,
			self::DepartmentTransfer, self::Secondment,
			self::SpecialAssignment, self::ProjectAssignment => 'movement',

			// Leave
			self::ExtendedLeave, self::Sabbatical,
			self::MaternityPaternity, self::MedicalLeave,
			self::UnpaidLeave, self::StudyLeave => 'leave',

			// Contract
			self::ContractRenewal, self::ContractExpiring,
			self::ContractExtension, self::FixedTermEnding,
			self::IndefiniteContract => 'contract',

			// Disciplinary
			self::DisciplinaryProcess, self::Suspension,
			self::InvestigationActive, self::CorrectiveAction,
			self::ComplianceReview => 'disciplinary',

			// Exit
			self::NoticePeriod, self::TransitionPeriod,
			self::GardeningLeave, self::ExitProcess,
			self::KnowledgeTransfer, self::ResignationAccepted,
			self::TerminationProcess, self::RedundancyProcess,
			self::RetirementProcess, self::ExitInterview,
			self::EmploymentEnded => 'exit',

			// Post-Employment
			self::Alumni, self::RehireEligible,
			self::RehireIneligible, self::BoomerangCandidate => 'post_employment',

			// Talent Management
			self::BenchResource, self::ResourceRedeployment,
			self::TalentPool, self::StrategicReserve,
			self::KeyResource => 'talent_management',

			default => 'other',
		};
	}

	/**
	 * Check if stage requires manager approval
	 */
	public function requiresManagerApproval(): bool
	{
		return in_array($this, [
			self::OfferPreparation,
			self::OfferSent,
			self::ProbationExtended,
			self::ProbationCompleted,
			self::ProbationFailed,
			self::PromotionInProcess,
			self::InternalTransfer,
			self::RoleChange,
			self::DepartmentTransfer,
			self::ContractRenewal,
			self::ContractExtension,
			self::DisciplinaryProcess,
			self::Suspension,
			self::CorrectiveAction,
			self::TerminationProcess,
			self::RedundancyProcess,
		]);
	}

	/**
	 * Check if stage is positive/growth oriented
	 */
	public function isPositiveStage(): bool
	{
		return in_array($this, [
			self::Hired,
			self::ProbationCompleted,
			self::ActiveEmployment,
			self::ConfirmedEmployee,
			self::PermanentEmployee,
			self::PromotionCandidate,
			self::PromotionInProcess,
			self::SuccessionCandidate,
			self::LeadershipPipeline,
			self::HighPotential,
			self::KeyResource,
			self::ContractRenewal,
			self::ContractExtension,
			self::IndefiniteContract,
			self::RehireEligible,
			self::Alumni,
		]);
	}

	/**
	 * Check if stage is negative/requires attention
	 */
	public function isAttentionRequired(): bool
	{
		return in_array($this, [
			self::PerformanceImprovementPlan,
			self::UnderObservation,
			self::PerformanceMonitoring,
			self::ProbationFailed,
			self::ProbationExtended,
			self::DisciplinaryProcess,
			self::Suspension,
			self::InvestigationActive,
			self::CorrectiveAction,
			self::ContractExpiring,
			self::FixedTermEnding,
			self::BenchResource,
			self::TerminationProcess,
			self::RedundancyProcess,
			self::RehireIneligible,
		]);
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'Sourcing',
			self::Application->value => 'Application Received',
			self::Screening->value => 'Screening',
			self::PhoneScreen->value => 'Phone Screen',
			self::Assessment->value => 'Assessment',
			self::Interview->value => 'Interview',
			self::Technical->value => 'Technical Interview',
			self::Behavioral->value => 'Behavioral Interview',
			self::Panel->value => 'Panel Interview',
			self::FinalInterview->value => 'Final Interview',
			self::ReferenceCheck->value => 'Reference Check',
			self::BackgroundCheck->value => 'Background Check',
			self::OfferPreparation->value => 'Offer Preparation',
			self::OfferSent->value => 'Offer Sent',
			self::OfferNegotiation->value => 'Offer Negotiation',
			self::OfferAccepted->value => 'Offer Accepted',
			self::PreEmployment->value => 'Pre-Employment',
			self::ContractSigning->value => 'Contract Signing',
			self::Hired->value => 'Hired',

			// Onboarding & Probation
			self::Onboarding->value => 'Onboarding',
			self::Orientation->value => 'Orientation',
			self::ProbationaryPeriod->value => 'Probationary Period',
			self::ProbationActive->value => 'Active Probation',
			self::ProbationExtended->value => 'Extended Probation',
			self::ProbationCompleted->value => 'Probation Completed',
			self::ProbationFailed->value => 'Probation Failed',
			self::IncorporationPeriod->value => 'Incorporation Period',

			// Regular Employment
			self::ActiveEmployment->value => 'Active Employment',
			self::ConfirmedEmployee->value => 'Confirmed Employee',
			self::PermanentEmployee->value => 'Permanent Employee',
			self::RegularStatus->value => 'Regular Status',

			// Performance & Development
			self::PerformanceReview->value => 'Performance Review',
			self::UnderObservation->value => 'Under Observation',
			self::PerformanceMonitoring->value => 'Performance Monitoring',
			self::PerformanceImprovementPlan->value => 'Performance Improvement Plan',
			self::DevelopmentPlan->value => 'Development Plan',
			self::CareerPlanning->value => 'Career Planning',

			// Career Progression
			self::PromotionCandidate->value => 'Promotion Candidate',
			self::PromotionInProcess->value => 'Promotion in Process',
			self::LateralMoveCandidate->value => 'Lateral Move Candidate',
			self::SuccessionCandidate->value => 'Succession Candidate',
			self::LeadershipPipeline->value => 'Leadership Pipeline',
			self::HighPotential->value => 'High Potential',

			// Organizational Movement
			self::InternalTransfer->value => 'Internal Transfer',
			self::RoleChange->value => 'Role Change',
			self::DepartmentTransfer->value => 'Department Transfer',
			self::Secondment->value => 'Secondment',
			self::SpecialAssignment->value => 'Special Assignment',
			self::ProjectAssignment->value => 'Project Assignment',

			// Leave & Absence
			self::ExtendedLeave->value => 'Extended Leave',
			self::Sabbatical->value => 'Sabbatical',
			self::MaternityPaternity->value => 'Maternity/Paternity Leave',
			self::MedicalLeave->value => 'Medical Leave',
			self::UnpaidLeave->value => 'Unpaid Leave',
			self::StudyLeave->value => 'Study Leave',

			// Contract Management
			self::ContractRenewal->value => 'Contract Renewal',
			self::ContractExpiring->value => 'Contract Expiring',
			self::ContractExtension->value => 'Contract Extension',
			self::FixedTermEnding->value => 'Fixed Term Ending',
			self::IndefiniteContract->value => 'Indefinite Contract',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'Disciplinary Process',
			self::Suspension->value => 'Suspension',
			self::InvestigationActive->value => 'Active Investigation',
			self::CorrectiveAction->value => 'Corrective Action',
			self::ComplianceReview->value => 'Compliance Review',

			// Pre-Termination
			self::NoticePeriod->value => 'Notice Period',
			self::TransitionPeriod->value => 'Transition Period',
			self::GardeningLeave->value => 'Gardening Leave',
			self::ExitProcess->value => 'Exit Process',
			self::KnowledgeTransfer->value => 'Knowledge Transfer',

			// Termination & Exit
			self::ResignationAccepted->value => 'Resignation Accepted',
			self::TerminationProcess->value => 'Termination Process',
			self::RedundancyProcess->value => 'Redundancy Process',
			self::RetirementProcess->value => 'Retirement Process',
			self::ExitInterview->value => 'Exit Interview',
			self::EmploymentEnded->value => 'Employment Ended',

			// Post-Employment
			self::Alumni->value => 'Alumni',
			self::RehireEligible->value => 'Rehire Eligible',
			self::RehireIneligible->value => 'Rehire Ineligible',
			self::BoomerangCandidate->value => 'Boomerang Candidate',

			// Talent Management
			self::BenchResource->value => 'Bench Resource',
			self::ResourceRedeployment->value => 'Resource Redeployment',
			self::TalentPool->value => 'Talent Pool',
			self::StrategicReserve->value => 'Strategic Reserve',
			self::KeyResource->value => 'Key Resource',

			// Recruitment Outcomes
			self::Rejected->value => 'Rejected',
			self::Withdrawn->value => 'Withdrawn',
			self::OnHold->value => 'On Hold',
			self::CandidateDatabase->value => 'Candidate Database',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'Captação',
			self::Application->value => 'Candidatura Recebida',
			self::Screening->value => 'Triagem',
			self::PhoneScreen->value => 'Triagem por Telefone',
			self::Assessment->value => 'Avaliação',
			self::Interview->value => 'Entrevista',
			self::Technical->value => 'Entrevista Técnica',
			self::Behavioral->value => 'Entrevista Comportamental',
			self::Panel->value => 'Entrevista em Painel',
			self::FinalInterview->value => 'Entrevista Final',
			self::ReferenceCheck->value => 'Verificação de Referências',
			self::BackgroundCheck->value => 'Verificação de Antecedentes',
			self::OfferPreparation->value => 'Preparação de Oferta',
			self::OfferSent->value => 'Oferta Enviada',
			self::OfferNegotiation->value => 'Negociação de Oferta',
			self::OfferAccepted->value => 'Oferta Aceita',
			self::PreEmployment->value => 'Pré-Contratação',
			self::ContractSigning->value => 'Assinatura de Contrato',
			self::Hired->value => 'Contratado',

			// Onboarding & Probation
			self::Onboarding->value => 'Integração',
			self::Orientation->value => 'Orientação',
			self::ProbationaryPeriod->value => 'Período de Experiência',
			self::ProbationActive->value => 'Experiência Ativa',
			self::ProbationExtended->value => 'Experiência Estendida',
			self::ProbationCompleted->value => 'Experiência Concluída',
			self::ProbationFailed->value => 'Experiência Não Aprovada',
			self::IncorporationPeriod->value => 'Período de Incorporação',

			// Regular Employment
			self::ActiveEmployment->value => 'Emprego Ativo',
			self::ConfirmedEmployee->value => 'Funcionário Confirmado',
			self::PermanentEmployee->value => 'Funcionário Efetivo',
			self::RegularStatus->value => 'Status Regular',

			// Performance & Development
			self::PerformanceReview->value => 'Avaliação de Desempenho',
			self::UnderObservation->value => 'Sob Observação',
			self::PerformanceMonitoring->value => 'Monitoramento de Desempenho',
			self::PerformanceImprovementPlan->value => 'Plano de Melhoria de Desempenho',
			self::DevelopmentPlan->value => 'Plano de Desenvolvimento',
			self::CareerPlanning->value => 'Planejamento de Carreira',

			// Career Progression
			self::PromotionCandidate->value => 'Candidato a Promoção',
			self::PromotionInProcess->value => 'Promoção em Processo',
			self::LateralMoveCandidate->value => 'Candidato a Movimentação Lateral',
			self::SuccessionCandidate->value => 'Candidato a Sucessão',
			self::LeadershipPipeline->value => 'Pipeline de Liderança',
			self::HighPotential->value => 'Alto Potencial',

			// Organizational Movement
			self::InternalTransfer->value => 'Transferência Interna',
			self::RoleChange->value => 'Mudança de Cargo',
			self::DepartmentTransfer->value => 'Transferência de Departamento',
			self::Secondment->value => 'Cedência',
			self::SpecialAssignment->value => 'Atribuição Especial',
			self::ProjectAssignment->value => 'Atribuição de Projeto',

			// Leave & Absence
			self::ExtendedLeave->value => 'Licença Prolongada',
			self::Sabbatical->value => 'Licença Sabática',
			self::MaternityPaternity->value => 'Licença Maternidade/Paternidade',
			self::MedicalLeave->value => 'Licença Médica',
			self::UnpaidLeave->value => 'Licença Não Remunerada',
			self::StudyLeave->value => 'Licença para Estudos',

			// Contract Management
			self::ContractRenewal->value => 'Renovação de Contrato',
			self::ContractExpiring->value => 'Contrato Expirando',
			self::ContractExtension->value => 'Extensão de Contrato',
			self::FixedTermEnding->value => 'Prazo Determinado Terminando',
			self::IndefiniteContract->value => 'Contrato Indeterminado',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'Processo Disciplinar',
			self::Suspension->value => 'Suspensão',
			self::InvestigationActive->value => 'Investigação Ativa',
			self::CorrectiveAction->value => 'Ação Corretiva',
			self::ComplianceReview->value => 'Revisão de Conformidade',

			// Pre-Termination
			self::NoticePeriod->value => 'Período de Aviso Prévio',
			self::TransitionPeriod->value => 'Período de Transição',
			self::GardeningLeave->value => 'Licença Garden',
			self::ExitProcess->value => 'Processo de Saída',
			self::KnowledgeTransfer->value => 'Transferência de Conhecimento',

			// Termination & Exit
			self::ResignationAccepted->value => 'Demissão Aceita',
			self::TerminationProcess->value => 'Processo de Demissão',
			self::RedundancyProcess->value => 'Processo de Redundância',
			self::RetirementProcess->value => 'Processo de Aposentadoria',
			self::ExitInterview->value => 'Entrevista de Saída',
			self::EmploymentEnded->value => 'Emprego Encerrado',

			// Post-Employment
			self::Alumni->value => 'Ex-Funcionário',
			self::RehireEligible->value => 'Elegível para Recontratação',
			self::RehireIneligible->value => 'Não Elegível para Recontratação',
			self::BoomerangCandidate->value => 'Candidato Boomerang',

			// Talent Management
			self::BenchResource->value => 'Recurso no Banco',
			self::ResourceRedeployment->value => 'Redistribuição de Recursos',
			self::TalentPool->value => 'Banco de Talentos',
			self::StrategicReserve->value => 'Reserva Estratégica',
			self::KeyResource->value => 'Recurso Chave',

			// Recruitment Outcomes
			self::Rejected->value => 'Rejeitado',
			self::Withdrawn->value => 'Retirado',
			self::OnHold->value => 'Em Espera',
			self::CandidateDatabase->value => 'Banco de Candidatos',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'Búsqueda',
			self::Application->value => 'Solicitud Recibida',
			self::Screening->value => 'Preselección',
			self::PhoneScreen->value => 'Preselección Telefónica',
			self::Assessment->value => 'Evaluación',
			self::Interview->value => 'Entrevista',
			self::Technical->value => 'Entrevista Técnica',
			self::Behavioral->value => 'Entrevista Conductual',
			self::Panel->value => 'Entrevista de Panel',
			self::FinalInterview->value => 'Entrevista Final',
			self::ReferenceCheck->value => 'Verificación de Referencias',
			self::BackgroundCheck->value => 'Verificación de Antecedentes',
			self::OfferPreparation->value => 'Preparación de Oferta',
			self::OfferSent->value => 'Oferta Enviada',
			self::OfferNegotiation->value => 'Negociación de Oferta',
			self::OfferAccepted->value => 'Oferta Aceptada',
			self::PreEmployment->value => 'Precontratación',
			self::ContractSigning->value => 'Firma de Contrato',
			self::Hired->value => 'Contratado',

			// Onboarding & Probation
			self::Onboarding->value => 'Incorporación',
			self::Orientation->value => 'Orientación',
			self::ProbationaryPeriod->value => 'Periodo de Prueba',
			self::ProbationActive->value => 'Prueba Activa',
			self::ProbationExtended->value => 'Prueba Extendida',
			self::ProbationCompleted->value => 'Prueba Completada',
			self::ProbationFailed->value => 'Prueba No Superada',
			self::IncorporationPeriod->value => 'Periodo de Incorporación',

			// Regular Employment
			self::ActiveEmployment->value => 'Empleo Activo',
			self::ConfirmedEmployee->value => 'Empleado Confirmado',
			self::PermanentEmployee->value => 'Empleado Permanente',
			self::RegularStatus->value => 'Estado Regular',

			// Performance & Development
			self::PerformanceReview->value => 'Evaluación de Desempeño',
			self::UnderObservation->value => 'Bajo Observación',
			self::PerformanceMonitoring->value => 'Monitoreo de Desempeño',
			self::PerformanceImprovementPlan->value => 'Plan de Mejora de Desempeño',
			self::DevelopmentPlan->value => 'Plan de Desarrollo',
			self::CareerPlanning->value => 'Planificación de Carrera',

			// Career Progression
			self::PromotionCandidate->value => 'Candidato a Promoción',
			self::PromotionInProcess->value => 'Promoción en Proceso',
			self::LateralMoveCandidate->value => 'Candidato a Movimiento Lateral',
			self::SuccessionCandidate->value => 'Candidato a Sucesión',
			self::LeadershipPipeline->value => 'Canalización de Liderazgo',
			self::HighPotential->value => 'Alto Potencial',

			// Organizational Movement
			self::InternalTransfer->value => 'Transferencia Interna',
			self::RoleChange->value => 'Cambio de Rol',
			self::DepartmentTransfer->value => 'Transferencia de Departamento',
			self::Secondment->value => 'Destacamento',
			self::SpecialAssignment->value => 'Asignación Especial',
			self::ProjectAssignment->value => 'Asignación de Proyecto',

			// Leave & Absence
			self::ExtendedLeave->value => 'Licencia Extendida',
			self::Sabbatical->value => 'Sabático',
			self::MaternityPaternity->value => 'Licencia de Maternidad/Paternidad',
			self::MedicalLeave->value => 'Licencia Médica',
			self::UnpaidLeave->value => 'Licencia no Remunerada',
			self::StudyLeave->value => 'Licencia de Estudios',

			// Contract Management
			self::ContractRenewal->value => 'Renovación de Contrato',
			self::ContractExpiring->value => 'Contrato por Expirar',
			self::ContractExtension->value => 'Extensión de Contrato',
			self::FixedTermEnding->value => 'Término Fijo Finalizando',
			self::IndefiniteContract->value => 'Contrato Indefinido',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'Proceso Disciplinario',
			self::Suspension->value => 'Suspensión',
			self::InvestigationActive->value => 'Investigación Activa',
			self::CorrectiveAction->value => 'Acción Correctiva',
			self::ComplianceReview->value => 'Revisión de Cumplimiento',

			// Pre-Termination
			self::NoticePeriod->value => 'Periodo de Preaviso',
			self::TransitionPeriod->value => 'Periodo de Transición',
			self::GardeningLeave->value => 'Licencia de Jardinería',
			self::ExitProcess->value => 'Proceso de Salida',
			self::KnowledgeTransfer->value => 'Transferencia de Conocimiento',

			// Termination & Exit
			self::ResignationAccepted->value => 'Renuncia Aceptada',
			self::TerminationProcess->value => 'Proceso de Terminación',
			self::RedundancyProcess->value => 'Proceso de Redundancia',
			self::RetirementProcess->value => 'Proceso de Jubilación',
			self::ExitInterview->value => 'Entrevista de Salida',
			self::EmploymentEnded->value => 'Empleo Terminado',

			// Post-Employment
			self::Alumni->value => 'Ex-Empleado',
			self::RehireEligible->value => 'Elegible para Recontratación',
			self::RehireIneligible->value => 'No Elegible para Recontratación',
			self::BoomerangCandidate->value => 'Candidato Boomerang',

			// Talent Management
			self::BenchResource->value => 'Recurso en Banco',
			self::ResourceRedeployment->value => 'Redistribución de Recursos',
			self::TalentPool->value => 'Banco de Talento',
			self::StrategicReserve->value => 'Reserva Estratégica',
			self::KeyResource->value => 'Recurso Clave',

			// Recruitment Outcomes
			self::Rejected->value => 'Rechazado',
			self::Withdrawn->value => 'Retirado',
			self::OnHold->value => 'En Espera',
			self::CandidateDatabase->value => 'Base de Datos de Candidatos',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'Personalsuche',
			self::Application->value => 'Bewerbung Eingegangen',
			self::Screening->value => 'Vorauswahl',
			self::PhoneScreen->value => 'Telefonische Vorauswahl',
			self::Assessment->value => 'Bewertung',
			self::Interview->value => 'Vorstellungsgespräch',
			self::Technical->value => 'Technisches Interview',
			self::Behavioral->value => 'Verhaltensinterview',
			self::Panel->value => 'Panel-Interview',
			self::FinalInterview->value => 'Abschlussgespräch',
			self::ReferenceCheck->value => 'Referenzprüfung',
			self::BackgroundCheck->value => 'Hintergrundprüfung',
			self::OfferPreparation->value => 'Angebotserstellung',
			self::OfferSent->value => 'Angebot Versendet',
			self::OfferNegotiation->value => 'Angebotsverhandlung',
			self::OfferAccepted->value => 'Angebot Angenommen',
			self::PreEmployment->value => 'Vor der Einstellung',
			self::ContractSigning->value => 'Vertragsunterzeichnung',
			self::Hired->value => 'Eingestellt',

			// Onboarding & Probation
			self::Onboarding->value => 'Einarbeitung',
			self::Orientation->value => 'Orientierung',
			self::ProbationaryPeriod->value => 'Probezeit',
			self::ProbationActive->value => 'Aktive Probezeit',
			self::ProbationExtended->value => 'Verlängerte Probezeit',
			self::ProbationCompleted->value => 'Probezeit Bestanden',
			self::ProbationFailed->value => 'Probezeit Nicht Bestanden',
			self::IncorporationPeriod->value => 'Eingliederungsphase',

			// Regular Employment
			self::ActiveEmployment->value => 'Aktive Beschäftigung',
			self::ConfirmedEmployee->value => 'Fest Angestellter',
			self::PermanentEmployee->value => 'Unbefristete Anstellung',
			self::RegularStatus->value => 'Regulärer Status',

			// Performance & Development
			self::PerformanceReview->value => 'Leistungsbeurteilung',
			self::UnderObservation->value => 'Unter Beobachtung',
			self::PerformanceMonitoring->value => 'Leistungsüberwachung',
			self::PerformanceImprovementPlan->value => 'Leistungsverbesserungsplan',
			self::DevelopmentPlan->value => 'Entwicklungsplan',
			self::CareerPlanning->value => 'Karriereplanung',

			// Career Progression
			self::PromotionCandidate->value => 'Beförderungskandidat',
			self::PromotionInProcess->value => 'Beförderung in Bearbeitung',
			self::LateralMoveCandidate->value => 'Seitwärtsbewegung Kandidat',
			self::SuccessionCandidate->value => 'Nachfolgekandidat',
			self::LeadershipPipeline->value => 'Führungsnachwuchs',
			self::HighPotential->value => 'High Potential',

			// Organizational Movement
			self::InternalTransfer->value => 'Interne Versetzung',
			self::RoleChange->value => 'Rollenwechsel',
			self::DepartmentTransfer->value => 'Abteilungswechsel',
			self::Secondment->value => 'Abordnung',
			self::SpecialAssignment->value => 'Sonderzuweisung',
			self::ProjectAssignment->value => 'Projektzuweisung',

			// Leave & Absence
			self::ExtendedLeave->value => 'Verlängerte Freistellung',
			self::Sabbatical->value => 'Sabbatical',
			self::MaternityPaternity->value => 'Elternzeit',
			self::MedicalLeave->value => 'Krankheitsurlaub',
			self::UnpaidLeave->value => 'Unbezahlter Urlaub',
			self::StudyLeave->value => 'Bildungsurlaub',

			// Contract Management
			self::ContractRenewal->value => 'Vertragsverlängerung',
			self::ContractExpiring->value => 'Vertrag Läuft Aus',
			self::ContractExtension->value => 'Vertragsverlängerung',
			self::FixedTermEnding->value => 'Befristetes Ende',
			self::IndefiniteContract->value => 'Unbefristeter Vertrag',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'Disziplinarverfahren',
			self::Suspension->value => 'Suspendierung',
			self::InvestigationActive->value => 'Aktive Untersuchung',
			self::CorrectiveAction->value => 'Korrekturmaßnahme',
			self::ComplianceReview->value => 'Compliance-Prüfung',

			// Pre-Termination
			self::NoticePeriod->value => 'Kündigungsfrist',
			self::TransitionPeriod->value => 'Übergangsphase',
			self::GardeningLeave->value => 'Gartenurlaub',
			self::ExitProcess->value => 'Austrittsprozess',
			self::KnowledgeTransfer->value => 'Wissenstransfer',

			// Termination & Exit
			self::ResignationAccepted->value => 'Kündigung Akzeptiert',
			self::TerminationProcess->value => 'Kündigungsprozess',
			self::RedundancyProcess->value => 'Freisetzungsprozess',
			self::RetirementProcess->value => 'Pensionsprozess',
			self::ExitInterview->value => 'Austrittsgespräch',
			self::EmploymentEnded->value => 'Beschäftigung Beendet',

			// Post-Employment
			self::Alumni->value => 'Ehemaliger Mitarbeiter',
			self::RehireEligible->value => 'Wiedereinstellung Möglich',
			self::RehireIneligible->value => 'Wiedereinstellung Nicht Möglich',
			self::BoomerangCandidate->value => 'Boomerang-Kandidat',

			// Talent Management
			self::BenchResource->value => 'Reservekraft',
			self::ResourceRedeployment->value => 'Ressourcenumverteilung',
			self::TalentPool->value => 'Talentpool',
			self::StrategicReserve->value => 'Strategische Reserve',
			self::KeyResource->value => 'Schlüsselressource',

			// Recruitment Outcomes
			self::Rejected->value => 'Abgelehnt',
			self::Withdrawn->value => 'Zurückgezogen',
			self::OnHold->value => 'In Wartestellung',
			self::CandidateDatabase->value => 'Kandidatendatenbank',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'Sourcing',
			self::Application->value => 'Candidature Reçue',
			self::Screening->value => 'Présélection',
			self::PhoneScreen->value => 'Présélection Téléphonique',
			self::Assessment->value => 'Évaluation',
			self::Interview->value => 'Entretien',
			self::Technical->value => 'Entretien Technique',
			self::Behavioral->value => 'Entretien Comportemental',
			self::Panel->value => 'Entretien en Panel',
			self::FinalInterview->value => 'Entretien Final',
			self::ReferenceCheck->value => 'Vérification des Références',
			self::BackgroundCheck->value => 'Vérification des Antécédents',
			self::OfferPreparation->value => 'Préparation de l\'Offre',
			self::OfferSent->value => 'Offre Envoyée',
			self::OfferNegotiation->value => 'Négociation de l\'Offre',
			self::OfferAccepted->value => 'Offre Acceptée',
			self::PreEmployment->value => 'Pré-Embauche',
			self::ContractSigning->value => 'Signature du Contrat',
			self::Hired->value => 'Embauché',

			// Onboarding & Probation
			self::Onboarding->value => 'Intégration',
			self::Orientation->value => 'Orientation',
			self::ProbationaryPeriod->value => 'Période d\'Essai',
			self::ProbationActive->value => 'Essai Actif',
			self::ProbationExtended->value => 'Essai Prolongé',
			self::ProbationCompleted->value => 'Essai Réussi',
			self::ProbationFailed->value => 'Essai Non Réussi',
			self::IncorporationPeriod->value => 'Période d\'Incorporation',

			// Regular Employment
			self::ActiveEmployment->value => 'Emploi Actif',
			self::ConfirmedEmployee->value => 'Employé Confirmé',
			self::PermanentEmployee->value => 'Employé Permanent',
			self::RegularStatus->value => 'Statut Régulier',

			// Performance & Development
			self::PerformanceReview->value => 'Évaluation des Performances',
			self::UnderObservation->value => 'Sous Observation',
			self::PerformanceMonitoring->value => 'Suivi des Performances',
			self::PerformanceImprovementPlan->value => 'Plan d\'Amélioration des Performances',
			self::DevelopmentPlan->value => 'Plan de Développement',
			self::CareerPlanning->value => 'Planification de Carrière',

			// Career Progression
			self::PromotionCandidate->value => 'Candidat à la Promotion',
			self::PromotionInProcess->value => 'Promotion en Cours',
			self::LateralMoveCandidate->value => 'Candidat à un Mouvement Latéral',
			self::SuccessionCandidate->value => 'Candidat à la Succession',
			self::LeadershipPipeline->value => 'Pipeline de Leadership',
			self::HighPotential->value => 'Haut Potentiel',

			// Organizational Movement
			self::InternalTransfer->value => 'Transfert Interne',
			self::RoleChange->value => 'Changement de Rôle',
			self::DepartmentTransfer->value => 'Transfert de Département',
			self::Secondment->value => 'Détachement',
			self::SpecialAssignment->value => 'Mission Spéciale',
			self::ProjectAssignment->value => 'Affectation de Projet',

			// Leave & Absence
			self::ExtendedLeave->value => 'Congé Prolongé',
			self::Sabbatical->value => 'Congé Sabbatique',
			self::MaternityPaternity->value => 'Congé Maternité/Paternité',
			self::MedicalLeave->value => 'Congé Maladie',
			self::UnpaidLeave->value => 'Congé Non Payé',
			self::StudyLeave->value => 'Congé pour Études',

			// Contract Management
			self::ContractRenewal->value => 'Renouvellement de Contrat',
			self::ContractExpiring->value => 'Contrat à Expiration',
			self::ContractExtension->value => 'Prolongation de Contrat',
			self::FixedTermEnding->value => 'Fin de CDD',
			self::IndefiniteContract->value => 'Contrat Indéterminé',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'Processus Disciplinaire',
			self::Suspension->value => 'Suspension',
			self::InvestigationActive->value => 'Enquête Active',
			self::CorrectiveAction->value => 'Action Corrective',
			self::ComplianceReview->value => 'Examen de Conformité',

			// Pre-Termination
			self::NoticePeriod->value => 'Préavis',
			self::TransitionPeriod->value => 'Période de Transition',
			self::GardeningLeave->value => 'Congé de Jardinage',
			self::ExitProcess->value => 'Processus de Sortie',
			self::KnowledgeTransfer->value => 'Transfert de Connaissances',

			// Termination & Exit
			self::ResignationAccepted->value => 'Démission Acceptée',
			self::TerminationProcess->value => 'Processus de Licenciement',
			self::RedundancyProcess->value => 'Processus de Redondance',
			self::RetirementProcess->value => 'Processus de Retraite',
			self::ExitInterview->value => 'Entretien de Sortie',
			self::EmploymentEnded->value => 'Emploi Terminé',

			// Post-Employment
			self::Alumni->value => 'Ancien Employé',
			self::RehireEligible->value => 'Réembauchable',
			self::RehireIneligible->value => 'Non Réembauchable',
			self::BoomerangCandidate->value => 'Candidat Boomerang',

			// Talent Management
			self::BenchResource->value => 'Ressource en Réserve',
			self::ResourceRedeployment->value => 'Redéploiement des Ressources',
			self::TalentPool->value => 'Réserve de Talents',
			self::StrategicReserve->value => 'Réserve Stratégique',
			self::KeyResource->value => 'Ressource Clé',

			// Recruitment Outcomes
			self::Rejected->value => 'Rejeté',
			self::Withdrawn->value => 'Retiré',
			self::OnHold->value => 'En Attente',
			self::CandidateDatabase->value => 'Base de Données des Candidats',
		];
	}
	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'Ricerca Candidati',
			self::Application->value => 'Candidatura Ricevuta',
			self::Screening->value => 'Screening',
			self::PhoneScreen->value => 'Screening Telefonico',
			self::Assessment->value => 'Valutazione',
			self::Interview->value => 'Colloquio',
			self::Technical->value => 'Colloquio Tecnico',
			self::Behavioral->value => 'Colloquio Comportamentale',
			self::Panel->value => 'Colloquio di Gruppo',
			self::FinalInterview->value => 'Colloquio Finale',
			self::ReferenceCheck->value => 'Verifica Referenze',
			self::BackgroundCheck->value => 'Verifica Antecedenti',
			self::OfferPreparation->value => 'Preparazione Offerta',
			self::OfferSent->value => 'Offerta Inviata',
			self::OfferNegotiation->value => 'Negoziazione Offerta',
			self::OfferAccepted->value => 'Offerta Accettata',
			self::PreEmployment->value => 'Pre-Assunzione',
			self::ContractSigning->value => 'Firma Contratto',
			self::Hired->value => 'Assunto',

			// Onboarding & Probation
			self::Onboarding->value => 'Onboarding',
			self::Orientation->value => 'Orientamento',
			self::ProbationaryPeriod->value => 'Periodo di Prova',
			self::ProbationActive->value => 'Prova Attiva',
			self::ProbationExtended->value => 'Prova Estesa',
			self::ProbationCompleted->value => 'Prova Superata',
			self::ProbationFailed->value => 'Prova Non Superata',
			self::IncorporationPeriod->value => 'Periodo di Incorporazione',

			// Regular Employment
			self::ActiveEmployment->value => 'Impiego Attivo',
			self::ConfirmedEmployee->value => 'Dipendente Confermato',
			self::PermanentEmployee->value => 'Dipendente Permanente',
			self::RegularStatus->value => 'Stato Regolare',

			// Performance & Development
			self::PerformanceReview->value => 'Valutazione delle Prestazioni',
			self::UnderObservation->value => 'Sotto Osservazione',
			self::PerformanceMonitoring->value => 'Monitoraggio Prestazioni',
			self::PerformanceImprovementPlan->value => 'Piano di Miglioramento Prestazioni',
			self::DevelopmentPlan->value => 'Piano di Sviluppo',
			self::CareerPlanning->value => 'Pianificazione Carriera',

			// Career Progression
			self::PromotionCandidate->value => 'Candidato alla Promozione',
			self::PromotionInProcess->value => 'Promozione in Corso',
			self::LateralMoveCandidate->value => 'Candidato a Movimento Laterale',
			self::SuccessionCandidate->value => 'Candidato alla Successione',
			self::LeadershipPipeline->value => 'Pipeline di Leadership',
			self::HighPotential->value => 'Alto Potenziale',

			// Organizational Movement
			self::InternalTransfer->value => 'Trasferimento Interno',
			self::RoleChange->value => 'Cambio Ruolo',
			self::DepartmentTransfer->value => 'Trasferimento Dipartimento',
			self::Secondment->value => 'Distacco',
			self::SpecialAssignment->value => 'Incarico Speciale',
			self::ProjectAssignment->value => 'Assegnazione Progetto',

			// Leave & Absence
			self::ExtendedLeave->value => 'Congedo Esteso',
			self::Sabbatical->value => 'Sabbatico',
			self::MaternityPaternity->value => 'Congedo di Maternità/Paternità',
			self::MedicalLeave->value => 'Congedo Medico',
			self::UnpaidLeave->value => 'Congedo Non Retribuito',
			self::StudyLeave->value => 'Congedo per Studio',

			// Contract Management
			self::ContractRenewal->value => 'Rinnovo Contratto',
			self::ContractExpiring->value => 'Contratto in Scadenza',
			self::ContractExtension->value => 'Proroga Contratto',
			self::FixedTermEnding->value => 'Scadenza Termine Fisso',
			self::IndefiniteContract->value => 'Contratto a Tempo Indeterminato',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'Procedimento Disciplinare',
			self::Suspension->value => 'Sospensione',
			self::InvestigationActive->value => 'Indagine Attiva',
			self::CorrectiveAction->value => 'Azione Correttiva',
			self::ComplianceReview->value => 'Revisione Conformità',

			// Pre-Termination
			self::NoticePeriod->value => 'Periodo di Preavviso',
			self::TransitionPeriod->value => 'Periodo di Transizione',
			self::GardeningLeave->value => 'Congedo di Giardinaggio',
			self::ExitProcess->value => 'Processo di Uscita',
			self::KnowledgeTransfer->value => 'Trasferimento Conoscenze',

			// Termination & Exit
			self::ResignationAccepted->value => 'Dimissioni Accettate',
			self::TerminationProcess->value => 'Processo di Licenziamento',
			self::RedundancyProcess->value => 'Processo di Ridondanza',
			self::RetirementProcess->value => 'Processo di Pensionamento',
			self::ExitInterview->value => 'Colloquio di Uscita',
			self::EmploymentEnded->value => 'Impiego Terminato',

			// Post-Employment
			self::Alumni->value => 'Ex Dipendente',
			self::RehireEligible->value => 'Riassegnabile',
			self::RehireIneligible->value => 'Non Riassegnabile',
			self::BoomerangCandidate->value => 'Candidato Boomerang',

			// Talent Management
			self::BenchResource->value => 'Risorsa in Bench',
			self::ResourceRedeployment->value => 'Ridistribuzione Risorse',
			self::TalentPool->value => 'Pool di Talenti',
			self::StrategicReserve->value => 'Riserva Strategica',
			self::KeyResource->value => 'Risorsa Chiave',

			// Recruitment Outcomes
			self::Rejected->value => 'Rifiutato',
			self::Withdrawn->value => 'Ritirato',
			self::OnHold->value => 'In Attesa',
			self::CandidateDatabase->value => 'Database Candidati',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'Werving',
			self::Application->value => 'Sollicitatie Ontvangen',
			self::Screening->value => 'Screening',
			self::PhoneScreen->value => 'Telefonische Screening',
			self::Assessment->value => 'Beoordeling',
			self::Interview->value => 'Sollicitatiegesprek',
			self::Technical->value => 'Technisch Gesprek',
			self::Behavioral->value => 'Gedragsinterview',
			self::Panel->value => 'Panelgesprek',
			self::FinalInterview->value => 'Laatste Gesprek',
			self::ReferenceCheck->value => 'Referentiecontrole',
			self::BackgroundCheck->value => 'Achtergrondcontrole',
			self::OfferPreparation->value => 'Voorbereiding Aanbod',
			self::OfferSent->value => 'Aanbod Verzonden',
			self::OfferNegotiation->value => 'Aanbodonderhandeling',
			self::OfferAccepted->value => 'Aanbod Geaccepteerd',
			self::PreEmployment->value => 'Pre-Employement',
			self::ContractSigning->value => 'Contractondertekening',
			self::Hired->value => 'Aangenomen',

			// Onboarding & Probation
			self::Onboarding->value => 'Onboarding',
			self::Orientation->value => 'Oriëntatie',
			self::ProbationaryPeriod->value => 'Proeftijd',
			self::ProbationActive->value => 'Actieve Proeftijd',
			self::ProbationExtended->value => 'Verlengde Proeftijd',
			self::ProbationCompleted->value => 'Proeftijd Voltooid',
			self::ProbationFailed->value => 'Proeftijd Niet Gehaald',
			self::IncorporationPeriod->value => 'Incorporate Periode',

			// Regular Employment
			self::ActiveEmployment->value => 'Actieve Dienst',
			self::ConfirmedEmployee->value => 'Bevestigd Medewerker',
			self::PermanentEmployee->value => 'Vaste Medewerker',
			self::RegularStatus->value => 'Reguliere Status',

			// Performance & Development
			self::PerformanceReview->value => 'Functioneringsgesprek',
			self::UnderObservation->value => 'Onder Observatie',
			self::PerformanceMonitoring->value => 'Prestatiebewaking',
			self::PerformanceImprovementPlan->value => 'Prestatieverbeterplan',
			self::DevelopmentPlan->value => 'Ontwikkelingsplan',
			self::CareerPlanning->value => 'Carrièreplanning',

			// Career Progression
			self::PromotionCandidate->value => 'Promotiekandidaat',
			self::PromotionInProcess->value => 'Promotie in Uitvoering',
			self::LateralMoveCandidate->value => 'Zijwaartse Beweging Kandidaat',
			self::SuccessionCandidate->value => 'Opvolgingskandidaat',
			self::LeadershipPipeline->value => 'Leiderschapspijplijn',
			self::HighPotential->value => 'Hoog Potentieel',

			// Organizational Movement
			self::InternalTransfer->value => 'Interne Overdracht',
			self::RoleChange->value => 'Functiewijziging',
			self::DepartmentTransfer->value => 'Afdelingsoverdracht',
			self::Secondment->value => 'Detachering',
			self::SpecialAssignment->value => 'Speciale Opdracht',
			self::ProjectAssignment->value => 'Projecttoewijzing',

			// Leave & Absence
			self::ExtendedLeave->value => 'Lang Verlof',
			self::Sabbatical->value => 'Sabbatical',
			self::MaternityPaternity->value => 'Zwangerschaps-/Ouderschapsverlof',
			self::MedicalLeave->value => 'Ziekteverlof',
			self::UnpaidLeave->value => 'Onbetaald Verlof',
			self::StudyLeave->value => 'Studieverlof',

			// Contract Management
			self::ContractRenewal->value => 'Contractverlenging',
			self::ContractExpiring->value => 'Contract Verstrijkt',
			self::ContractExtension->value => 'Contractverlenging',
			self::FixedTermEnding->value => 'Bepaalde Tijd Eindigt',
			self::IndefiniteContract->value => 'Contract voor Onbepaalde Tijd',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'Tuchtprocedure',
			self::Suspension->value => 'Schorsing',
			self::InvestigationActive->value => 'Actief Onderzoek',
			self::CorrectiveAction->value => 'Corrigerende Maatregel',
			self::ComplianceReview->value => 'Compliance Beoordeling',

			// Pre-Termination
			self::NoticePeriod->value => 'Opzegtermijn',
			self::TransitionPeriod->value => 'Overgangsperiode',
			self::GardeningLeave->value => 'Garden Leave',
			self::ExitProcess->value => 'Uitstapprocedure',
			self::KnowledgeTransfer->value => 'Kennisoverdracht',

			// Termination & Exit
			self::ResignationAccepted->value => 'Ontslag Aanvaard',
			self::TerminationProcess->value => 'Ontslagprocedure',
			self::RedundancyProcess->value => 'Redundantieprocedure',
			self::RetirementProcess->value => 'Pensioenprocedure',
			self::ExitInterview->value => 'Exitgesprek',
			self::EmploymentEnded->value => 'Dienstverband Beëindigd',

			// Post-Employment
			self::Alumni->value => 'Ex-Medewerker',
			self::RehireEligible->value => 'Herplaatsbaar',
			self::RehireIneligible->value => 'Niet Herplaatsbaar',
			self::BoomerangCandidate->value => 'Boomerang Kandidaat',

			// Talent Management
			self::BenchResource->value => 'Bench Resource',
			self::ResourceRedeployment->value => 'Herinzet van Middelen',
			self::TalentPool->value => 'Talentenpool',
			self::StrategicReserve->value => 'Strategische Reserve',
			self::KeyResource->value => 'Sleutelresource',

			// Recruitment Outcomes
			self::Rejected->value => 'Afgewezen',
			self::Withdrawn->value => 'Ingetrokken',
			self::OnHold->value => 'In de Wacht',
			self::CandidateDatabase->value => 'Kandidatendatabase',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'استقطاب',
			self::Application->value => 'استلام الطلب',
			self::Screening->value => 'فرز',
			self::PhoneScreen->value => 'فرز هاتفي',
			self::Assessment->value => 'تقييم',
			self::Interview->value => 'مقابلة',
			self::Technical->value => 'مقابلة تقنية',
			self::Behavioral->value => 'مقابلة سلوكية',
			self::Panel->value => 'مقابلة لجنة',
			self::FinalInterview->value => 'المقابلة النهائية',
			self::ReferenceCheck->value => 'التحقق من المراجع',
			self::BackgroundCheck->value => 'التحقق من الخلفية',
			self::OfferPreparation->value => 'إعداد العرض',
			self::OfferSent->value => 'إرسال العرض',
			self::OfferNegotiation->value => 'التفاوض على العرض',
			self::OfferAccepted->value => 'قبول العرض',
			self::PreEmployment->value => 'ما قبل التوظيف',
			self::ContractSigning->value => 'توقيع العقد',
			self::Hired->value => 'تم التوظيف',

			// Onboarding & Probation
			self::Onboarding->value => 'التهيئة',
			self::Orientation->value => 'التوجيه',
			self::ProbationaryPeriod->value => 'فترة التجربة',
			self::ProbationActive->value => 'تجربة نشطة',
			self::ProbationExtended->value => 'تمديد التجربة',
			self::ProbationCompleted->value => 'اجتياز التجربة',
			self::ProbationFailed->value => 'فشل التجربة',
			self::IncorporationPeriod->value => 'فترة الاندماج',

			// Regular Employment
			self::ActiveEmployment->value => 'توظيف نشط',
			self::ConfirmedEmployee->value => 'موظف مثبت',
			self::PermanentEmployee->value => 'موظف دائم',
			self::RegularStatus->value => 'حالة منتظمة',

			// Performance & Development
			self::PerformanceReview->value => 'مراجعة الأداء',
			self::UnderObservation->value => 'تحت الملاحظة',
			self::PerformanceMonitoring->value => 'مراقبة الأداء',
			self::PerformanceImprovementPlan->value => 'خطة تحسين الأداء',
			self::DevelopmentPlan->value => 'خطة تطوير',
			self::CareerPlanning->value => 'تخطيط المسار الوظيفي',

			// Career Progression
			self::PromotionCandidate->value => 'مرشح للترقية',
			self::PromotionInProcess->value => 'ترقية قيد الإجراء',
			self::LateralMoveCandidate->value => 'مرشح لانتقال أفقي',
			self::SuccessionCandidate->value => 'مرشح للخلافة',
			self::LeadershipPipeline->value => 'مسار القيادة',
			self::HighPotential->value => 'إمكانات عالية',

			// Organizational Movement
			self::InternalTransfer->value => 'نقل داخلي',
			self::RoleChange->value => 'تغيير الدور',
			self::DepartmentTransfer->value => 'نقل قسم',
			self::Secondment->value => 'إعارة',
			self::SpecialAssignment->value => 'مهمة خاصة',
			self::ProjectAssignment->value => 'تكليف مشروع',

			// Leave & Absence
			self::ExtendedLeave->value => 'إجازة ممتدة',
			self::Sabbatical->value => 'إجازة تفرغ',
			self::MaternityPaternity->value => 'إجازة أمومة/أبوة',
			self::MedicalLeave->value => 'إجازة مرضية',
			self::UnpaidLeave->value => 'إجازة بدون راتب',
			self::StudyLeave->value => 'إجازة دراسية',

			// Contract Management
			self::ContractRenewal->value => 'تجديد العقد',
			self::ContractExpiring->value => 'عقد على وشك الانتهاء',
			self::ContractExtension->value => 'تمديد العقد',
			self::FixedTermEnding->value => 'نهاية عقد محدد المدة',
			self::IndefiniteContract->value => 'عقد غير محدد المدة',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'إجراء تأديبي',
			self::Suspension->value => 'إيقاف عن العمل',
			self::InvestigationActive->value => 'تحقيق جارٍ',
			self::CorrectiveAction->value => 'إجراء تصحيحي',
			self::ComplianceReview->value => 'مراجعة الامتثال',

			// Pre-Termination
			self::NoticePeriod->value => 'فترة الإشعار',
			self::TransitionPeriod->value => 'فترة انتقالية',
			self::GardeningLeave->value => 'إجازة مدفوعة دون عمل',
			self::ExitProcess->value => 'إجراءات الخروج',
			self::KnowledgeTransfer->value => 'نقل المعرفة',

			// Termination & Exit
			self::ResignationAccepted->value => 'قبول الاستقالة',
			self::TerminationProcess->value => 'إجراءات إنهاء الخدمة',
			self::RedundancyProcess->value => 'إجراءات الاستغناء',
			self::RetirementProcess->value => 'إجراءات التقاعد',
			self::ExitInterview->value => 'مقابلة الخروج',
			self::EmploymentEnded->value => 'انتهاء التوظيف',

			// Post-Employment
			self::Alumni->value => 'موظف سابق',
			self::RehireEligible->value => 'مؤهل لإعادة التوظيف',
			self::RehireIneligible->value => 'غير مؤهل لإعادة التوظيف',
			self::BoomerangCandidate->value => 'مرشح للعودة',

			// Talent Management
			self::BenchResource->value => 'مورد احتياطي',
			self::ResourceRedeployment->value => 'إعادة توزيع الموارد',
			self::TalentPool->value => 'تجمع المواهب',
			self::StrategicReserve->value => 'احتياطي استراتيجي',
			self::KeyResource->value => 'مورد أساسي',

			// Recruitment Outcomes
			self::Rejected->value => 'مرفوض',
			self::Withdrawn->value => 'منسحب',
			self::OnHold->value => 'قيد الانتظار',
			self::CandidateDatabase->value => 'قاعدة بيانات المرشحين',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'Kandidatsøgning',
			self::Application->value => 'Ansøgning modtaget',
			self::Screening->value => 'Screening',
			self::PhoneScreen->value => 'Telefonscreening',
			self::Assessment->value => 'Vurdering',
			self::Interview->value => 'Interview',
			self::Technical->value => 'Teknisk interview',
			self::Behavioral->value => 'Adfærdsinterview',
			self::Panel->value => 'Panelinterview',
			self::FinalInterview->value => 'Endeligt interview',
			self::ReferenceCheck->value => 'Referencecheck',
			self::BackgroundCheck->value => 'Baggrundstjek',
			self::OfferPreparation->value => 'Forberedelse af tilbud',
			self::OfferSent->value => 'Tilbud sendt',
			self::OfferNegotiation->value => 'Tilbudsforhandling',
			self::OfferAccepted->value => 'Tilbud accepteret',
			self::PreEmployment->value => 'Før ansættelse',
			self::ContractSigning->value => 'Kontraktunderskrift',
			self::Hired->value => 'Ansat',

			// Onboarding & Probation
			self::Onboarding->value => 'Onboarding',
			self::Orientation->value => 'Introduktion',
			self::ProbationaryPeriod->value => 'Prøvetid',
			self::ProbationActive->value => 'Aktiv prøvetid',
			self::ProbationExtended->value => 'Forlænget prøvetid',
			self::ProbationCompleted->value => 'Prøvetid gennemført',
			self::ProbationFailed->value => 'Prøvetid ikke bestået',
			self::IncorporationPeriod->value => 'Indkøringsperiode',

			// Regular Employment
			self::ActiveEmployment->value => 'Aktiv ansættelse',
			self::ConfirmedEmployee->value => 'Bekræftet medarbejder',
			self::PermanentEmployee->value => 'Fast medarbejder',
			self::RegularStatus->value => 'Regulær status',

			// Performance & Development
			self::PerformanceReview->value => 'Performancevurdering',
			self::UnderObservation->value => 'Under observation',
			self::PerformanceMonitoring->value => 'Performanceovervågning',
			self::PerformanceImprovementPlan->value => 'Forbedringsplan',
			self::DevelopmentPlan->value => 'Udviklingsplan',
			self::CareerPlanning->value => 'Karriereplanlægning',

			// Career Progression
			self::PromotionCandidate->value => 'Kandidat til forfremmelse',
			self::PromotionInProcess->value => 'Forfremmelse i gang',
			self::LateralMoveCandidate->value => 'Kandidat til lateral flytning',
			self::SuccessionCandidate->value => 'Successionkandidat',
			self::LeadershipPipeline->value => 'Lederpipeline',
			self::HighPotential->value => 'Højt potentiale',

			// Organizational Movement
			self::InternalTransfer->value => 'Intern overflytning',
			self::RoleChange->value => 'Rolleændring',
			self::DepartmentTransfer->value => 'Afdelingsoverflytning',
			self::Secondment->value => 'Udstationering',
			self::SpecialAssignment->value => 'Særlig opgave',
			self::ProjectAssignment->value => 'Projektopgave',

			// Leave & Absence
			self::ExtendedLeave->value => 'Langvarig orlov',
			self::Sabbatical->value => 'Orlov',
			self::MaternityPaternity->value => 'Barselsorlov',
			self::MedicalLeave->value => 'Sygeorlov',
			self::UnpaidLeave->value => 'Orlov uden løn',
			self::StudyLeave->value => 'Studieorlov',

			// Contract Management
			self::ContractRenewal->value => 'Kontraktfornyelse',
			self::ContractExpiring->value => 'Kontrakt udløber',
			self::ContractExtension->value => 'Kontraktforlængelse',
			self::FixedTermEnding->value => 'Tidsbegrænset kontrakt slutter',
			self::IndefiniteContract->value => 'Ubegrænset kontrakt',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'Disciplinærsag',
			self::Suspension->value => 'Suspendering',
			self::InvestigationActive->value => 'Igangværende undersøgelse',
			self::CorrectiveAction->value => 'Korrigerende handling',
			self::ComplianceReview->value => 'Compliance-gennemgang',

			// Pre-Termination
			self::NoticePeriod->value => 'Opsigelsesperiode',
			self::TransitionPeriod->value => 'Overgangsperiode',
			self::GardeningLeave->value => 'Fritstilling',
			self::ExitProcess->value => 'Fratrædelsesproces',
			self::KnowledgeTransfer->value => 'Vidensoverdragelse',

			// Termination & Exit
			self::ResignationAccepted->value => 'Opsigelse accepteret',
			self::TerminationProcess->value => 'Afskedigelsesproces',
			self::RedundancyProcess->value => 'Nedskæringsproces',
			self::RetirementProcess->value => 'Pensionsproces',
			self::ExitInterview->value => 'Fratrædelsessamtale',
			self::EmploymentEnded->value => 'Ansættelse afsluttet',

			// Post-Employment
			self::Alumni->value => 'Tidligere medarbejder',
			self::RehireEligible->value => 'Kan genansættes',
			self::RehireIneligible->value => 'Kan ikke genansættes',
			self::BoomerangCandidate->value => 'Kandidat til tilbagevenden',

			// Talent Management
			self::BenchResource->value => 'Reserveressource',
			self::ResourceRedeployment->value => 'Ressourceomplacering',
			self::TalentPool->value => 'Talentpulje',
			self::StrategicReserve->value => 'Strategisk reserve',
			self::KeyResource->value => 'Nøglemedarbejder',

			// Recruitment Outcomes
			self::Rejected->value => 'Afvist',
			self::Withdrawn->value => 'Trukket tilbage',
			self::OnHold->value => 'På hold',
			self::CandidateDatabase->value => 'Kandidatdatabase',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'איתור מועמדים',
			self::Application->value => 'בקשה התקבלה',
			self::Screening->value => 'סינון',
			self::PhoneScreen->value => 'סינון טלפוני',
			self::Assessment->value => 'הערכה',
			self::Interview->value => 'ראיון',
			self::Technical->value => 'ראיון טכני',
			self::Behavioral->value => 'ראיון התנהגותי',
			self::Panel->value => 'ראיון פאנל',
			self::FinalInterview->value => 'ראיון סופי',
			self::ReferenceCheck->value => 'בדיקת ממליצים',
			self::BackgroundCheck->value => 'בדיקת רקע',
			self::OfferPreparation->value => 'הכנת הצעה',
			self::OfferSent->value => 'הצעה נשלחה',
			self::OfferNegotiation->value => 'משא ומתן על ההצעה',
			self::OfferAccepted->value => 'הצעה התקבלה',
			self::PreEmployment->value => 'טרום העסקה',
			self::ContractSigning->value => 'חתימת חוזה',
			self::Hired->value => 'הועסק',

			// Onboarding & Probation
			self::Onboarding->value => 'קליטה',
			self::Orientation->value => 'אוריינטציה',
			self::ProbationaryPeriod->value => 'תקופת ניסיון',
			self::ProbationActive->value => 'ניסיון פעיל',
			self::ProbationExtended->value => 'הארכת ניסיון',
			self::ProbationCompleted->value => 'תקופת ניסיון הושלמה',
			self::ProbationFailed->value => 'תקופת ניסיון נכשלה',
			self::IncorporationPeriod->value => 'תקופת השתלבות',

			// Regular Employment
			self::ActiveEmployment->value => 'העסקה פעילה',
			self::ConfirmedEmployee->value => 'עובד מאושר',
			self::PermanentEmployee->value => 'עובד קבוע',
			self::RegularStatus->value => 'סטטוס קבוע',

			// Performance & Development
			self::PerformanceReview->value => 'סקירת ביצועים',
			self::UnderObservation->value => 'במעקב',
			self::PerformanceMonitoring->value => 'ניטור ביצועים',
			self::PerformanceImprovementPlan->value => 'תוכנית שיפור ביצועים',
			self::DevelopmentPlan->value => 'תוכנית פיתוח',
			self::CareerPlanning->value => 'תכנון קריירה',

			// Career Progression
			self::PromotionCandidate->value => 'מועמד לקידום',
			self::PromotionInProcess->value => 'קידום בתהליך',
			self::LateralMoveCandidate->value => 'מועמד למעבר רוחבי',
			self::SuccessionCandidate->value => 'מועמד ליורש',
			self::LeadershipPipeline->value => 'מסלול מנהיגות',
			self::HighPotential->value => 'פוטנציאל גבוה',

			// Organizational Movement
			self::InternalTransfer->value => 'העברה פנימית',
			self::RoleChange->value => 'שינוי תפקיד',
			self::DepartmentTransfer->value => 'העברה בין מחלקות',
			self::Secondment->value => 'השאלה',
			self::SpecialAssignment->value => 'משימה מיוחדת',
			self::ProjectAssignment->value => 'שיבוץ לפרויקט',

			// Leave & Absence
			self::ExtendedLeave->value => 'חופשה ממושכת',
			self::Sabbatical->value => 'שבתון',
			self::MaternityPaternity->value => 'חופשת לידה/הורות',
			self::MedicalLeave->value => 'חופשת מחלה',
			self::UnpaidLeave->value => 'חופשה ללא תשלום',
			self::StudyLeave->value => 'חופשת לימודים',

			// Contract Management
			self::ContractRenewal->value => 'חידוש חוזה',
			self::ContractExpiring->value => 'חוזה עומד לפוג',
			self::ContractExtension->value => 'הארכת חוזה',
			self::FixedTermEnding->value => 'סיום חוזה לתקופה קצובה',
			self::IndefiniteContract->value => 'חוזה ללא הגבלת זמן',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'הליך משמעתי',
			self::Suspension->value => 'השעיה',
			self::InvestigationActive->value => 'חקירה פעילה',
			self::CorrectiveAction->value => 'פעולה מתקנת',
			self::ComplianceReview->value => 'בדיקת ציות',

			// Pre-Termination
			self::NoticePeriod->value => 'תקופת הודעה מוקדמת',
			self::TransitionPeriod->value => 'תקופת מעבר',
			self::GardeningLeave->value => 'חופשה בתשלום ללא עבודה',
			self::ExitProcess->value => 'תהליך עזיבה',
			self::KnowledgeTransfer->value => 'העברת ידע',

			// Termination & Exit
			self::ResignationAccepted->value => 'התפטרות התקבלה',
			self::TerminationProcess->value => 'הליך פיטורים',
			self::RedundancyProcess->value => 'הליך צמצומים',
			self::RetirementProcess->value => 'הליך פרישה',
			self::ExitInterview->value => 'ראיון יציאה',
			self::EmploymentEnded->value => 'העסקה הסתיימה',

			// Post-Employment
			self::Alumni->value => 'בוגר הארגון',
			self::RehireEligible->value => 'זכאי לחזרה',
			self::RehireIneligible->value => 'לא זכאי לחזרה',
			self::BoomerangCandidate->value => 'מועמד לחזרה',

			// Talent Management
			self::BenchResource->value => 'משאב בהמתנה',
			self::ResourceRedeployment->value => 'שיבוץ מחדש',
			self::TalentPool->value => 'מאגר כישרונות',
			self::StrategicReserve->value => 'עתודה אסטרטגית',
			self::KeyResource->value => 'משאב מפתח',

			// Recruitment Outcomes
			self::Rejected->value => 'נדחה',
			self::Withdrawn->value => 'נסוג',
			self::OnHold->value => 'בהמתנה',
			self::CandidateDatabase->value => 'מאגר מועמדים',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'ソーシング',
			self::Application->value => '応募受付',
			self::Screening->value => '書類選考',
			self::PhoneScreen->value => '電話面談',
			self::Assessment->value => '適性評価',
			self::Interview->value => '面接',
			self::Technical->value => '技術面接',
			self::Behavioral->value => '行動面接',
			self::Panel->value => 'パネル面接',
			self::FinalInterview->value => '最終面接',
			self::ReferenceCheck->value => 'リファレンスチェック',
			self::BackgroundCheck->value => 'バックグラウンドチェック',
			self::OfferPreparation->value => 'オファー準備',
			self::OfferSent->value => 'オファー送付',
			self::OfferNegotiation->value => 'オファー交渉',
			self::OfferAccepted->value => 'オファー承諾',
			self::PreEmployment->value => '入社前手続き',
			self::ContractSigning->value => '契約署名',
			self::Hired->value => '採用',

			// Onboarding & Probation
			self::Onboarding->value => 'オンボーディング',
			self::Orientation->value => 'オリエンテーション',
			self::ProbationaryPeriod->value => '試用期間',
			self::ProbationActive->value => '試用中',
			self::ProbationExtended->value => '試用延長',
			self::ProbationCompleted->value => '試用完了',
			self::ProbationFailed->value => '試用不合格',
			self::IncorporationPeriod->value => '立ち上がり期間',

			// Regular Employment
			self::ActiveEmployment->value => '在籍中',
			self::ConfirmedEmployee->value => '本採用',
			self::PermanentEmployee->value => '正社員',
			self::RegularStatus->value => '通常ステータス',

			// Performance & Development
			self::PerformanceReview->value => '評価面談',
			self::UnderObservation->value => '観察中',
			self::PerformanceMonitoring->value => 'パフォーマンス監視',
			self::PerformanceImprovementPlan->value => '改善計画（PIP）',
			self::DevelopmentPlan->value => '育成計画',
			self::CareerPlanning->value => 'キャリア計画',

			// Career Progression
			self::PromotionCandidate->value => '昇進候補',
			self::PromotionInProcess->value => '昇進手続き中',
			self::LateralMoveCandidate->value => '横異動候補',
			self::SuccessionCandidate->value => '後継者候補',
			self::LeadershipPipeline->value => 'リーダー育成枠',
			self::HighPotential->value => 'ハイポテンシャル',

			// Organizational Movement
			self::InternalTransfer->value => '社内異動',
			self::RoleChange->value => '役割変更',
			self::DepartmentTransfer->value => '部署異動',
			self::Secondment->value => '出向',
			self::SpecialAssignment->value => '特別任務',
			self::ProjectAssignment->value => 'プロジェクト配属',

			// Leave & Absence
			self::ExtendedLeave->value => '長期休暇',
			self::Sabbatical->value => 'サバティカル',
			self::MaternityPaternity->value => '育児休業',
			self::MedicalLeave->value => '病気休暇',
			self::UnpaidLeave->value => '無給休暇',
			self::StudyLeave->value => '学習休暇',

			// Contract Management
			self::ContractRenewal->value => '契約更新',
			self::ContractExpiring->value => '契約満了間近',
			self::ContractExtension->value => '契約延長',
			self::FixedTermEnding->value => '有期契約終了',
			self::IndefiniteContract->value => '無期契約',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => '懲戒手続き',
			self::Suspension->value => '停職',
			self::InvestigationActive->value => '調査中',
			self::CorrectiveAction->value => '是正措置',
			self::ComplianceReview->value => 'コンプライアンス確認',

			// Pre-Termination
			self::NoticePeriod->value => '予告期間',
			self::TransitionPeriod->value => '引継ぎ期間',
			self::GardeningLeave->value => '自宅待機（有給）',
			self::ExitProcess->value => '退職手続き',
			self::KnowledgeTransfer->value => 'ナレッジ移管',

			// Termination & Exit
			self::ResignationAccepted->value => '退職受理',
			self::TerminationProcess->value => '解雇手続き',
			self::RedundancyProcess->value => '人員整理手続き',
			self::RetirementProcess->value => '退職（定年）手続き',
			self::ExitInterview->value => '退職面談',
			self::EmploymentEnded->value => '雇用終了',

			// Post-Employment
			self::Alumni->value => 'OB/OG',
			self::RehireEligible->value => '再雇用可',
			self::RehireIneligible->value => '再雇用不可',
			self::BoomerangCandidate->value => '再入社候補',

			// Talent Management
			self::BenchResource->value => '待機要員',
			self::ResourceRedeployment->value => '再配置',
			self::TalentPool->value => 'タレントプール',
			self::StrategicReserve->value => '戦略的予備要員',
			self::KeyResource->value => '重要人材',

			// Recruitment Outcomes
			self::Rejected->value => '不採用',
			self::Withdrawn->value => '辞退',
			self::OnHold->value => '保留',
			self::CandidateDatabase->value => '候補者データベース',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'Pozyskiwanie kandydatów',
			self::Application->value => 'Zgłoszenie otrzymane',
			self::Screening->value => 'Selekcja wstępna',
			self::PhoneScreen->value => 'Rozmowa telefoniczna',
			self::Assessment->value => 'Ocena',
			self::Interview->value => 'Rozmowa kwalifikacyjna',
			self::Technical->value => 'Rozmowa techniczna',
			self::Behavioral->value => 'Rozmowa behawioralna',
			self::Panel->value => 'Rozmowa panelowa',
			self::FinalInterview->value => 'Rozmowa finałowa',
			self::ReferenceCheck->value => 'Sprawdzenie referencji',
			self::BackgroundCheck->value => 'Weryfikacja przeszłości',
			self::OfferPreparation->value => 'Przygotowanie oferty',
			self::OfferSent->value => 'Oferta wysłana',
			self::OfferNegotiation->value => 'Negocjacje oferty',
			self::OfferAccepted->value => 'Oferta przyjęta',
			self::PreEmployment->value => 'Przed zatrudnieniem',
			self::ContractSigning->value => 'Podpisanie umowy',
			self::Hired->value => 'Zatrudniony',

			// Onboarding & Probation
			self::Onboarding->value => 'Wdrożenie',
			self::Orientation->value => 'Orientacja',
			self::ProbationaryPeriod->value => 'Okres próbny',
			self::ProbationActive->value => 'Okres próbny (aktywny)',
			self::ProbationExtended->value => 'Przedłużony okres próbny',
			self::ProbationCompleted->value => 'Okres próbny zakończony',
			self::ProbationFailed->value => 'Nieudany okres próbny',
			self::IncorporationPeriod->value => 'Okres adaptacji',

			// Regular Employment
			self::ActiveEmployment->value => 'Aktywne zatrudnienie',
			self::ConfirmedEmployee->value => 'Pracownik potwierdzony',
			self::PermanentEmployee->value => 'Pracownik stały',
			self::RegularStatus->value => 'Status regularny',

			// Performance & Development
			self::PerformanceReview->value => 'Ocena okresowa',
			self::UnderObservation->value => 'Pod obserwacją',
			self::PerformanceMonitoring->value => 'Monitorowanie wyników',
			self::PerformanceImprovementPlan->value => 'Plan poprawy wyników',
			self::DevelopmentPlan->value => 'Plan rozwoju',
			self::CareerPlanning->value => 'Planowanie kariery',

			// Career Progression
			self::PromotionCandidate->value => 'Kandydat do awansu',
			self::PromotionInProcess->value => 'Awans w toku',
			self::LateralMoveCandidate->value => 'Kandydat do przesunięcia poziomego',
			self::SuccessionCandidate->value => 'Kandydat sukcesyjny',
			self::LeadershipPipeline->value => 'Ścieżka liderów',
			self::HighPotential->value => 'Wysoki potencjał',

			// Organizational Movement
			self::InternalTransfer->value => 'Transfer wewnętrzny',
			self::RoleChange->value => 'Zmiana roli',
			self::DepartmentTransfer->value => 'Transfer działu',
			self::Secondment->value => 'Oddelegowanie',
			self::SpecialAssignment->value => 'Zadanie specjalne',
			self::ProjectAssignment->value => 'Przydział do projektu',

			// Leave & Absence
			self::ExtendedLeave->value => 'Dłuższy urlop',
			self::Sabbatical->value => 'Urlop sabatyczny',
			self::MaternityPaternity->value => 'Urlop macierzyński/ojcowski',
			self::MedicalLeave->value => 'Zwolnienie lekarskie',
			self::UnpaidLeave->value => 'Urlop bezpłatny',
			self::StudyLeave->value => 'Urlop szkoleniowy',

			// Contract Management
			self::ContractRenewal->value => 'Odnowienie umowy',
			self::ContractExpiring->value => 'Umowa wygasa',
			self::ContractExtension->value => 'Przedłużenie umowy',
			self::FixedTermEnding->value => 'Koniec umowy terminowej',
			self::IndefiniteContract->value => 'Umowa bezterminowa',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'Postępowanie dyscyplinarne',
			self::Suspension->value => 'Zawieszenie',
			self::InvestigationActive->value => 'Trwa dochodzenie',
			self::CorrectiveAction->value => 'Działanie korygujące',
			self::ComplianceReview->value => 'Przegląd zgodności',

			// Pre-Termination
			self::NoticePeriod->value => 'Okres wypowiedzenia',
			self::TransitionPeriod->value => 'Okres przejściowy',
			self::GardeningLeave->value => 'Zwolnienie z obowiązku świadczenia pracy',
			self::ExitProcess->value => 'Proces odejścia',
			self::KnowledgeTransfer->value => 'Przekazanie wiedzy',

			// Termination & Exit
			self::ResignationAccepted->value => 'Rezygnacja przyjęta',
			self::TerminationProcess->value => 'Proces rozwiązania umowy',
			self::RedundancyProcess->value => 'Proces redukcji etatów',
			self::RetirementProcess->value => 'Proces emerytalny',
			self::ExitInterview->value => 'Rozmowa wyjściowa',
			self::EmploymentEnded->value => 'Zatrudnienie zakończone',

			// Post-Employment
			self::Alumni->value => 'Były pracownik',
			self::RehireEligible->value => 'Możliwe ponowne zatrudnienie',
			self::RehireIneligible->value => 'Brak możliwości ponownego zatrudnienia',
			self::BoomerangCandidate->value => 'Kandydat powrotu',

			// Talent Management
			self::BenchResource->value => 'Zasób w rezerwie',
			self::ResourceRedeployment->value => 'Ponowne przydzielenie zasobów',
			self::TalentPool->value => 'Pula talentów',
			self::StrategicReserve->value => 'Rezerwa strategiczna',
			self::KeyResource->value => 'Kluczowy zasób',

			// Recruitment Outcomes
			self::Rejected->value => 'Odrzucony',
			self::Withdrawn->value => 'Wycofany',
			self::OnHold->value => 'Wstrzymane',
			self::CandidateDatabase->value => 'Baza kandydatów',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'Поиск кандидатов',
			self::Application->value => 'Заявка получена',
			self::Screening->value => 'Первичный отбор',
			self::PhoneScreen->value => 'Телефонный скрининг',
			self::Assessment->value => 'Оценка',
			self::Interview->value => 'Собеседование',
			self::Technical->value => 'Техническое собеседование',
			self::Behavioral->value => 'Поведенческое интервью',
			self::Panel->value => 'Панельное интервью',
			self::FinalInterview->value => 'Финальное собеседование',
			self::ReferenceCheck->value => 'Проверка рекомендаций',
			self::BackgroundCheck->value => 'Проверка благонадежности',
			self::OfferPreparation->value => 'Подготовка оффера',
			self::OfferSent->value => 'Оффер отправлен',
			self::OfferNegotiation->value => 'Переговоры по офферу',
			self::OfferAccepted->value => 'Оффер принят',
			self::PreEmployment->value => 'Перед выходом',
			self::ContractSigning->value => 'Подписание договора',
			self::Hired->value => 'Принят на работу',

			// Onboarding & Probation
			self::Onboarding->value => 'Адаптация',
			self::Orientation->value => 'Вводный инструктаж',
			self::ProbationaryPeriod->value => 'Испытательный срок',
			self::ProbationActive->value => 'Испытательный срок (активен)',
			self::ProbationExtended->value => 'Испытательный срок продлен',
			self::ProbationCompleted->value => 'Испытательный срок пройден',
			self::ProbationFailed->value => 'Испытательный срок не пройден',
			self::IncorporationPeriod->value => 'Период вхождения',

			// Regular Employment
			self::ActiveEmployment->value => 'Активная занятость',
			self::ConfirmedEmployee->value => 'Подтвержденный сотрудник',
			self::PermanentEmployee->value => 'Постоянный сотрудник',
			self::RegularStatus->value => 'Стандартный статус',

			// Performance & Development
			self::PerformanceReview->value => 'Оценка эффективности',
			self::UnderObservation->value => 'Под наблюдением',
			self::PerformanceMonitoring->value => 'Мониторинг эффективности',
			self::PerformanceImprovementPlan->value => 'План улучшения эффективности',
			self::DevelopmentPlan->value => 'План развития',
			self::CareerPlanning->value => 'Планирование карьеры',

			// Career Progression
			self::PromotionCandidate->value => 'Кандидат на повышение',
			self::PromotionInProcess->value => 'Повышение в процессе',
			self::LateralMoveCandidate->value => 'Кандидат на горизонтальный перевод',
			self::SuccessionCandidate->value => 'Кандидат в преемники',
			self::LeadershipPipeline->value => 'Кадровый резерв руководителей',
			self::HighPotential->value => 'Высокий потенциал',

			// Organizational Movement
			self::InternalTransfer->value => 'Внутренний перевод',
			self::RoleChange->value => 'Смена роли',
			self::DepartmentTransfer->value => 'Перевод в другой отдел',
			self::Secondment->value => 'Временное назначение',
			self::SpecialAssignment->value => 'Особое поручение',
			self::ProjectAssignment->value => 'Назначение на проект',

			// Leave & Absence
			self::ExtendedLeave->value => 'Длительный отпуск',
			self::Sabbatical->value => 'Саббатикал',
			self::MaternityPaternity->value => 'Декретный отпуск',
			self::MedicalLeave->value => 'Больничный',
			self::UnpaidLeave->value => 'Отпуск без содержания',
			self::StudyLeave->value => 'Учебный отпуск',

			// Contract Management
			self::ContractRenewal->value => 'Продление договора',
			self::ContractExpiring->value => 'Договор истекает',
			self::ContractExtension->value => 'Продление договора',
			self::FixedTermEnding->value => 'Окончание срочного договора',
			self::IndefiniteContract->value => 'Бессрочный договор',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'Дисциплинарное производство',
			self::Suspension->value => 'Отстранение',
			self::InvestigationActive->value => 'Идет расследование',
			self::CorrectiveAction->value => 'Корректирующие меры',
			self::ComplianceReview->value => 'Проверка соответствия',

			// Pre-Termination
			self::NoticePeriod->value => 'Срок уведомления',
			self::TransitionPeriod->value => 'Переходный период',
			self::GardeningLeave->value => 'Освобождение от работы с сохранением оплаты',
			self::ExitProcess->value => 'Процесс увольнения',
			self::KnowledgeTransfer->value => 'Передача знаний',

			// Termination & Exit
			self::ResignationAccepted->value => 'Заявление об увольнении принято',
			self::TerminationProcess->value => 'Процесс увольнения',
			self::RedundancyProcess->value => 'Процесс сокращения',
			self::RetirementProcess->value => 'Процесс выхода на пенсию',
			self::ExitInterview->value => 'Выходное интервью',
			self::EmploymentEnded->value => 'Трудовые отношения прекращены',

			// Post-Employment
			self::Alumni->value => 'Бывший сотрудник',
			self::RehireEligible->value => 'Можно нанять повторно',
			self::RehireIneligible->value => 'Повторный найм невозможен',
			self::BoomerangCandidate->value => 'Кандидат на возвращение',

			// Talent Management
			self::BenchResource->value => 'Ресурс на бенче',
			self::ResourceRedeployment->value => 'Перераспределение ресурсов',
			self::TalentPool->value => 'Пул талантов',
			self::StrategicReserve->value => 'Стратегический резерв',
			self::KeyResource->value => 'Ключевой ресурс',

			// Recruitment Outcomes
			self::Rejected->value => 'Отклонен',
			self::Withdrawn->value => 'Отозван',
			self::OnHold->value => 'Приостановлено',
			self::CandidateDatabase->value => 'База кандидатов',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => 'Aday Arama',
			self::Application->value => 'Başvuru Alındı',
			self::Screening->value => 'Ön Eleme',
			self::PhoneScreen->value => 'Telefon Ön Görüşmesi',
			self::Assessment->value => 'Değerlendirme',
			self::Interview->value => 'Mülakat',
			self::Technical->value => 'Teknik Mülakat',
			self::Behavioral->value => 'Davranışsal Mülakat',
			self::Panel->value => 'Panel Mülakatı',
			self::FinalInterview->value => 'Final Mülakat',
			self::ReferenceCheck->value => 'Referans Kontrolü',
			self::BackgroundCheck->value => 'Arka Plan Kontrolü',
			self::OfferPreparation->value => 'Teklif Hazırlığı',
			self::OfferSent->value => 'Teklif Gönderildi',
			self::OfferNegotiation->value => 'Teklif Pazarlığı',
			self::OfferAccepted->value => 'Teklif Kabul Edildi',
			self::PreEmployment->value => 'İşe Başlama Öncesi',
			self::ContractSigning->value => 'Sözleşme İmzalama',
			self::Hired->value => 'İşe Alındı',

			// Onboarding & Probation
			self::Onboarding->value => 'İşe Alıştırma',
			self::Orientation->value => 'Oryantasyon',
			self::ProbationaryPeriod->value => 'Deneme Süresi',
			self::ProbationActive->value => 'Deneme Süresi Aktif',
			self::ProbationExtended->value => 'Deneme Süresi Uzatıldı',
			self::ProbationCompleted->value => 'Deneme Süresi Tamamlandı',
			self::ProbationFailed->value => 'Deneme Süresi Başarısız',
			self::IncorporationPeriod->value => 'Uyum Süreci',

			// Regular Employment
			self::ActiveEmployment->value => 'Aktif Çalışma',
			self::ConfirmedEmployee->value => 'Onaylı Çalışan',
			self::PermanentEmployee->value => 'Kadrolu Çalışan',
			self::RegularStatus->value => 'Normal Durum',

			// Performance & Development
			self::PerformanceReview->value => 'Performans Değerlendirmesi',
			self::UnderObservation->value => 'Gözlem Altında',
			self::PerformanceMonitoring->value => 'Performans İzleme',
			self::PerformanceImprovementPlan->value => 'Performans İyileştirme Planı',
			self::DevelopmentPlan->value => 'Gelişim Planı',
			self::CareerPlanning->value => 'Kariyer Planlama',

			// Career Progression
			self::PromotionCandidate->value => 'Terfi Adayı',
			self::PromotionInProcess->value => 'Terfi Sürecinde',
			self::LateralMoveCandidate->value => 'Yatay Geçiş Adayı',
			self::SuccessionCandidate->value => 'Halef Adayı',
			self::LeadershipPipeline->value => 'Liderlik Havuzu',
			self::HighPotential->value => 'Yüksek Potansiyel',

			// Organizational Movement
			self::InternalTransfer->value => 'Kurum İçi Transfer',
			self::RoleChange->value => 'Rol Değişikliği',
			self::DepartmentTransfer->value => 'Departman Transferi',
			self::Secondment->value => 'Geçici Görevlendirme',
			self::SpecialAssignment->value => 'Özel Görev',
			self::ProjectAssignment->value => 'Proje Görevlendirmesi',

			// Leave & Absence
			self::ExtendedLeave->value => 'Uzun Süreli İzin',
			self::Sabbatical->value => 'Sabbatical İzni',
			self::MaternityPaternity->value => 'Doğum/Ebeveyn İzni',
			self::MedicalLeave->value => 'Sağlık İzni',
			self::UnpaidLeave->value => 'Ücretsiz İzin',
			self::StudyLeave->value => 'Eğitim İzni',

			// Contract Management
			self::ContractRenewal->value => 'Sözleşme Yenileme',
			self::ContractExpiring->value => 'Sözleşme Sona Eriyor',
			self::ContractExtension->value => 'Sözleşme Uzatma',
			self::FixedTermEnding->value => 'Belirli Süreli Sözleşme Bitiyor',
			self::IndefiniteContract->value => 'Belirsiz Süreli Sözleşme',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => 'Disiplin Süreci',
			self::Suspension->value => 'Uzaklaştırma',
			self::InvestigationActive->value => 'Soruşturma Aktif',
			self::CorrectiveAction->value => 'Düzeltici Faaliyet',
			self::ComplianceReview->value => 'Uyum İncelemesi',

			// Pre-Termination
			self::NoticePeriod->value => 'İhbar Süresi',
			self::TransitionPeriod->value => 'Geçiş Süreci',
			self::GardeningLeave->value => 'Çalışmadan Ücretli İzin',
			self::ExitProcess->value => 'Ayrılış Süreci',
			self::KnowledgeTransfer->value => 'Bilgi Devri',

			// Termination & Exit
			self::ResignationAccepted->value => 'İstifa Kabul Edildi',
			self::TerminationProcess->value => 'Fesih Süreci',
			self::RedundancyProcess->value => 'Kadro Azaltma Süreci',
			self::RetirementProcess->value => 'Emeklilik Süreci',
			self::ExitInterview->value => 'Çıkış Görüşmesi',
			self::EmploymentEnded->value => 'İstihdam Sona Erdi',

			// Post-Employment
			self::Alumni->value => 'Eski Çalışan',
			self::RehireEligible->value => 'Yeniden İşe Alınabilir',
			self::RehireIneligible->value => 'Yeniden İşe Alınamaz',
			self::BoomerangCandidate->value => 'Geri Dönüş Adayı',

			// Talent Management
			self::BenchResource->value => 'Yedek Kaynak',
			self::ResourceRedeployment->value => 'Kaynak Yeniden Konumlandırma',
			self::TalentPool->value => 'Yetenek Havuzu',
			self::StrategicReserve->value => 'Stratejik Rezerv',
			self::KeyResource->value => 'Kilit Kaynak',

			// Recruitment Outcomes
			self::Rejected->value => 'Reddedildi',
			self::Withdrawn->value => 'Geri Çekildi',
			self::OnHold->value => 'Beklemede',
			self::CandidateDatabase->value => 'Aday Veritabanı',
		];
	}

	// Chinese (Simplified) Labels
	public static function labelsZh(): array
	{
		return [
			// Recruitment
			self::Sourcing->value => '人才寻源',
			self::Application->value => '申请已收到',
			self::Screening->value => '筛选',
			self::PhoneScreen->value => '电话初筛',
			self::Assessment->value => '测评',
			self::Interview->value => '面试',
			self::Technical->value => '技术面试',
			self::Behavioral->value => '行为面试',
			self::Panel->value => '小组面试',
			self::FinalInterview->value => '终面',
			self::ReferenceCheck->value => '推荐人核查',
			self::BackgroundCheck->value => '背景调查',
			self::OfferPreparation->value => 'Offer准备',
			self::OfferSent->value => 'Offer已发送',
			self::OfferNegotiation->value => 'Offer谈判',
			self::OfferAccepted->value => 'Offer已接受',
			self::PreEmployment->value => '入职前',
			self::ContractSigning->value => '合同签署',
			self::Hired->value => '已录用',

			// Onboarding & Probation
			self::Onboarding->value => '入职办理',
			self::Orientation->value => '入职培训',
			self::ProbationaryPeriod->value => '试用期',
			self::ProbationActive->value => '试用中',
			self::ProbationExtended->value => '试用期延长',
			self::ProbationCompleted->value => '试用期通过',
			self::ProbationFailed->value => '试用期未通过',
			self::IncorporationPeriod->value => '融入期',

			// Regular Employment
			self::ActiveEmployment->value => '在职',
			self::ConfirmedEmployee->value => '转正员工',
			self::PermanentEmployee->value => '正式员工',
			self::RegularStatus->value => '常规状态',

			// Performance & Development
			self::PerformanceReview->value => '绩效评审',
			self::UnderObservation->value => '观察期',
			self::PerformanceMonitoring->value => '绩效监控',
			self::PerformanceImprovementPlan->value => '绩效改进计划',
			self::DevelopmentPlan->value => '发展计划',
			self::CareerPlanning->value => '职业规划',

			// Career Progression
			self::PromotionCandidate->value => '晋升候选',
			self::PromotionInProcess->value => '晋升进行中',
			self::LateralMoveCandidate->value => '横向调动候选',
			self::SuccessionCandidate->value => '继任候选',
			self::LeadershipPipeline->value => '领导力梯队',
			self::HighPotential->value => '高潜人才',

			// Organizational Movement
			self::InternalTransfer->value => '内部调动',
			self::RoleChange->value => '岗位变更',
			self::DepartmentTransfer->value => '部门调动',
			self::Secondment->value => '借调',
			self::SpecialAssignment->value => '特别任务',
			self::ProjectAssignment->value => '项目派遣',

			// Leave & Absence
			self::ExtendedLeave->value => '长期休假',
			self::Sabbatical->value => 'Sabbatical休假',
			self::MaternityPaternity->value => '产假/陪产假',
			self::MedicalLeave->value => '病假',
			self::UnpaidLeave->value => '无薪假',
			self::StudyLeave->value => '学习假',

			// Contract Management
			self::ContractRenewal->value => '合同续签',
			self::ContractExpiring->value => '合同即将到期',
			self::ContractExtension->value => '合同延期',
			self::FixedTermEnding->value => '固定期限结束',
			self::IndefiniteContract->value => '无固定期限合同',

			// Disciplinary & Compliance
			self::DisciplinaryProcess->value => '纪律处分流程',
			self::Suspension->value => '停职',
			self::InvestigationActive->value => '调查进行中',
			self::CorrectiveAction->value => '纠正措施',
			self::ComplianceReview->value => '合规审查',

			// Pre-Termination
			self::NoticePeriod->value => '通知期',
			self::TransitionPeriod->value => '交接期',
			self::GardeningLeave->value => '带薪免职期',
			self::ExitProcess->value => '离职流程',
			self::KnowledgeTransfer->value => '知识交接',

			// Termination & Exit
			self::ResignationAccepted->value => '辞职已接受',
			self::TerminationProcess->value => '解雇流程',
			self::RedundancyProcess->value => '裁员流程',
			self::RetirementProcess->value => '退休流程',
			self::ExitInterview->value => '离职面谈',
			self::EmploymentEnded->value => '雇佣结束',

			// Post-Employment
			self::Alumni->value => '前员工',
			self::RehireEligible->value => '可再雇用',
			self::RehireIneligible->value => '不可再雇用',
			self::BoomerangCandidate->value => '回流候选',

			// Talent Management
			self::BenchResource->value => '待岗资源',
			self::ResourceRedeployment->value => '资源再部署',
			self::TalentPool->value => '人才库',
			self::StrategicReserve->value => '战略储备',
			self::KeyResource->value => '关键资源',

			// Recruitment Outcomes
			self::Rejected->value => '未通过',
			self::Withdrawn->value => '已撤回',
			self::OnHold->value => '暂缓',
			self::CandidateDatabase->value => '候选人数据库',
		];
	}
}
