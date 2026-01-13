<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum PlanningScheduleType: string
{
	// Time-based schedules
	case FixedSchedule = 'fixed_schedule';
	case FlexibleSchedule = 'flexible_schedule';
	case RollingSchedule = 'rolling_schedule';
	case ContinuousSchedule = 'continuous_schedule';
	case CyclicSchedule = 'cyclic_schedule';

		// Project/Work schedules
	case ProjectSchedule = 'project_schedule';
	case MilestoneSchedule = 'milestone_schedule';
	case PhaseBasedSchedule = 'phase_based';
	case IterativeSchedule = 'iterative_schedule';
	case AgileSchedule = 'agile_schedule';
	case WaterfallSchedule = 'waterfall_schedule';

		// Resource schedules
	case ResourceSchedule = 'resource_schedule';
	case CapacitySchedule = 'capacity_schedule';
	case WorkSchedule = 'work_schedule';
	case ShiftSchedule = 'shift_schedule';
	case RotationSchedule = 'rotation_schedule';

		// Event-based schedules
	case EventDrivenSchedule = 'event_driven';
	case TriggerBasedSchedule = 'trigger_based';
	case DependencySchedule = 'dependency_schedule';
	case ConditionalSchedule = 'conditional_schedule';

		// Calendar schedules
	case CalendarSchedule = 'calendar_schedule';
	case TimeBlockSchedule = 'time_block_schedule';
	case AppointmentSchedule = 'appointment_schedule';
	case MeetingSchedule = 'meeting_schedule';

		// Production/Operations
	case ProductionSchedule = 'production_schedule';
	case ManufacturingSchedule = 'manufacturing_schedule';
	case MaintenanceSchedule = 'maintenance_schedule';
	case InventorySchedule = 'inventory_schedule';
	case DeliverySchedule = 'delivery_schedule';

		// Financial/Business
	case BudgetSchedule = 'budget_schedule';
	case FinancialSchedule = 'financial_schedule';
	case BillingSchedule = 'billing_schedule';
	case ReportingSchedule = 'reporting_schedule';
	case TaxSchedule = 'tax_schedule';

		// Education/Training
	case AcademicSchedule = 'academic_schedule';
	case TrainingSchedule = 'training_schedule';
	case CourseSchedule = 'course_schedule';
	case ExamSchedule = 'exam_schedule';
	case SessionSchedule = 'session_schedule';

		// Marketing/Sales
	case MarketingSchedule = 'marketing_schedule';
	case CampaignSchedule = 'campaign_schedule';
	case ContentSchedule = 'content_schedule';
	case SocialMediaSchedule = 'social_media_schedule';
	case SalesSchedule = 'sales_schedule';

		// Development/Technical
	case DevelopmentSchedule = 'development_schedule';
	case ReleaseSchedule = 'release_schedule';
	case DeploymentSchedule = 'deployment_schedule';
	case TestingSchedule = 'testing_schedule';
	case SprintSchedule = 'sprint_schedule';

		// Personal/Life
	case PersonalSchedule = 'personal_schedule';
	case FamilySchedule = 'family_schedule';
	case HealthSchedule = 'health_schedule';
	case FitnessSchedule = 'fitness_schedule';
	case TravelSchedule = 'travel_schedule';

		// Other
	case AdHocSchedule = 'ad_hoc_schedule';
	case DynamicSchedule = 'dynamic_schedule';
	case ManualSchedule = 'manual_schedule';
	case AutomatedSchedule = 'automated_schedule';
	case TemplateSchedule = 'template_schedule';
	case Other = 'other';

	/**
	 * Normalize input to PlanningScheduleType
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
			// Time-based
			'fixedschedule', 'fixed', 'rigid', 'static', 'predefined' => self::FixedSchedule,
			'flexibleschedule', 'flexible', 'adaptive', 'adjustable', 'variable' => self::FlexibleSchedule,
			'rollingschedule', 'rolling', 'movingwindow', 'slidingwindow' => self::RollingSchedule,
			'continuousschedule', 'continuous', 'ongoing', 'perpetual', 'nonstop' => self::ContinuousSchedule,
			'cyclicschedule', 'cyclic', 'periodic', 'recurringschedule' => self::CyclicSchedule,

			// Project/Work
			'projectschedule', 'projectplan', 'projecttimeline' => self::ProjectSchedule,
			'milestoneschedule', 'milestonebased', 'milestonetimeline' => self::MilestoneSchedule,
			'phasebased', 'phased', 'staged', 'phasewise' => self::PhaseBasedSchedule,
			'iterativeschedule', 'iterative', 'incremental', 'stepwise' => self::IterativeSchedule,
			'agileschedule', 'agile', 'scrumagile', 'adaptiveplanning' => self::AgileSchedule,
			'waterfallschedule', 'waterfall', 'sequential', 'linearschedule' => self::WaterfallSchedule,

			// Resource
			'resourceschedule', 'resourceplan', 'resourceallocation' => self::ResourceSchedule,
			'capacityschedule', 'capacityplanning', 'capacitymanagement' => self::CapacitySchedule,
			'workschedule', 'workplan', 'workcalendar' => self::WorkSchedule,
			'shiftschedule', 'shift', 'shiftplanning', 'rotatingshift' => self::ShiftSchedule,
			'rotationschedule', 'rotation', 'rotational', 'turnaround' => self::RotationSchedule,

			// Event-based
			'eventdriven', 'eventbased', 'eventtriggered' => self::EventDrivenSchedule,
			'triggerbased', 'triggered', 'conditiontriggered' => self::TriggerBasedSchedule,
			'dependencyschedule', 'dependencybased', 'taskdependency' => self::DependencySchedule,
			'conditionalschedule', 'conditional', 'rulebased' => self::ConditionalSchedule,

			// Calendar
			'calendarschedule', 'calendar', 'calendarbased' => self::CalendarSchedule,
			'timeblockschedule', 'timeblocking', 'timeblock', 'timechunking' => self::TimeBlockSchedule,
			'appointmentschedule', 'appointment', 'appointmentcalendar' => self::AppointmentSchedule,
			'meetingschedule', 'meetingcalendar', 'meetingplan' => self::MeetingSchedule,

			// Production/Operations
			'productionschedule', 'productionplan', 'productionline' => self::ProductionSchedule,
			'manufacturingschedule', 'manufacturingplan', 'assemblyschedule' => self::ManufacturingSchedule,
			'maintenanceschedule', 'maintenanceplan', 'servicesschedule' => self::MaintenanceSchedule,
			'inventoryschedule', 'inventoryplan', 'stockmanagement' => self::InventorySchedule,
			'deliveryschedule', 'deliveryplan', 'shippingschedule' => self::DeliverySchedule,

			// Financial/Business
			'budgetschedule', 'budgetplan', 'budgettimeline' => self::BudgetSchedule,
			'financialschedule', 'financialplan', 'financialcalendar' => self::FinancialSchedule,
			'billingschedule', 'billingcycle', 'invoicingschedule' => self::BillingSchedule,
			'reportingschedule', 'reportingcalendar', 'reporttimeline' => self::ReportingSchedule,
			'taxschedule', 'taxtimeline', 'taxcalendar' => self::TaxSchedule,

			// Education/Training
			'academicschedule', 'academiccalendar', 'schoolschedule' => self::AcademicSchedule,
			'trainingschedule', 'trainingplan', 'trainingcalendar' => self::TrainingSchedule,
			'courseschedule', 'coursecalendar', 'classschedule' => self::CourseSchedule,
			'examschedule', 'examcalendar', 'testingschedule' => self::ExamSchedule,
			'sessionschedule', 'sessionplan', 'classsession' => self::SessionSchedule,

			// Marketing/Sales
			'marketingschedule', 'marketingplan', 'marketingcalendar' => self::MarketingSchedule,
			'campaignschedule', 'campaignplan', 'campaigncalendar' => self::CampaignSchedule,
			'contentschedule', 'contentcalendar', 'editorialcalendar' => self::ContentSchedule,
			'socialmediaschedule', 'socialmediacalendar', 'socialmediaplan' => self::SocialMediaSchedule,
			'salesschedule', 'salesplan', 'salescalendar' => self::SalesSchedule,

			// Development/Technical
			'developmentschedule', 'developmentplan', 'devschedule' => self::DevelopmentSchedule,
			'releaseschedule', 'releaseplan', 'launchschedule' => self::ReleaseSchedule,
			'deploymentschedule', 'deploymentplan', 'implementationschedule' => self::DeploymentSchedule,
			'testingschedule', 'testplan', 'qa schedule' => self::TestingSchedule,
			'sprintschedule', 'sprintplan', 'sprintcalendar' => self::SprintSchedule,

			// Personal/Life
			'personalschedule', 'personalcalendar', 'personalplan' => self::PersonalSchedule,
			'familyschedule', 'familycalendar', 'familyplan' => self::FamilySchedule,
			'healthschedule', 'healthplan', 'medicalschedule' => self::HealthSchedule,
			'fitnessschedule', 'fitnessplan', 'workoutschedule' => self::FitnessSchedule,
			'travelschedule', 'travelplan', 'itinerary' => self::TravelSchedule,

			// Other
			'adhocschedule', 'adhoc', 'impromptu', 'spontaneous' => self::AdHocSchedule,
			'dynamicschedule', 'dynamic', 'realtime', 'responsive' => self::DynamicSchedule,
			'manualschedule', 'manual', 'handcrafted', 'custom' => self::ManualSchedule,
			'automatedschedule', 'automated', 'automatic', 'autoscheduled' => self::AutomatedSchedule,
			'templateschedule', 'template', 'boilerplate', 'predefinedtemplate' => self::TemplateSchedule,
			'other', 'miscellaneous', 'unspecified', 'general' => self::Other,

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
	 * Get label for this schedule type in specified language
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
			// Time-based - blue
			self::FixedSchedule, self::FlexibleSchedule, self::RollingSchedule,
			self::ContinuousSchedule, self::CyclicSchedule => '#3b82f6',

			// Project/Work - indigo
			self::ProjectSchedule, self::MilestoneSchedule, self::PhaseBasedSchedule,
			self::IterativeSchedule, self::AgileSchedule, self::WaterfallSchedule => '#6366f1',

			// Resource - purple
			self::ResourceSchedule, self::CapacitySchedule, self::WorkSchedule,
			self::ShiftSchedule, self::RotationSchedule => '#8b5cf6',

			// Event-based - pink
			self::EventDrivenSchedule, self::TriggerBasedSchedule,
			self::DependencySchedule, self::ConditionalSchedule => '#ec4899',

			// Calendar - teal
			self::CalendarSchedule, self::TimeBlockSchedule,
			self::AppointmentSchedule, self::MeetingSchedule => '#14b8a6',

			// Production/Operations - orange
			self::ProductionSchedule, self::ManufacturingSchedule,
			self::MaintenanceSchedule, self::InventorySchedule,
			self::DeliverySchedule => '#f97316',

			// Financial/Business - green
			self::BudgetSchedule, self::FinancialSchedule, self::BillingSchedule,
			self::ReportingSchedule, self::TaxSchedule => '#10b981',

			// Education/Training - yellow
			self::AcademicSchedule, self::TrainingSchedule, self::CourseSchedule,
			self::ExamSchedule, self::SessionSchedule => '#eab308',

			// Marketing/Sales - red
			self::MarketingSchedule, self::CampaignSchedule, self::ContentSchedule,
			self::SocialMediaSchedule, self::SalesSchedule => '#ef4444',

			// Development/Technical - cyan
			self::DevelopmentSchedule, self::ReleaseSchedule, self::DeploymentSchedule,
			self::TestingSchedule, self::SprintSchedule => '#06b6d4',

			// Personal/Life - lime
			self::PersonalSchedule, self::FamilySchedule, self::HealthSchedule,
			self::FitnessSchedule, self::TravelSchedule => '#84cc16',

			// Other - gray
			self::AdHocSchedule, self::DynamicSchedule, self::ManualSchedule,
			self::AutomatedSchedule, self::TemplateSchedule, self::Other => '#6b7280',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			// Time-based
			self::FixedSchedule => 'calendar-times',
			self::FlexibleSchedule => 'calendar-alt',
			self::RollingSchedule => 'sync-alt',
			self::ContinuousSchedule => 'infinity',
			self::CyclicSchedule => 'redo-alt',

			// Project/Work
			self::ProjectSchedule => 'project-diagram',
			self::MilestoneSchedule => 'flag-checkered',
			self::PhaseBasedSchedule => 'layer-group',
			self::IterativeSchedule => 'redo',
			self::AgileSchedule => 'bolt',
			self::WaterfallSchedule => 'stream',

			// Resource
			self::ResourceSchedule => 'users-cog',
			self::CapacitySchedule => 'tachometer-alt',
			self::WorkSchedule => 'briefcase',
			self::ShiftSchedule => 'clock',
			self::RotationSchedule => 'retweet',

			// Event-based
			self::EventDrivenSchedule => 'bolt',
			self::TriggerBasedSchedule => 'mouse-pointer',
			self::DependencySchedule => 'link',
			self::ConditionalSchedule => 'question-circle',

			// Calendar
			self::CalendarSchedule => 'calendar',
			self::TimeBlockSchedule => 'th-large',
			self::AppointmentSchedule => 'calendar-check',
			self::MeetingSchedule => 'users',

			// Production/Operations
			self::ProductionSchedule => 'industry',
			self::ManufacturingSchedule => 'cogs',
			self::MaintenanceSchedule => 'tools',
			self::InventorySchedule => 'boxes',
			self::DeliverySchedule => 'truck',

			// Financial/Business
			self::BudgetSchedule => 'money-bill-wave',
			self::FinancialSchedule => 'chart-line',
			self::BillingSchedule => 'file-invoice-dollar',
			self::ReportingSchedule => 'chart-bar',
			self::TaxSchedule => 'receipt',

			// Education/Training
			self::AcademicSchedule => 'graduation-cap',
			self::TrainingSchedule => 'chalkboard-teacher',
			self::CourseSchedule => 'book-open',
			self::ExamSchedule => 'file-alt',
			self::SessionSchedule => 'chalkboard',

			// Marketing/Sales
			self::MarketingSchedule => 'bullhorn',
			self::CampaignSchedule => 'rocket',
			self::ContentSchedule => 'newspaper',
			self::SocialMediaSchedule => 'share-alt',
			self::SalesSchedule => 'handshake',

			// Development/Technical
			self::DevelopmentSchedule => 'code',
			self::ReleaseSchedule => 'rocket',
			self::DeploymentSchedule => 'cloud-upload-alt',
			self::TestingSchedule => 'flask',
			self::SprintSchedule => 'running',

			// Personal/Life
			self::PersonalSchedule => 'user',
			self::FamilySchedule => 'users',
			self::HealthSchedule => 'heartbeat',
			self::FitnessSchedule => 'dumbbell',
			self::TravelSchedule => 'plane',

			// Other
			self::AdHocSchedule => 'exclamation-circle',
			self::DynamicSchedule => 'sync',
			self::ManualSchedule => 'hand-paper',
			self::AutomatedSchedule => 'robot',
			self::TemplateSchedule => 'clone',
			self::Other => 'ellipsis-h',
		};
	}

	/**
	 * Check if schedule type is time-based
	 */
	public function isTimeBased(): bool
	{
		return in_array($this, [
			self::FixedSchedule,
			self::FlexibleSchedule,
			self::RollingSchedule,
			self::ContinuousSchedule,
			self::CyclicSchedule,
			self::CalendarSchedule,
			self::TimeBlockSchedule,
		]);
	}

	/**
	 * Check if schedule type is project/work oriented
	 */
	public function isProjectBased(): bool
	{
		return in_array($this, [
			self::ProjectSchedule,
			self::MilestoneSchedule,
			self::PhaseBasedSchedule,
			self::IterativeSchedule,
			self::AgileSchedule,
			self::WaterfallSchedule,
			self::DevelopmentSchedule,
			self::ReleaseSchedule,
			self::DeploymentSchedule,
			self::TestingSchedule,
			self::SprintSchedule,
		]);
	}

	/**
	 * Check if schedule type is resource/people oriented
	 */
	public function isResourceBased(): bool
	{
		return in_array($this, [
			self::ResourceSchedule,
			self::CapacitySchedule,
			self::WorkSchedule,
			self::ShiftSchedule,
			self::RotationSchedule,
		]);
	}

	/**
	 * Check if schedule type is event/trigger based
	 */
	public function isEventBased(): bool
	{
		return in_array($this, [
			self::EventDrivenSchedule,
			self::TriggerBasedSchedule,
			self::DependencySchedule,
			self::ConditionalSchedule,
		]);
	}

	/**
	 * Check if schedule type is recurring/cyclic
	 */
	public function isRecurring(): bool
	{
		return in_array($this, [
			self::CyclicSchedule,
			self::RollingSchedule,
			self::ContinuousSchedule,
			self::IterativeSchedule,
			self::SprintSchedule,
			self::BillingSchedule,
			self::ReportingSchedule,
		]);
	}

	/**
	 * Check if schedule type is flexible/adaptive
	 */
	public function isFlexible(): bool
	{
		return in_array($this, [
			self::FlexibleSchedule,
			self::AgileSchedule,
			self::DynamicSchedule,
			self::AdHocSchedule,
			self::ConditionalSchedule,
		]);
	}

	/**
	 * Get schedule type category
	 */
	public function getCategory(): string
	{
		return match ($this) {
			// Time-based
			self::FixedSchedule, self::FlexibleSchedule, self::RollingSchedule,
			self::ContinuousSchedule, self::CyclicSchedule => 'time_based',

			// Project/Work
			self::ProjectSchedule, self::MilestoneSchedule, self::PhaseBasedSchedule,
			self::IterativeSchedule, self::AgileSchedule, self::WaterfallSchedule => 'project_management',

			// Resource
			self::ResourceSchedule, self::CapacitySchedule, self::WorkSchedule,
			self::ShiftSchedule, self::RotationSchedule => 'resource_management',

			// Event-based
			self::EventDrivenSchedule, self::TriggerBasedSchedule,
			self::DependencySchedule, self::ConditionalSchedule => 'event_based',

			// Calendar
			self::CalendarSchedule, self::TimeBlockSchedule,
			self::AppointmentSchedule, self::MeetingSchedule => 'calendar_based',

			// Production/Operations
			self::ProductionSchedule, self::ManufacturingSchedule,
			self::MaintenanceSchedule, self::InventorySchedule,
			self::DeliverySchedule => 'operations',

			// Financial/Business
			self::BudgetSchedule, self::FinancialSchedule, self::BillingSchedule,
			self::ReportingSchedule, self::TaxSchedule => 'financial',

			// Education/Training
			self::AcademicSchedule, self::TrainingSchedule, self::CourseSchedule,
			self::ExamSchedule, self::SessionSchedule => 'education',

			// Marketing/Sales
			self::MarketingSchedule, self::CampaignSchedule, self::ContentSchedule,
			self::SocialMediaSchedule, self::SalesSchedule => 'marketing_sales',

			// Development/Technical
			self::DevelopmentSchedule, self::ReleaseSchedule, self::DeploymentSchedule,
			self::TestingSchedule, self::SprintSchedule => 'technical',

			// Personal/Life
			self::PersonalSchedule, self::FamilySchedule, self::HealthSchedule,
			self::FitnessSchedule, self::TravelSchedule => 'personal',

			// Other
			self::AdHocSchedule, self::DynamicSchedule, self::ManualSchedule,
			self::AutomatedSchedule, self::TemplateSchedule, self::Other => 'other',
		};
	}

	/**
	 * Get typical planning horizon (in days)
	 */
	public function getTypicalPlanningHorizon(): int
	{
		return match ($this) {
			// Short-term (days to weeks)
			self::AdHocSchedule, self::DynamicSchedule, self::SprintSchedule,
			self::ShiftSchedule, self::MeetingSchedule, self::AppointmentSchedule => 7,

			// Medium-term (weeks to months)
			self::ProjectSchedule, self::MilestoneSchedule, self::IterativeSchedule,
			self::CampaignSchedule, self::TrainingSchedule, self::CourseSchedule,
			self::ReleaseSchedule, self::DeploymentSchedule => 90,

			// Long-term (months to years)
			self::FixedSchedule, self::PhaseBasedSchedule, self::WaterfallSchedule,
			self::AcademicSchedule, self::BudgetSchedule, self::FinancialSchedule,
			self::ProductionSchedule, self::ManufacturingSchedule => 365,

			// Continuous/Rolling
			self::ContinuousSchedule, self::RollingSchedule, self::CyclicSchedule,
			self::BillingSchedule, self::ReportingSchedule => 30, // with rolling updates

			// Default
			default => 30,
		};
	}

	/**
	 * Check if schedule is automated
	 */
	public function isAutomated(): bool
	{
		return in_array($this, [
			self::AutomatedSchedule,
			self::DynamicSchedule,
			self::TriggerBasedSchedule,
			self::EventDrivenSchedule,
		]);
	}

	/**
	 * Get description of the schedule type
	 */
	public function getDescription(): string
	{
		return match ($this) {
			self::FixedSchedule => 'Schedule with predetermined, unchangeable dates and times',
			self::FlexibleSchedule => 'Schedule that allows adjustments and changes as needed',
			self::RollingSchedule => 'Schedule that continuously updates with a moving time window',
			self::ContinuousSchedule => 'Ongoing schedule without defined start or end dates',
			self::CyclicSchedule => 'Schedule that repeats in regular cycles or patterns',
			self::ProjectSchedule => 'Timeline for project tasks, milestones, and deliverables',
			self::MilestoneSchedule => 'Schedule focused on key milestones and critical dates',
			self::PhaseBasedSchedule => 'Schedule organized into distinct phases or stages',
			self::IterativeSchedule => 'Schedule based on repeated cycles of development',
			self::AgileSchedule => 'Flexible, adaptive schedule used in agile methodologies',
			self::WaterfallSchedule => 'Sequential, linear schedule with distinct phases',
			self::ResourceSchedule => 'Schedule focused on allocating and managing resources',
			self::CapacitySchedule => 'Schedule based on resource capacity and availability',
			self::WorkSchedule => 'Schedule defining work hours, shifts, and assignments',
			self::ShiftSchedule => 'Schedule for rotating or fixed work shifts',
			self::RotationSchedule => 'Schedule where tasks or people rotate in cycles',
			self::EventDrivenSchedule => 'Schedule triggered by specific events or conditions',
			self::TriggerBasedSchedule => 'Schedule activated by predefined triggers',
			self::DependencySchedule => 'Schedule where tasks depend on completion of others',
			self::ConditionalSchedule => 'Schedule based on conditional rules and logic',
			self::CalendarSchedule => 'Schedule organized around calendar dates and times',
			self::TimeBlockSchedule => 'Schedule using time blocks for specific activities',
			self::AppointmentSchedule => 'Schedule for appointments and one-on-one meetings',
			self::MeetingSchedule => 'Schedule for group meetings and collaborative sessions',
			self::ProductionSchedule => 'Schedule for manufacturing and production processes',
			self::ManufacturingSchedule => 'Schedule for assembly line and manufacturing',
			self::MaintenanceSchedule => 'Schedule for equipment and facility maintenance',
			self::InventorySchedule => 'Schedule for inventory management and stocktaking',
			self::DeliverySchedule => 'Schedule for shipping and delivery operations',
			self::BudgetSchedule => 'Schedule for financial planning and budget cycles',
			self::FinancialSchedule => 'Schedule for financial reporting and analysis',
			self::BillingSchedule => 'Schedule for invoicing and billing cycles',
			self::ReportingSchedule => 'Schedule for regular reporting and documentation',
			self::TaxSchedule => 'Schedule for tax filings and compliance deadlines',
			self::AcademicSchedule => 'Schedule for educational institutions and courses',
			self::TrainingSchedule => 'Schedule for training programs and workshops',
			self::CourseSchedule => 'Schedule for academic courses and classes',
			self::ExamSchedule => 'Schedule for examinations and assessments',
			self::SessionSchedule => 'Schedule for individual or group sessions',
			self::MarketingSchedule => 'Schedule for marketing activities and campaigns',
			self::CampaignSchedule => 'Schedule for marketing or advertising campaigns',
			self::ContentSchedule => 'Schedule for content creation and publishing',
			self::SocialMediaSchedule => 'Schedule for social media posting and engagement',
			self::SalesSchedule => 'Schedule for sales activities and follow-ups',
			self::DevelopmentSchedule => 'Schedule for software or product development',
			self::ReleaseSchedule => 'Schedule for product releases and updates',
			self::DeploymentSchedule => 'Schedule for system deployments and implementations',
			self::TestingSchedule => 'Schedule for testing and quality assurance',
			self::SprintSchedule => 'Schedule for agile development sprints',
			self::PersonalSchedule => 'Schedule for personal activities and commitments',
			self::FamilySchedule => 'Schedule for family activities and coordination',
			self::HealthSchedule => 'Schedule for medical appointments and health activities',
			self::FitnessSchedule => 'Schedule for workouts and fitness routines',
			self::TravelSchedule => 'Schedule for travel plans and itineraries',
			self::AdHocSchedule => 'Schedule created as needed for specific situations',
			self::DynamicSchedule => 'Schedule that adjusts in real-time based on changes',
			self::ManualSchedule => 'Schedule created and managed manually',
			self::AutomatedSchedule => 'Schedule automatically generated and managed',
			self::TemplateSchedule => 'Schedule based on predefined templates',
			self::Other => 'Other type of schedule not covered by specific categories',
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'Fixed Schedule',
			self::FlexibleSchedule->value => 'Flexible Schedule',
			self::RollingSchedule->value => 'Rolling Schedule',
			self::ContinuousSchedule->value => 'Continuous Schedule',
			self::CyclicSchedule->value => 'Cyclic Schedule',

			// Project/Work
			self::ProjectSchedule->value => 'Project Schedule',
			self::MilestoneSchedule->value => 'Milestone Schedule',
			self::PhaseBasedSchedule->value => 'Phase-Based Schedule',
			self::IterativeSchedule->value => 'Iterative Schedule',
			self::AgileSchedule->value => 'Agile Schedule',
			self::WaterfallSchedule->value => 'Waterfall Schedule',

			// Resource
			self::ResourceSchedule->value => 'Resource Schedule',
			self::CapacitySchedule->value => 'Capacity Schedule',
			self::WorkSchedule->value => 'Work Schedule',
			self::ShiftSchedule->value => 'Shift Schedule',
			self::RotationSchedule->value => 'Rotation Schedule',

			// Event-based
			self::EventDrivenSchedule->value => 'Event-Driven Schedule',
			self::TriggerBasedSchedule->value => 'Trigger-Based Schedule',
			self::DependencySchedule->value => 'Dependency Schedule',
			self::ConditionalSchedule->value => 'Conditional Schedule',

			// Calendar
			self::CalendarSchedule->value => 'Calendar Schedule',
			self::TimeBlockSchedule->value => 'Time Block Schedule',
			self::AppointmentSchedule->value => 'Appointment Schedule',
			self::MeetingSchedule->value => 'Meeting Schedule',

			// Production/Operations
			self::ProductionSchedule->value => 'Production Schedule',
			self::ManufacturingSchedule->value => 'Manufacturing Schedule',
			self::MaintenanceSchedule->value => 'Maintenance Schedule',
			self::InventorySchedule->value => 'Inventory Schedule',
			self::DeliverySchedule->value => 'Delivery Schedule',

			// Financial/Business
			self::BudgetSchedule->value => 'Budget Schedule',
			self::FinancialSchedule->value => 'Financial Schedule',
			self::BillingSchedule->value => 'Billing Schedule',
			self::ReportingSchedule->value => 'Reporting Schedule',
			self::TaxSchedule->value => 'Tax Schedule',

			// Education/Training
			self::AcademicSchedule->value => 'Academic Schedule',
			self::TrainingSchedule->value => 'Training Schedule',
			self::CourseSchedule->value => 'Course Schedule',
			self::ExamSchedule->value => 'Exam Schedule',
			self::SessionSchedule->value => 'Session Schedule',

			// Marketing/Sales
			self::MarketingSchedule->value => 'Marketing Schedule',
			self::CampaignSchedule->value => 'Campaign Schedule',
			self::ContentSchedule->value => 'Content Schedule',
			self::SocialMediaSchedule->value => 'Social Media Schedule',
			self::SalesSchedule->value => 'Sales Schedule',

			// Development/Technical
			self::DevelopmentSchedule->value => 'Development Schedule',
			self::ReleaseSchedule->value => 'Release Schedule',
			self::DeploymentSchedule->value => 'Deployment Schedule',
			self::TestingSchedule->value => 'Testing Schedule',
			self::SprintSchedule->value => 'Sprint Schedule',

			// Personal/Life
			self::PersonalSchedule->value => 'Personal Schedule',
			self::FamilySchedule->value => 'Family Schedule',
			self::HealthSchedule->value => 'Health Schedule',
			self::FitnessSchedule->value => 'Fitness Schedule',
			self::TravelSchedule->value => 'Travel Schedule',

			// Other
			self::AdHocSchedule->value => 'Ad Hoc Schedule',
			self::DynamicSchedule->value => 'Dynamic Schedule',
			self::ManualSchedule->value => 'Manual Schedule',
			self::AutomatedSchedule->value => 'Automated Schedule',
			self::TemplateSchedule->value => 'Template Schedule',
			self::Other->value => 'Other',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'Cronograma Fixo',
			self::FlexibleSchedule->value => 'Cronograma Flexível',
			self::RollingSchedule->value => 'Cronograma Contínuo',
			self::ContinuousSchedule->value => 'Cronograma Contínuo',
			self::CyclicSchedule->value => 'Cronograma Cíclico',

			// Project/Work
			self::ProjectSchedule->value => 'Cronograma de Projeto',
			self::MilestoneSchedule->value => 'Cronograma de Marcos',
			self::PhaseBasedSchedule->value => 'Cronograma por Fases',
			self::IterativeSchedule->value => 'Cronograma Iterativo',
			self::AgileSchedule->value => 'Cronograma Ágil',
			self::WaterfallSchedule->value => 'Cronograma em Cascata',

			// Resource
			self::ResourceSchedule->value => 'Cronograma de Recursos',
			self::CapacitySchedule->value => 'Cronograma de Capacidade',
			self::WorkSchedule->value => 'Cronograma de Trabalho',
			self::ShiftSchedule->value => 'Cronograma de Turnos',
			self::RotationSchedule->value => 'Cronograma de Rotação',

			// Event-based
			self::EventDrivenSchedule->value => 'Cronograma Baseado em Eventos',
			self::TriggerBasedSchedule->value => 'Cronograma Baseado em Gatilhos',
			self::DependencySchedule->value => 'Cronograma de Dependências',
			self::ConditionalSchedule->value => 'Cronograma Condicional',

			// Calendar
			self::CalendarSchedule->value => 'Cronograma de Calendário',
			self::TimeBlockSchedule->value => 'Cronograma de Blocos de Tempo',
			self::AppointmentSchedule->value => 'Cronograma de Compromissos',
			self::MeetingSchedule->value => 'Cronograma de Reuniões',

			// Production/Operations
			self::ProductionSchedule->value => 'Cronograma de Produção',
			self::ManufacturingSchedule->value => 'Cronograma de Manufatura',
			self::MaintenanceSchedule->value => 'Cronograma de Manutenção',
			self::InventorySchedule->value => 'Cronograma de Estoque',
			self::DeliverySchedule->value => 'Cronograma de Entrega',

			// Financial/Business
			self::BudgetSchedule->value => 'Cronograma Orçamentário',
			self::FinancialSchedule->value => 'Cronograma Financeiro',
			self::BillingSchedule->value => 'Cronograma de Cobrança',
			self::ReportingSchedule->value => 'Cronograma de Relatórios',
			self::TaxSchedule->value => 'Cronograma Fiscal',

			// Education/Training
			self::AcademicSchedule->value => 'Cronograma Acadêmico',
			self::TrainingSchedule->value => 'Cronograma de Treinamento',
			self::CourseSchedule->value => 'Cronograma de Cursos',
			self::ExamSchedule->value => 'Cronograma de Exames',
			self::SessionSchedule->value => 'Cronograma de Sessões',

			// Marketing/Sales
			self::MarketingSchedule->value => 'Cronograma de Marketing',
			self::CampaignSchedule->value => 'Cronograma de Campanha',
			self::ContentSchedule->value => 'Cronograma de Conteúdo',
			self::SocialMediaSchedule->value => 'Cronograma de Mídia Social',
			self::SalesSchedule->value => 'Cronograma de Vendas',

			// Development/Technical
			self::DevelopmentSchedule->value => 'Cronograma de Desenvolvimento',
			self::ReleaseSchedule->value => 'Cronograma de Lançamento',
			self::DeploymentSchedule->value => 'Cronograma de Implantação',
			self::TestingSchedule->value => 'Cronograma de Testes',
			self::SprintSchedule->value => 'Cronograma de Sprint',

			// Personal/Life
			self::PersonalSchedule->value => 'Cronograma Pessoal',
			self::FamilySchedule->value => 'Cronograma Familiar',
			self::HealthSchedule->value => 'Cronograma de Saúde',
			self::FitnessSchedule->value => 'Cronograma de Fitness',
			self::TravelSchedule->value => 'Cronograma de Viagem',

			// Other
			self::AdHocSchedule->value => 'Cronograma Ad Hoc',
			self::DynamicSchedule->value => 'Cronograma Dinâmico',
			self::ManualSchedule->value => 'Cronograma Manual',
			self::AutomatedSchedule->value => 'Cronograma Automatizado',
			self::TemplateSchedule->value => 'Cronograma de Modelo',
			self::Other->value => 'Outro',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'Programa Fijo',
			self::FlexibleSchedule->value => 'Programa Flexible',
			self::RollingSchedule->value => 'Programa Continuo',
			self::ContinuousSchedule->value => 'Programa Continuo',
			self::CyclicSchedule->value => 'Programa Cíclico',

			// Project/Work
			self::ProjectSchedule->value => 'Programa de Proyecto',
			self::MilestoneSchedule->value => 'Programa de Hitos',
			self::PhaseBasedSchedule->value => 'Programa por Fases',
			self::IterativeSchedule->value => 'Programa Iterativo',
			self::AgileSchedule->value => 'Programa Ágil',
			self::WaterfallSchedule->value => 'Programa en Cascada',

			// Resource
			self::ResourceSchedule->value => 'Programa de Recursos',
			self::CapacitySchedule->value => 'Programa de Capacidad',
			self::WorkSchedule->value => 'Programa de Trabajo',
			self::ShiftSchedule->value => 'Programa de Turnos',
			self::RotationSchedule->value => 'Programa de Rotación',

			// Event-based
			self::EventDrivenSchedule->value => 'Programa Basado en Eventos',
			self::TriggerBasedSchedule->value => 'Programa Basado en Disparadores',
			self::DependencySchedule->value => 'Programa de Dependencias',
			self::ConditionalSchedule->value => 'Programa Condicional',

			// Calendar
			self::CalendarSchedule->value => 'Programa de Calendario',
			self::TimeBlockSchedule->value => 'Programa de Bloques de Tiempo',
			self::AppointmentSchedule->value => 'Programa de Citas',
			self::MeetingSchedule->value => 'Programa de Reuniones',

			// Production/Operations
			self::ProductionSchedule->value => 'Programa de Producción',
			self::ManufacturingSchedule->value => 'Programa de Manufactura',
			self::MaintenanceSchedule->value => 'Programa de Mantenimiento',
			self::InventorySchedule->value => 'Programa de Inventario',
			self::DeliverySchedule->value => 'Programa de Entrega',

			// Financial/Business
			self::BudgetSchedule->value => 'Programa Presupuestario',
			self::FinancialSchedule->value => 'Programa Financiero',
			self::BillingSchedule->value => 'Programa de Facturación',
			self::ReportingSchedule->value => 'Programa de Informes',
			self::TaxSchedule->value => 'Programa Fiscal',

			// Education/Training
			self::AcademicSchedule->value => 'Programa Académico',
			self::TrainingSchedule->value => 'Programa de Capacitación',
			self::CourseSchedule->value => 'Programa de Cursos',
			self::ExamSchedule->value => 'Programa de Exámenes',
			self::SessionSchedule->value => 'Programa de Sesiones',

			// Marketing/Sales
			self::MarketingSchedule->value => 'Programa de Marketing',
			self::CampaignSchedule->value => 'Programa de Campaña',
			self::ContentSchedule->value => 'Programa de Contenido',
			self::SocialMediaSchedule->value => 'Programa de Redes Sociales',
			self::SalesSchedule->value => 'Programa de Ventas',

			// Development/Technical
			self::DevelopmentSchedule->value => 'Programa de Desarrollo',
			self::ReleaseSchedule->value => 'Programa de Lanzamiento',
			self::DeploymentSchedule->value => 'Programa de Implementación',
			self::TestingSchedule->value => 'Programa de Pruebas',
			self::SprintSchedule->value => 'Programa de Sprint',

			// Personal/Life
			self::PersonalSchedule->value => 'Programa Personal',
			self::FamilySchedule->value => 'Programa Familiar',
			self::HealthSchedule->value => 'Programa de Salud',
			self::FitnessSchedule->value => 'Programa de Fitness',
			self::TravelSchedule->value => 'Programa de Viaje',

			// Other
			self::AdHocSchedule->value => 'Programa Ad Hoc',
			self::DynamicSchedule->value => 'Programa Dinámico',
			self::ManualSchedule->value => 'Programa Manual',
			self::AutomatedSchedule->value => 'Programa Automatizado',
			self::TemplateSchedule->value => 'Programa de Plantilla',
			self::Other->value => 'Otro',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'Calendrier Fixe',
			self::FlexibleSchedule->value => 'Calendrier Flexible',
			self::RollingSchedule->value => 'Calendrier Glissant',
			self::ContinuousSchedule->value => 'Calendrier Continu',
			self::CyclicSchedule->value => 'Calendrier Cyclique',

			// Project/Work
			self::ProjectSchedule->value => 'Calendrier de Projet',
			self::MilestoneSchedule->value => 'Calendrier des Jalons',
			self::PhaseBasedSchedule->value => 'Calendrier par Phases',
			self::IterativeSchedule->value => 'Calendrier Itératif',
			self::AgileSchedule->value => 'Calendrier Agile',
			self::WaterfallSchedule->value => 'Calendrier en Cascade',

			// Resource
			self::ResourceSchedule->value => 'Calendrier des Ressources',
			self::CapacitySchedule->value => 'Calendrier de Capacité',
			self::WorkSchedule->value => 'Calendrier de Travail',
			self::ShiftSchedule->value => 'Calendrier des Shifts',
			self::RotationSchedule->value => 'Calendrier de Rotation',

			// Event-based
			self::EventDrivenSchedule->value => 'Calendrier Basé sur les Événements',
			self::TriggerBasedSchedule->value => 'Calendrier Basé sur les Déclencheurs',
			self::DependencySchedule->value => 'Calendrier des Dépendances',
			self::ConditionalSchedule->value => 'Calendrier Conditionnel',

			// Calendar
			self::CalendarSchedule->value => 'Calendrier',
			self::TimeBlockSchedule->value => 'Calendrier par Blocs de Temps',
			self::AppointmentSchedule->value => 'Calendrier de Rendez-vous',
			self::MeetingSchedule->value => 'Calendrier des Réunions',

			// Production/Operations
			self::ProductionSchedule->value => 'Calendrier de Production',
			self::ManufacturingSchedule->value => 'Calendrier de Fabrication',
			self::MaintenanceSchedule->value => 'Calendrier de Maintenance',
			self::InventorySchedule->value => 'Calendrier des Stocks',
			self::DeliverySchedule->value => 'Calendrier de Livraison',

			// Financial/Business
			self::BudgetSchedule->value => 'Calendrier Budgétaire',
			self::FinancialSchedule->value => 'Calendrier Financier',
			self::BillingSchedule->value => 'Calendrier de Facturation',
			self::ReportingSchedule->value => 'Calendrier des Rapports',
			self::TaxSchedule->value => 'Calendrier Fiscal',

			// Education/Training
			self::AcademicSchedule->value => 'Calendrier Académique',
			self::TrainingSchedule->value => 'Calendrier de Formation',
			self::CourseSchedule->value => 'Calendrier des Cours',
			self::ExamSchedule->value => 'Calendrier des Examens',
			self::SessionSchedule->value => 'Calendrier des Sessions',

			// Marketing/Sales
			self::MarketingSchedule->value => 'Calendrier Marketing',
			self::CampaignSchedule->value => 'Calendrier de Campagne',
			self::ContentSchedule->value => 'Calendrier de Contenu',
			self::SocialMediaSchedule->value => 'Calendrier des Médias Sociaux',
			self::SalesSchedule->value => 'Calendrier des Ventes',

			// Development/Technical
			self::DevelopmentSchedule->value => 'Calendrier de Développement',
			self::ReleaseSchedule->value => 'Calendrier de Sortie',
			self::DeploymentSchedule->value => 'Calendrier de Déploiement',
			self::TestingSchedule->value => 'Calendrier de Tests',
			self::SprintSchedule->value => 'Calendrier des Sprints',

			// Personal/Life
			self::PersonalSchedule->value => 'Calendrier Personnel',
			self::FamilySchedule->value => 'Calendrier Familial',
			self::HealthSchedule->value => 'Calendrier de Santé',
			self::FitnessSchedule->value => 'Calendrier de Fitness',
			self::TravelSchedule->value => 'Calendrier de Voyage',

			// Other
			self::AdHocSchedule->value => 'Calendrier Ad Hoc',
			self::DynamicSchedule->value => 'Calendrier Dynamique',
			self::ManualSchedule->value => 'Calendrier Manuel',
			self::AutomatedSchedule->value => 'Calendrier Automatisé',
			self::TemplateSchedule->value => 'Calendrier Modèle',
			self::Other->value => 'Autre',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'Fester Zeitplan',
			self::FlexibleSchedule->value => 'Flexibler Zeitplan',
			self::RollingSchedule->value => 'Rollender Zeitplan',
			self::ContinuousSchedule->value => 'Kontinuierlicher Zeitplan',
			self::CyclicSchedule->value => 'Zyklischer Zeitplan',

			// Project/Work
			self::ProjectSchedule->value => 'Projektzeitplan',
			self::MilestoneSchedule->value => 'Meilenstein-Zeitplan',
			self::PhaseBasedSchedule->value => 'Phasenbasierter Zeitplan',
			self::IterativeSchedule->value => 'Iterativer Zeitplan',
			self::AgileSchedule->value => 'Agiler Zeitplan',
			self::WaterfallSchedule->value => 'Wasserfall-Zeitplan',

			// Resource
			self::ResourceSchedule->value => 'Ressourcen-Zeitplan',
			self::CapacitySchedule->value => 'Kapazitäts-Zeitplan',
			self::WorkSchedule->value => 'Arbeitszeitplan',
			self::ShiftSchedule->value => 'Schichtplan',
			self::RotationSchedule->value => 'Rotationsplan',

			// Event-based
			self::EventDrivenSchedule->value => 'Ereignisgesteuerter Zeitplan',
			self::TriggerBasedSchedule->value => 'Triggerbasierter Zeitplan',
			self::DependencySchedule->value => 'Abhängigkeits-Zeitplan',
			self::ConditionalSchedule->value => 'Bedingter Zeitplan',

			// Calendar
			self::CalendarSchedule->value => 'Kalenderzeitplan',
			self::TimeBlockSchedule->value => 'Zeitblock-Planung',
			self::AppointmentSchedule->value => 'Terminplan',
			self::MeetingSchedule->value => 'Besprechungsplan',

			// Production/Operations
			self::ProductionSchedule->value => 'Produktionsplan',
			self::ManufacturingSchedule->value => 'Fertigungsplan',
			self::MaintenanceSchedule->value => 'Wartungsplan',
			self::InventorySchedule->value => 'Inventarplan',
			self::DeliverySchedule->value => 'Lieferplan',

			// Financial/Business
			self::BudgetSchedule->value => 'Budgetplan',
			self::FinancialSchedule->value => 'Finanzplan',
			self::BillingSchedule->value => 'Abrechnungsplan',
			self::ReportingSchedule->value => 'Berichtsplan',
			self::TaxSchedule->value => 'Steuerplan',

			// Education/Training
			self::AcademicSchedule->value => 'Akademischer Zeitplan',
			self::TrainingSchedule->value => 'Schulungsplan',
			self::CourseSchedule->value => 'Kursplan',
			self::ExamSchedule->value => 'Prüfungsplan',
			self::SessionSchedule->value => 'Sitzungsplan',

			// Marketing/Sales
			self::MarketingSchedule->value => 'Marketingplan',
			self::CampaignSchedule->value => 'Kampagnenplan',
			self::ContentSchedule->value => 'Content-Plan',
			self::SocialMediaSchedule->value => 'Social-Media-Plan',
			self::SalesSchedule->value => 'Verkaufsplan',

			// Development/Technical
			self::DevelopmentSchedule->value => 'Entwicklungsplan',
			self::ReleaseSchedule->value => 'Release-Plan',
			self::DeploymentSchedule->value => 'Bereitstellungsplan',
			self::TestingSchedule->value => 'Testplan',
			self::SprintSchedule->value => 'Sprint-Plan',

			// Personal/Life
			self::PersonalSchedule->value => 'Persönlicher Zeitplan',
			self::FamilySchedule->value => 'Familienzeitplan',
			self::HealthSchedule->value => 'Gesundheitsplan',
			self::FitnessSchedule->value => 'Fitnessplan',
			self::TravelSchedule->value => 'Reiseplan',

			// Other
			self::AdHocSchedule->value => 'Ad-hoc-Zeitplan',
			self::DynamicSchedule->value => 'Dynamischer Zeitplan',
			self::ManualSchedule->value => 'Manueller Zeitplan',
			self::AutomatedSchedule->value => 'Automatisierter Zeitplan',
			self::TemplateSchedule->value => 'Vorlagen-Zeitplan',
			self::Other->value => 'Andere',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'Programma Fisso',
			self::FlexibleSchedule->value => 'Programma Flessibile',
			self::RollingSchedule->value => 'Programma Continuo',
			self::ContinuousSchedule->value => 'Programma Continuo',
			self::CyclicSchedule->value => 'Programma Ciclico',

			// Project/Work
			self::ProjectSchedule->value => 'Programma di Progetto',
			self::MilestoneSchedule->value => 'Programma delle Milestone',
			self::PhaseBasedSchedule->value => 'Programma per Fasi',
			self::IterativeSchedule->value => 'Programma Iterativo',
			self::AgileSchedule->value => 'Programma Agile',
			self::WaterfallSchedule->value => 'Programma a Cascata',

			// Resource
			self::ResourceSchedule->value => 'Programma delle Risorse',
			self::CapacitySchedule->value => 'Programma della Capacità',
			self::WorkSchedule->value => 'Programma di Lavoro',
			self::ShiftSchedule->value => 'Programma dei Turni',
			self::RotationSchedule->value => 'Programma di Rotazione',

			// Event-based
			self::EventDrivenSchedule->value => 'Programma Basato su Eventi',
			self::TriggerBasedSchedule->value => 'Programma Basato su Trigger',
			self::DependencySchedule->value => 'Programma delle Dipendenze',
			self::ConditionalSchedule->value => 'Programma Condizionale',

			// Calendar
			self::CalendarSchedule->value => 'Programma del Calendario',
			self::TimeBlockSchedule->value => 'Programma a Blocchi di Tempo',
			self::AppointmentSchedule->value => 'Programma degli Appuntamenti',
			self::MeetingSchedule->value => 'Programma delle Riunioni',

			// Production/Operations
			self::ProductionSchedule->value => 'Programma di Produzione',
			self::ManufacturingSchedule->value => 'Programma di Produzione',
			self::MaintenanceSchedule->value => 'Programma di Manutenzione',
			self::InventorySchedule->value => 'Programma delle Scorte',
			self::DeliverySchedule->value => 'Programma delle Consegne',

			// Financial/Business
			self::BudgetSchedule->value => 'Programma di Budget',
			self::FinancialSchedule->value => 'Programma Finanziario',
			self::BillingSchedule->value => 'Programma di Fatturazione',
			self::ReportingSchedule->value => 'Programma dei Report',
			self::TaxSchedule->value => 'Programma Fiscale',

			// Education/Training
			self::AcademicSchedule->value => 'Programma Accademico',
			self::TrainingSchedule->value => 'Programma di Formazione',
			self::CourseSchedule->value => 'Programma dei Corsi',
			self::ExamSchedule->value => 'Programma degli Esami',
			self::SessionSchedule->value => 'Programma delle Sessioni',

			// Marketing/Sales
			self::MarketingSchedule->value => 'Programma di Marketing',
			self::CampaignSchedule->value => 'Programma della Campagna',
			self::ContentSchedule->value => 'Programma dei Contenuti',
			self::SocialMediaSchedule->value => 'Programma dei Social Media',
			self::SalesSchedule->value => 'Programma delle Vendite',

			// Development/Technical
			self::DevelopmentSchedule->value => 'Programma di Sviluppo',
			self::ReleaseSchedule->value => 'Programma di Rilascio',
			self::DeploymentSchedule->value => 'Programma di Implementazione',
			self::TestingSchedule->value => 'Programma di Test',
			self::SprintSchedule->value => 'Programma degli Sprint',

			// Personal/Life
			self::PersonalSchedule->value => 'Programma Personale',
			self::FamilySchedule->value => 'Programma Familiare',
			self::HealthSchedule->value => 'Programma della Salute',
			self::FitnessSchedule->value => 'Programma del Fitness',
			self::TravelSchedule->value => 'Programma di Viaggio',

			// Other
			self::AdHocSchedule->value => 'Programma Ad Hoc',
			self::DynamicSchedule->value => 'Programma Dinamico',
			self::ManualSchedule->value => 'Programma Manuale',
			self::AutomatedSchedule->value => 'Programma Automatizzato',
			self::TemplateSchedule->value => 'Programma Modello',
			self::Other->value => 'Altro',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'Vast Schema',
			self::FlexibleSchedule->value => 'Flexibel Schema',
			self::RollingSchedule->value => 'Rollend Schema',
			self::ContinuousSchedule->value => 'Doorlopend Schema',
			self::CyclicSchedule->value => 'Cyclisch Schema',

			// Project/Work
			self::ProjectSchedule->value => 'Project Schema',
			self::MilestoneSchedule->value => 'Mijlpaal Schema',
			self::PhaseBasedSchedule->value => 'Fasegebaseerd Schema',
			self::IterativeSchedule->value => 'Iteratief Schema',
			self::AgileSchedule->value => 'Agile Schema',
			self::WaterfallSchedule->value => 'Waterval Schema',

			// Resource
			self::ResourceSchedule->value => 'Resource Schema',
			self::CapacitySchedule->value => 'Capaciteit Schema',
			self::WorkSchedule->value => 'Werk Schema',
			self::ShiftSchedule->value => 'Dienstrooster',
			self::RotationSchedule->value => 'Rotatie Schema',

			// Event-based
			self::EventDrivenSchedule->value => 'Gebeurtenis Gestuurd Schema',
			self::TriggerBasedSchedule->value => 'Trigger Gebaseerd Schema',
			self::DependencySchedule->value => 'Afhankelijkheid Schema',
			self::ConditionalSchedule->value => 'Voorwaardelijk Schema',

			// Calendar
			self::CalendarSchedule->value => 'Kalender Schema',
			self::TimeBlockSchedule->value => 'Tijdblok Schema',
			self::AppointmentSchedule->value => 'Afspraken Schema',
			self::MeetingSchedule->value => 'Vergader Schema',

			// Production/Operations
			self::ProductionSchedule->value => 'Productie Schema',
			self::ManufacturingSchedule->value => 'Productie Schema',
			self::MaintenanceSchedule->value => 'Onderhoud Schema',
			self::InventorySchedule->value => 'Voorraad Schema',
			self::DeliverySchedule->value => 'Levering Schema',

			// Financial/Business
			self::BudgetSchedule->value => 'Budget Schema',
			self::FinancialSchedule->value => 'Financieel Schema',
			self::BillingSchedule->value => 'Facturatie Schema',
			self::ReportingSchedule->value => 'Rapportage Schema',
			self::TaxSchedule->value => 'Belasting Schema',

			// Education/Training
			self::AcademicSchedule->value => 'Academisch Schema',
			self::TrainingSchedule->value => 'Training Schema',
			self::CourseSchedule->value => 'Cursus Schema',
			self::ExamSchedule->value => 'Examen Schema',
			self::SessionSchedule->value => 'Sessie Schema',

			// Marketing/Sales
			self::MarketingSchedule->value => 'Marketing Schema',
			self::CampaignSchedule->value => 'Campagne Schema',
			self::ContentSchedule->value => 'Content Schema',
			self::SocialMediaSchedule->value => 'Social Media Schema',
			self::SalesSchedule->value => 'Verkoop Schema',

			// Development/Technical
			self::DevelopmentSchedule->value => 'Ontwikkeling Schema',
			self::ReleaseSchedule->value => 'Release Schema',
			self::DeploymentSchedule->value => 'Implementatie Schema',
			self::TestingSchedule->value => 'Test Schema',
			self::SprintSchedule->value => 'Sprint Schema',

			// Personal/Life
			self::PersonalSchedule->value => 'Persoonlijk Schema',
			self::FamilySchedule->value => 'Familie Schema',
			self::HealthSchedule->value => 'Gezondheid Schema',
			self::FitnessSchedule->value => 'Fitness Schema',
			self::TravelSchedule->value => 'Reis Schema',

			// Other
			self::AdHocSchedule->value => 'Ad Hoc Schema',
			self::DynamicSchedule->value => 'Dynamisch Schema',
			self::ManualSchedule->value => 'Handmatig Schema',
			self::AutomatedSchedule->value => 'Geautomatiseerd Schema',
			self::TemplateSchedule->value => 'Sjabloon Schema',
			self::Other->value => 'Anders',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'Stały Harmonogram',
			self::FlexibleSchedule->value => 'Elastyczny Harmonogram',
			self::RollingSchedule->value => 'Harmonogram Przesuwny',
			self::ContinuousSchedule->value => 'Ciągły Harmonogram',
			self::CyclicSchedule->value => 'Cykliczny Harmonogram',

			// Project/Work
			self::ProjectSchedule->value => 'Harmonogram Projektu',
			self::MilestoneSchedule->value => 'Harmonogram Kamieni Milowych',
			self::PhaseBasedSchedule->value => 'Harmonogram Fazowy',
			self::IterativeSchedule->value => 'Harmonogram Iteracyjny',
			self::AgileSchedule->value => 'Harmonogram Agile',
			self::WaterfallSchedule->value => 'Harmonogram Kaskadowy',

			// Resource
			self::ResourceSchedule->value => 'Harmonogram Zasobów',
			self::CapacitySchedule->value => 'Harmonogram Pojemności',
			self::WorkSchedule->value => 'Harmonogram Pracy',
			self::ShiftSchedule->value => 'Harmonogram Zmian',
			self::RotationSchedule->value => 'Harmonogram Rotacji',

			// Event-based
			self::EventDrivenSchedule->value => 'Harmonogram Zdarzeniowy',
			self::TriggerBasedSchedule->value => 'Harmonogram Wyzwalany',
			self::DependencySchedule->value => 'Harmonogram Zależności',
			self::ConditionalSchedule->value => 'Harmonogram Warunkowy',

			// Calendar
			self::CalendarSchedule->value => 'Harmonogram Kalendarza',
			self::TimeBlockSchedule->value => 'Harmonogram Bloków Czasowych',
			self::AppointmentSchedule->value => 'Harmonogram Spotkań',
			self::MeetingSchedule->value => 'Harmonogram Zebrań',

			// Production/Operations
			self::ProductionSchedule->value => 'Harmonogram Produkcji',
			self::ManufacturingSchedule->value => 'Harmonogram Wytwarzania',
			self::MaintenanceSchedule->value => 'Harmonogram Konserwacji',
			self::InventorySchedule->value => 'Harmonogram Zapasów',
			self::DeliverySchedule->value => 'Harmonogram Dostaw',

			// Financial/Business
			self::BudgetSchedule->value => 'Harmonogram Budżetowy',
			self::FinancialSchedule->value => 'Harmonogram Finansowy',
			self::BillingSchedule->value => 'Harmonogram Fakturowania',
			self::ReportingSchedule->value => 'Harmonogram Raportowania',
			self::TaxSchedule->value => 'Harmonogram Podatkowy',

			// Education/Training
			self::AcademicSchedule->value => 'Harmonogram Akademicki',
			self::TrainingSchedule->value => 'Harmonogram Szkoleniowy',
			self::CourseSchedule->value => 'Harmonogram Kursów',
			self::ExamSchedule->value => 'Harmonogram Egzaminów',
			self::SessionSchedule->value => 'Harmonogram Sesji',

			// Marketing/Sales
			self::MarketingSchedule->value => 'Harmonogram Marketingu',
			self::CampaignSchedule->value => 'Harmonogram Kampanii',
			self::ContentSchedule->value => 'Harmonogram Treści',
			self::SocialMediaSchedule->value => 'Harmonogram Mediów Społecznościowych',
			self::SalesSchedule->value => 'Harmonogram Sprzedaży',

			// Development/Technical
			self::DevelopmentSchedule->value => 'Harmonogram Rozwoju',
			self::ReleaseSchedule->value => 'Harmonogram Wydań',
			self::DeploymentSchedule->value => 'Harmonogram Wdrożenia',
			self::TestingSchedule->value => 'Harmonogram Testów',
			self::SprintSchedule->value => 'Harmonogram Sprintów',

			// Personal/Life
			self::PersonalSchedule->value => 'Harmonogram Osobisty',
			self::FamilySchedule->value => 'Harmonogram Rodzinny',
			self::HealthSchedule->value => 'Harmonogram Zdrowia',
			self::FitnessSchedule->value => 'Harmonogram Fitness',
			self::TravelSchedule->value => 'Harmonogram Podróży',

			// Other
			self::AdHocSchedule->value => 'Harmonogram Ad Hoc',
			self::DynamicSchedule->value => 'Harmonogram Dynamiczny',
			self::ManualSchedule->value => 'Harmonogram Ręczny',
			self::AutomatedSchedule->value => 'Harmonogram Automatyczny',
			self::TemplateSchedule->value => 'Harmonogram Szablonowy',
			self::Other->value => 'Inny',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'Фиксированное Расписание',
			self::FlexibleSchedule->value => 'Гибкое Расписание',
			self::RollingSchedule->value => 'Скользящее Расписание',
			self::ContinuousSchedule->value => 'Непрерывное Расписание',
			self::CyclicSchedule->value => 'Циклическое Расписание',

			// Project/Work
			self::ProjectSchedule->value => 'Расписание Проекта',
			self::MilestoneSchedule->value => 'Расписание Вех',
			self::PhaseBasedSchedule->value => 'Расписание по Этапам',
			self::IterativeSchedule->value => 'Итеративное Расписание',
			self::AgileSchedule->value => 'Agile Расписание',
			self::WaterfallSchedule->value => 'Каскадное Расписание',

			// Resource
			self::ResourceSchedule->value => 'Расписание Ресурсов',
			self::CapacitySchedule->value => 'Расписание Мощностей',
			self::WorkSchedule->value => 'Рабочее Расписание',
			self::ShiftSchedule->value => 'Сменное Расписание',
			self::RotationSchedule->value => 'Расписание Ротации',

			// Event-based
			self::EventDrivenSchedule->value => 'Событийное Расписание',
			self::TriggerBasedSchedule->value => 'Триггерное Расписание',
			self::DependencySchedule->value => 'Расписание Зависимостей',
			self::ConditionalSchedule->value => 'Условное Расписание',

			// Calendar
			self::CalendarSchedule->value => 'Календарное Расписание',
			self::TimeBlockSchedule->value => 'Расписание по Блокам Времени',
			self::AppointmentSchedule->value => 'Расписание Встреч',
			self::MeetingSchedule->value => 'Расписание Совещаний',

			// Production/Operations
			self::ProductionSchedule->value => 'Производственное Расписание',
			self::ManufacturingSchedule->value => 'Расписание Производства',
			self::MaintenanceSchedule->value => 'Расписание Техобслуживания',
			self::InventorySchedule->value => 'Расписание Инвентаризации',
			self::DeliverySchedule->value => 'Расписание Доставки',

			// Financial/Business
			self::BudgetSchedule->value => 'Бюджетное Расписание',
			self::FinancialSchedule->value => 'Финансовое Расписание',
			self::BillingSchedule->value => 'Расписание Выставления Счетов',
			self::ReportingSchedule->value => 'Расписание Отчетности',
			self::TaxSchedule->value => 'Налоговое Расписание',

			// Education/Training
			self::AcademicSchedule->value => 'Академическое Расписание',
			self::TrainingSchedule->value => 'Расписание Обучения',
			self::CourseSchedule->value => 'Расписание Курсов',
			self::ExamSchedule->value => 'Расписание Экзаменов',
			self::SessionSchedule->value => 'Расписание Сессий',

			// Marketing/Sales
			self::MarketingSchedule->value => 'Маркетинговое Расписание',
			self::CampaignSchedule->value => 'Расписание Кампании',
			self::ContentSchedule->value => 'Расписание Контента',
			self::SocialMediaSchedule->value => 'Расписание Соцсетей',
			self::SalesSchedule->value => 'Расписание Продаж',

			// Development/Technical
			self::DevelopmentSchedule->value => 'Расписание Разработки',
			self::ReleaseSchedule->value => 'Расписание Релизов',
			self::DeploymentSchedule->value => 'Расписание Развертывания',
			self::TestingSchedule->value => 'Расписание Тестирования',
			self::SprintSchedule->value => 'Расписание Спринтов',

			// Personal/Life
			self::PersonalSchedule->value => 'Личное Расписание',
			self::FamilySchedule->value => 'Семейное Расписание',
			self::HealthSchedule->value => 'Расписание Здоровья',
			self::FitnessSchedule->value => 'Расписание Фитнеса',
			self::TravelSchedule->value => 'Расписание Путешествий',

			// Other
			self::AdHocSchedule->value => 'Ad Hoc Расписание',
			self::DynamicSchedule->value => 'Динамическое Расписание',
			self::ManualSchedule->value => 'Ручное Расписание',
			self::AutomatedSchedule->value => 'Автоматизированное Расписание',
			self::TemplateSchedule->value => 'Расписание по Шаблону',
			self::Other->value => 'Другое',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'Sabit Çizelge',
			self::FlexibleSchedule->value => 'Esnek Çizelge',
			self::RollingSchedule->value => 'Kayan Çizelge',
			self::ContinuousSchedule->value => 'Sürekli Çizelge',
			self::CyclicSchedule->value => 'Döngüsel Çizelge',

			// Project/Work
			self::ProjectSchedule->value => 'Proje Çizelgesi',
			self::MilestoneSchedule->value => 'Kilometre Taşı Çizelgesi',
			self::PhaseBasedSchedule->value => 'Faz Bazlı Çizelge',
			self::IterativeSchedule->value => 'Yinelemeli Çizelge',
			self::AgileSchedule->value => 'Çevik Çizelge',
			self::WaterfallSchedule->value => 'Şelale Çizelge',

			// Resource
			self::ResourceSchedule->value => 'Kaynak Çizelgesi',
			self::CapacitySchedule->value => 'Kapasite Çizelgesi',
			self::WorkSchedule->value => 'Çalışma Çizelgesi',
			self::ShiftSchedule->value => 'Vardiya Çizelgesi',
			self::RotationSchedule->value => 'Rotasyon Çizelgesi',

			// Event-based
			self::EventDrivenSchedule->value => 'Olay Tabanlı Çizelge',
			self::TriggerBasedSchedule->value => 'Tetikleyici Tabanlı Çizelge',
			self::DependencySchedule->value => 'Bağımlılık Çizelgesi',
			self::ConditionalSchedule->value => 'Koşullu Çizelge',

			// Calendar
			self::CalendarSchedule->value => 'Takvim Çizelgesi',
			self::TimeBlockSchedule->value => 'Zaman Blok Çizelgesi',
			self::AppointmentSchedule->value => 'Randevu Çizelgesi',
			self::MeetingSchedule->value => 'Toplantı Çizelgesi',

			// Production/Operations
			self::ProductionSchedule->value => 'Üretim Çizelgesi',
			self::ManufacturingSchedule->value => 'Üretim Çizelgesi',
			self::MaintenanceSchedule->value => 'Bakım Çizelgesi',
			self::InventorySchedule->value => 'Envanter Çizelgesi',
			self::DeliverySchedule->value => 'Teslimat Çizelgesi',

			// Financial/Business
			self::BudgetSchedule->value => 'Bütçe Çizelgesi',
			self::FinancialSchedule->value => 'Finansal Çizelge',
			self::BillingSchedule->value => 'Faturalama Çizelgesi',
			self::ReportingSchedule->value => 'Raporlama Çizelgesi',
			self::TaxSchedule->value => 'Vergi Çizelgesi',

			// Education/Training
			self::AcademicSchedule->value => 'Akademik Çizelge',
			self::TrainingSchedule->value => 'Eğitim Çizelgesi',
			self::CourseSchedule->value => 'Kurs Çizelgesi',
			self::ExamSchedule->value => 'Sınav Çizelgesi',
			self::SessionSchedule->value => 'Oturum Çizelgesi',

			// Marketing/Sales
			self::MarketingSchedule->value => 'Pazarlama Çizelgesi',
			self::CampaignSchedule->value => 'Kampanya Çizelgesi',
			self::ContentSchedule->value => 'İçerik Çizelgesi',
			self::SocialMediaSchedule->value => 'Sosyal Medya Çizelgesi',
			self::SalesSchedule->value => 'Satış Çizelgesi',

			// Development/Technical
			self::DevelopmentSchedule->value => 'Geliştirme Çizelgesi',
			self::ReleaseSchedule->value => 'Yayın Çizelgesi',
			self::DeploymentSchedule->value => 'Dağıtım Çizelgesi',
			self::TestingSchedule->value => 'Test Çizelgesi',
			self::SprintSchedule->value => 'Sprint Çizelgesi',

			// Personal/Life
			self::PersonalSchedule->value => 'Kişisel Çizelge',
			self::FamilySchedule->value => 'Aile Çizelgesi',
			self::HealthSchedule->value => 'Sağlık Çizelgesi',
			self::FitnessSchedule->value => 'Fitness Çizelgesi',
			self::TravelSchedule->value => 'Seyahat Çizelgesi',

			// Other
			self::AdHocSchedule->value => 'Ad Hoc Çizelge',
			self::DynamicSchedule->value => 'Dinamik Çizelge',
			self::ManualSchedule->value => 'Manuel Çizelge',
			self::AutomatedSchedule->value => 'Otomatik Çizelge',
			self::TemplateSchedule->value => 'Şablon Çizelge',
			self::Other->value => 'Diğer',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'جدول ثابت',
			self::FlexibleSchedule->value => 'جدول مرن',
			self::RollingSchedule->value => 'جدول متداول',
			self::ContinuousSchedule->value => 'جدول مستمر',
			self::CyclicSchedule->value => 'جدول دوري',

			// Project/Work
			self::ProjectSchedule->value => 'جدول المشروع',
			self::MilestoneSchedule->value => 'جدول المعالم',
			self::PhaseBasedSchedule->value => 'جدول مرحلي',
			self::IterativeSchedule->value => 'جدول تكراري',
			self::AgileSchedule->value => 'جدول أجايل',
			self::WaterfallSchedule->value => 'جدول الشلال',

			// Resource
			self::ResourceSchedule->value => 'جدول الموارد',
			self::CapacitySchedule->value => 'جدول السعة',
			self::WorkSchedule->value => 'جدول العمل',
			self::ShiftSchedule->value => 'جدول الورديات',
			self::RotationSchedule->value => 'جدول الدوران',

			// Event-based
			self::EventDrivenSchedule->value => 'جدول قائم على الأحداث',
			self::TriggerBasedSchedule->value => 'جدول قائم على المحفزات',
			self::DependencySchedule->value => 'جدول التبعيات',
			self::ConditionalSchedule->value => 'جدول شرطي',

			// Calendar
			self::CalendarSchedule->value => 'جدول التقويم',
			self::TimeBlockSchedule->value => 'جدول الكتل الزمنية',
			self::AppointmentSchedule->value => 'جدول المواعيد',
			self::MeetingSchedule->value => 'جدول الاجتماعات',

			// Production/Operations
			self::ProductionSchedule->value => 'جدول الإنتاج',
			self::ManufacturingSchedule->value => 'جدول التصنيع',
			self::MaintenanceSchedule->value => 'جدول الصيانة',
			self::InventorySchedule->value => 'جدول المخزون',
			self::DeliverySchedule->value => 'جدول التسليم',

			// Financial/Business
			self::BudgetSchedule->value => 'جدول الميزانية',
			self::FinancialSchedule->value => 'جدول مالي',
			self::BillingSchedule->value => 'جدول الفواتير',
			self::ReportingSchedule->value => 'جدول التقارير',
			self::TaxSchedule->value => 'جدول الضرائب',

			// Education/Training
			self::AcademicSchedule->value => 'جدول أكاديمي',
			self::TrainingSchedule->value => 'جدول التدريب',
			self::CourseSchedule->value => 'جدول الدورات',
			self::ExamSchedule->value => 'جدول الامتحانات',
			self::SessionSchedule->value => 'جدول الجلسات',

			// Marketing/Sales
			self::MarketingSchedule->value => 'جدول التسويق',
			self::CampaignSchedule->value => 'جدول الحملة',
			self::ContentSchedule->value => 'جدول المحتوى',
			self::SocialMediaSchedule->value => 'جدول وسائل التواصل الاجتماعي',
			self::SalesSchedule->value => 'جدول المبيعات',

			// Development/Technical
			self::DevelopmentSchedule->value => 'جدول التطوير',
			self::ReleaseSchedule->value => 'جدول الإصدار',
			self::DeploymentSchedule->value => 'جدول النشر',
			self::TestingSchedule->value => 'جدول الاختبار',
			self::SprintSchedule->value => 'جدول السباقات',

			// Personal/Life
			self::PersonalSchedule->value => 'جدول شخصي',
			self::FamilySchedule->value => 'جدول عائلي',
			self::HealthSchedule->value => 'جدول الصحة',
			self::FitnessSchedule->value => 'جدول اللياقة',
			self::TravelSchedule->value => 'جدول السفر',

			// Other
			self::AdHocSchedule->value => 'جدول مخصص',
			self::DynamicSchedule->value => 'جدول ديناميكي',
			self::ManualSchedule->value => 'جدول يدوي',
			self::AutomatedSchedule->value => 'جدول آلي',
			self::TemplateSchedule->value => 'جدول قالب',
			self::Other->value => 'آخر',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'לוח זמנים קבוע',
			self::FlexibleSchedule->value => 'לוח זמנים גמיש',
			self::RollingSchedule->value => 'לוח זמנים מתגלגל',
			self::ContinuousSchedule->value => 'לוח זמנים רציף',
			self::CyclicSchedule->value => 'לוח זמנים מחזורי',

			// Project/Work
			self::ProjectSchedule->value => 'לוח זמנים לפרויקט',
			self::MilestoneSchedule->value => 'לוח זמנים לאבני דרך',
			self::PhaseBasedSchedule->value => 'לוח זמנים לפי שלבים',
			self::IterativeSchedule->value => 'לוח זמנים איטרטיבי',
			self::AgileSchedule->value => 'לוח זמנים Agile',
			self::WaterfallSchedule->value => 'לוח זמנים מפל',

			// Resource
			self::ResourceSchedule->value => 'לוח זמנים למשאבים',
			self::CapacitySchedule->value => 'לוח זמנים לקיבולת',
			self::WorkSchedule->value => 'לוח זמנים לעבודה',
			self::ShiftSchedule->value => 'לוח משמרות',
			self::RotationSchedule->value => 'לוח זמנים לסיבוב',

			// Event-based
			self::EventDrivenSchedule->value => 'לוח זמנים מבוסס אירועים',
			self::TriggerBasedSchedule->value => 'לוח זמנים מבוסס טריגרים',
			self::DependencySchedule->value => 'לוח זמנים לתלותיות',
			self::ConditionalSchedule->value => 'לוח זמנים מותנה',

			// Calendar
			self::CalendarSchedule->value => 'לוח זמנים לפי לוח שנה',
			self::TimeBlockSchedule->value => 'לוח זמנים לפי בלוקי זמן',
			self::AppointmentSchedule->value => 'לוח זמנים לפגישות',
			self::MeetingSchedule->value => 'לוח זמנים לפגישות',

			// Production/Operations
			self::ProductionSchedule->value => 'לוח זמנים לייצור',
			self::ManufacturingSchedule->value => 'לוח זמנים לייצור',
			self::MaintenanceSchedule->value => 'לוח זמנים לתחזוקה',
			self::InventorySchedule->value => 'לוח זמנים למלאי',
			self::DeliverySchedule->value => 'לוח זמנים למשלוחים',

			// Financial/Business
			self::BudgetSchedule->value => 'לוח זמנים לתקציב',
			self::FinancialSchedule->value => 'לוח זמנים פיננסי',
			self::BillingSchedule->value => 'לוח זמנים לחיוב',
			self::ReportingSchedule->value => 'לוח זמנים לדיווח',
			self::TaxSchedule->value => 'לוח זמנים למיסים',

			// Education/Training
			self::AcademicSchedule->value => 'לוח זמנים אקדמי',
			self::TrainingSchedule->value => 'לוח זמנים להדרכה',
			self::CourseSchedule->value => 'לוח זמנים לקורסים',
			self::ExamSchedule->value => 'לוח זמנים לבחינות',
			self::SessionSchedule->value => 'לוח זמנים למפגשים',

			// Marketing/Sales
			self::MarketingSchedule->value => 'לוח זמנים לשיווק',
			self::CampaignSchedule->value => 'לוח זמנים לקמפיין',
			self::ContentSchedule->value => 'לוח זמנים לתוכן',
			self::SocialMediaSchedule->value => 'לוח זמנים לרשתות חברתיות',
			self::SalesSchedule->value => 'לוח זמנים למכירות',

			// Development/Technical
			self::DevelopmentSchedule->value => 'לוח זמנים לפיתוח',
			self::ReleaseSchedule->value => 'לוח זמנים לשחרור',
			self::DeploymentSchedule->value => 'לוח זמנים לפריסה',
			self::TestingSchedule->value => 'לוח זמנים לבדיקות',
			self::SprintSchedule->value => 'לוח זמנים לספרינטים',

			// Personal/Life
			self::PersonalSchedule->value => 'לוח זמנים אישי',
			self::FamilySchedule->value => 'לוח זמנים משפחתי',
			self::HealthSchedule->value => 'לוח זמנים לבריאות',
			self::FitnessSchedule->value => 'לוח זמנים לכושר',
			self::TravelSchedule->value => 'לוח זמנים לנסיעות',

			// Other
			self::AdHocSchedule->value => 'לוח זמנים Ad Hoc',
			self::DynamicSchedule->value => 'לוח זמנים דינמי',
			self::ManualSchedule->value => 'לוח זמנים ידני',
			self::AutomatedSchedule->value => 'לוח זמנים אוטומטי',
			self::TemplateSchedule->value => 'לוח זמנים לתבנית',
			self::Other->value => 'אחר',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => '固定スケジュール',
			self::FlexibleSchedule->value => '柔軟なスケジュール',
			self::RollingSchedule->value => 'ローリングスケジュール',
			self::ContinuousSchedule->value => '連続スケジュール',
			self::CyclicSchedule->value => '周期スケジュール',

			// Project/Work
			self::ProjectSchedule->value => 'プロジェクトスケジュール',
			self::MilestoneSchedule->value => 'マイルストーンスケジュール',
			self::PhaseBasedSchedule->value => '段階的スケジュール',
			self::IterativeSchedule->value => '反復スケジュール',
			self::AgileSchedule->value => 'アジャイルスケジュール',
			self::WaterfallSchedule->value => 'ウォーターフォールスケジュール',

			// Resource
			self::ResourceSchedule->value => 'リソーススケジュール',
			self::CapacitySchedule->value => '容量スケジュール',
			self::WorkSchedule->value => '作業スケジュール',
			self::ShiftSchedule->value => 'シフトスケジュール',
			self::RotationSchedule->value => '回転スケジュール',

			// Event-based
			self::EventDrivenSchedule->value => 'イベント駆動型スケジュール',
			self::TriggerBasedSchedule->value => 'トリガーベースのスケジュール',
			self::DependencySchedule->value => '依存関係スケジュール',
			self::ConditionalSchedule->value => '条件付きスケジュール',

			// Calendar
			self::CalendarSchedule->value => 'カレンダースケジュール',
			self::TimeBlockSchedule->value => '時間ブロックスケジュール',
			self::AppointmentSchedule->value => '予約スケジュール',
			self::MeetingSchedule->value => '会議スケジュール',

			// Production/Operations
			self::ProductionSchedule->value => '生産スケジュール',
			self::ManufacturingSchedule->value => '製造スケジュール',
			self::MaintenanceSchedule->value => '保守スケジュール',
			self::InventorySchedule->value => '在庫スケジュール',
			self::DeliverySchedule->value => '配送スケジュール',

			// Financial/Business
			self::BudgetSchedule->value => '予算スケジュール',
			self::FinancialSchedule->value => '財務スケジュール',
			self::BillingSchedule->value => '請求スケジュール',
			self::ReportingSchedule->value => '報告スケジュール',
			self::TaxSchedule->value => '税務スケジュール',

			// Education/Training
			self::AcademicSchedule->value => '学術スケジュール',
			self::TrainingSchedule->value => 'トレーニングスケジュール',
			self::CourseSchedule->value => 'コーススケジュール',
			self::ExamSchedule->value => '試験スケジュール',
			self::SessionSchedule->value => 'セッションスケジュール',

			// Marketing/Sales
			self::MarketingSchedule->value => 'マーケティングスケジュール',
			self::CampaignSchedule->value => 'キャンペーンスケジュール',
			self::ContentSchedule->value => 'コンテンツスケジュール',
			self::SocialMediaSchedule->value => 'ソーシャルメディアスケジュール',
			self::SalesSchedule->value => '販売スケジュール',

			// Development/Technical
			self::DevelopmentSchedule->value => '開発スケジュール',
			self::ReleaseSchedule->value => 'リリーススケジュール',
			self::DeploymentSchedule->value => '展開スケジュール',
			self::TestingSchedule->value => 'テストスケジュール',
			self::SprintSchedule->value => 'スプリントスケジュール',

			// Personal/Life
			self::PersonalSchedule->value => '個人スケジュール',
			self::FamilySchedule->value => '家族スケジュール',
			self::HealthSchedule->value => '健康スケジュール',
			self::FitnessSchedule->value => 'フィットネススケジュール',
			self::TravelSchedule->value => '旅行スケジュール',

			// Other
			self::AdHocSchedule->value => 'アドホックスケジュール',
			self::DynamicSchedule->value => '動的スケジュール',
			self::ManualSchedule->value => '手動スケジュール',
			self::AutomatedSchedule->value => '自動化スケジュール',
			self::TemplateSchedule->value => 'テンプレートスケジュール',
			self::Other->value => 'その他',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => 'Fast Tidsplan',
			self::FlexibleSchedule->value => 'Fleksibel Tidsplan',
			self::RollingSchedule->value => 'Rullende Tidsplan',
			self::ContinuousSchedule->value => 'Kontinuerlig Tidsplan',
			self::CyclicSchedule->value => 'Cyklisk Tidsplan',

			// Project/Work
			self::ProjectSchedule->value => 'Projekt Tidsplan',
			self::MilestoneSchedule->value => 'Milesten Tidsplan',
			self::PhaseBasedSchedule->value => 'Fasebaseret Tidsplan',
			self::IterativeSchedule->value => 'Iterativ Tidsplan',
			self::AgileSchedule->value => 'Agile Tidsplan',
			self::WaterfallSchedule->value => 'Vandfald Tidsplan',

			// Resource
			self::ResourceSchedule->value => 'Ressource Tidsplan',
			self::CapacitySchedule->value => 'Kapacitets Tidsplan',
			self::WorkSchedule->value => 'Arbejds Tidsplan',
			self::ShiftSchedule->value => 'Skift Tidsplan',
			self::RotationSchedule->value => 'Rotations Tidsplan',

			// Event-based
			self::EventDrivenSchedule->value => 'Begivenhedsdrevet Tidsplan',
			self::TriggerBasedSchedule->value => 'Triggerbaseret Tidsplan',
			self::DependencySchedule->value => 'Afhængigheds Tidsplan',
			self::ConditionalSchedule->value => 'Betinget Tidsplan',

			// Calendar
			self::CalendarSchedule->value => 'Kalender Tidsplan',
			self::TimeBlockSchedule->value => 'Tidsblok Tidsplan',
			self::AppointmentSchedule->value => 'Aftale Tidsplan',
			self::MeetingSchedule->value => 'Møde Tidsplan',

			// Production/Operations
			self::ProductionSchedule->value => 'Produktions Tidsplan',
			self::ManufacturingSchedule->value => 'Fabrikations Tidsplan',
			self::MaintenanceSchedule->value => 'Vedligeholdelses Tidsplan',
			self::InventorySchedule->value => 'Lager Tidsplan',
			self::DeliverySchedule->value => 'Leverings Tidsplan',

			// Financial/Business
			self::BudgetSchedule->value => 'Budget Tidsplan',
			self::FinancialSchedule->value => 'Finansiel Tidsplan',
			self::BillingSchedule->value => 'Fakturerings Tidsplan',
			self::ReportingSchedule->value => 'Rapporterings Tidsplan',
			self::TaxSchedule->value => 'Skat Tidsplan',

			// Education/Training
			self::AcademicSchedule->value => 'Akademisk Tidsplan',
			self::TrainingSchedule->value => 'Trænings Tidsplan',
			self::CourseSchedule->value => 'Kursus Tidsplan',
			self::ExamSchedule->value => 'Eksamen Tidsplan',
			self::SessionSchedule->value => 'Session Tidsplan',

			// Marketing/Sales
			self::MarketingSchedule->value => 'Marketing Tidsplan',
			self::CampaignSchedule->value => 'Kampagne Tidsplan',
			self::ContentSchedule->value => 'Indhold Tidsplan',
			self::SocialMediaSchedule->value => 'Sociale Medier Tidsplan',
			self::SalesSchedule->value => 'Salg Tidsplan',

			// Development/Technical
			self::DevelopmentSchedule->value => 'Udviklings Tidsplan',
			self::ReleaseSchedule->value => 'Udgivelses Tidsplan',
			self::DeploymentSchedule->value => 'Implementerings Tidsplan',
			self::TestingSchedule->value => 'Test Tidsplan',
			self::SprintSchedule->value => 'Sprint Tidsplan',

			// Personal/Life
			self::PersonalSchedule->value => 'Personlig Tidsplan',
			self::FamilySchedule->value => 'Familie Tidsplan',
			self::HealthSchedule->value => 'Sundheds Tidsplan',
			self::FitnessSchedule->value => 'Fitness Tidsplan',
			self::TravelSchedule->value => 'Rejse Tidsplan',

			// Other
			self::AdHocSchedule->value => 'Ad Hoc Tidsplan',
			self::DynamicSchedule->value => 'Dynamisk Tidsplan',
			self::ManualSchedule->value => 'Manuel Tidsplan',
			self::AutomatedSchedule->value => 'Automatiseret Tidsplan',
			self::TemplateSchedule->value => 'Skabelon Tidsplan',
			self::Other->value => 'Andet',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			// Time-based
			self::FixedSchedule->value => '固定日程',
			self::FlexibleSchedule->value => '灵活日程',
			self::RollingSchedule->value => '滚动日程',
			self::ContinuousSchedule->value => '连续日程',
			self::CyclicSchedule->value => '循环日程',

			// Project/Work
			self::ProjectSchedule->value => '项目日程',
			self::MilestoneSchedule->value => '里程碑日程',
			self::PhaseBasedSchedule->value => '阶段日程',
			self::IterativeSchedule->value => '迭代日程',
			self::AgileSchedule->value => '敏捷日程',
			self::WaterfallSchedule->value => '瀑布日程',

			// Resource
			self::ResourceSchedule->value => '资源日程',
			self::CapacitySchedule->value => '容量日程',
			self::WorkSchedule->value => '工作日程',
			self::ShiftSchedule->value => '轮班日程',
			self::RotationSchedule->value => '轮换日程',

			// Event-based
			self::EventDrivenSchedule->value => '事件驱动日程',
			self::TriggerBasedSchedule->value => '触发器日程',
			self::DependencySchedule->value => '依赖日程',
			self::ConditionalSchedule->value => '条件日程',

			// Calendar
			self::CalendarSchedule->value => '日历日程',
			self::TimeBlockSchedule->value => '时间块日程',
			self::AppointmentSchedule->value => '预约日程',
			self::MeetingSchedule->value => '会议日程',

			// Production/Operations
			self::ProductionSchedule->value => '生产日程',
			self::ManufacturingSchedule->value => '制造日程',
			self::MaintenanceSchedule->value => '维护日程',
			self::InventorySchedule->value => '库存日程',
			self::DeliverySchedule->value => '交付日程',

			// Financial/Business
			self::BudgetSchedule->value => '预算日程',
			self::FinancialSchedule->value => '财务日程',
			self::BillingSchedule->value => '计费日程',
			self::ReportingSchedule->value => '报告日程',
			self::TaxSchedule->value => '税务日程',

			// Education/Training
			self::AcademicSchedule->value => '学术日程',
			self::TrainingSchedule->value => '培训日程',
			self::CourseSchedule->value => '课程日程',
			self::ExamSchedule->value => '考试日程',
			self::SessionSchedule->value => '会话日程',

			// Marketing/Sales
			self::MarketingSchedule->value => '营销日程',
			self::CampaignSchedule->value => '活动日程',
			self::ContentSchedule->value => '内容日程',
			self::SocialMediaSchedule->value => '社交媒体日程',
			self::SalesSchedule->value => '销售日程',

			// Development/Technical
			self::DevelopmentSchedule->value => '开发日程',
			self::ReleaseSchedule->value => '发布日程',
			self::DeploymentSchedule->value => '部署日程',
			self::TestingSchedule->value => '测试日程',
			self::SprintSchedule->value => '冲刺日程',

			// Personal/Life
			self::PersonalSchedule->value => '个人日程',
			self::FamilySchedule->value => '家庭日程',
			self::HealthSchedule->value => '健康日程',
			self::FitnessSchedule->value => '健身日程',
			self::TravelSchedule->value => '旅行日程',

			// Other
			self::AdHocSchedule->value => '临时日程',
			self::DynamicSchedule->value => '动态日程',
			self::ManualSchedule->value => '手动日程',
			self::AutomatedSchedule->value => '自动日程',
			self::TemplateSchedule->value => '模板日程',
			self::Other->value => '其他',
		];
	}
}
