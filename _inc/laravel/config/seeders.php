<?php
/**
 * Seeder Profile Configuration
 *
 * Controls which seeders are called by DatabaseSeeder based on
 * the SEED_PROFILE env var or --seed-profile artisan flag.
 *
 * Profiles:
 *  - minimal  : Foundation tables only (fast CI, ~1 min)
 *  - standard : Full mock data without heavy combinations (~10-30 min)
 *  - full     : Exhaustive combinations from long-version seeders (hours)
 *
 * Fine-grained overrides:
 *  - SEED_ONLY=BillSeeder,InvoiceSeeder  → run ONLY these (plus foundation)
 *  - SEED_SKIP=PurchaseSeeder,TaskSeeder → skip these from the profile list
 *
 * ⚠ Long-version seeders live in _inc/.seeders/ and .backup/database/seeders/
 *   — NEVER modify those. To test with them, copy the specific files into
 *   database/seeders/ temporarily, then revert when done.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Foundation Seeders (always run, regardless of profile)
    |--------------------------------------------------------------------------
    */
    'foundation' => [
        \Database\Seeders\NotificationSeeder::class,
        \Database\Seeders\PlansTableSeeder::class,
        \Database\Seeders\UsersTableSeeder::class,
        \Database\Seeders\AiTemplateSeeder::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | User Bootstrap (phased, always run after foundation)
    |--------------------------------------------------------------------------
    */
    'user_bootstrap' => [
        'initial' => \Database\Seeders\UserSeeder::class,
        'org_structure' => [
            \Database\Seeders\BranchSeeder::class,
            \Database\Seeders\DepartmentSeeder::class,
            \Database\Seeders\DesignationSeeder::class,
        ],
        'additional' => \Database\Seeders\UserSeeder::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimal Profile — empty mock list, foundation + user bootstrap only
    |--------------------------------------------------------------------------
    */
    'minimal' => [],

    /*
    |--------------------------------------------------------------------------
    | Standard Profile — all module seeders (current default behaviour)
    |--------------------------------------------------------------------------
    */
    'standard' => [
        // Scheduling & infrastructure
        \Database\Seeders\ScheduleSeeder::class,
        \Database\Seeders\BranchSeeder::class,
        \Database\Seeders\DepartmentSeeder::class,
        \Database\Seeders\ClientSeeder::class,
        \Database\Seeders\PasswordResetsSeeder::class,
        \Database\Seeders\PipelineSeeder::class,
        \Database\Seeders\PlanSeeder::class,
        \Database\Seeders\DocumentSeeder::class,

        // Notifications & emails
        \Database\Seeders\NotificationTemplatesSeeder::class,
        \Database\Seeders\NotificationTemplateLangsSeeder::class,
        \Database\Seeders\NotificationsLateSeeder::class,
        \Database\Seeders\EmailTemplatesSeeder::class,
        \Database\Seeders\EmailTemplateLangsSeeder::class,
        \Database\Seeders\UserEmailTemplatesSeeder::class,

        // Custom fields
        \Database\Seeders\CustomFieldsSeeder::class,
        \Database\Seeders\CustomFieldValuesSeeder::class,
        \Database\Seeders\CustomQuestionSeeder::class,

        // Projects
        \Database\Seeders\ProjectSeeder::class,
        \Database\Seeders\ProjectStagesSeeder::class,
        \Database\Seeders\ProjectUserSeeder::class,
        \Database\Seeders\ProjectEmailTemplateSeeder::class,

        // CRM core
        \Database\Seeders\SourceSeeder::class,
        \Database\Seeders\StageSeeder::class,
        \Database\Seeders\LabelSeeder::class,

        // HR / Performance
        \Database\Seeders\AppraisalSeeder::class,
        \Database\Seeders\IndicatorSeeder::class,
        \Database\Seeders\CompanyPolicySeeder::class,
        \Database\Seeders\GoalTypesSeeder::class,
        \Database\Seeders\GoalsSeeder::class,
        \Database\Seeders\GoalTrackingsSeeder::class,
        \Database\Seeders\JobCategoriesSeeder::class,

        // Deals
        \Database\Seeders\DealSeeder::class,
        \Database\Seeders\DealFileSeeder::class,
        \Database\Seeders\DealDiscussionSeeder::class,
        \Database\Seeders\UserDealSeeder::class,
        \Database\Seeders\ClientDealSeeder::class,

        // Tax & bugs
        \Database\Seeders\TaxSeeder::class,
        \Database\Seeders\BugSeeder::class,
        \Database\Seeders\BugFileSeeder::class,
        \Database\Seeders\BugCommentSeeder::class,
        \Database\Seeders\BugStatusSeeder::class,

        // Payroll
        \Database\Seeders\PayslipTypeSeeder::class,
        \Database\Seeders\EmployeeSeeder::class,
        \Database\Seeders\EmployeeDocumentSeeder::class,

        // Communications
        \Database\Seeders\NoteSeeder::class,
        \Database\Seeders\EmailSeeder::class,
        \Database\Seeders\UserContactSeeder::class,

        // Tasks
        \Database\Seeders\MilestoneSeeder::class,
        \Database\Seeders\TaskSeeder::class,
        \Database\Seeders\DealTaskSeeder::class,
        \Database\Seeders\TaskStageSeeder::class,
        \Database\Seeders\TaskChecklistSeeder::class,
        \Database\Seeders\TaskCommentSeeder::class,
        \Database\Seeders\TaskFileSeeder::class,
        \Database\Seeders\UserToDoSeeder::class,

        // Training
        \Database\Seeders\TrainingTypeSeeder::class,
        \Database\Seeders\TrainerSeeder::class,
        \Database\Seeders\TrainingSeeder::class,

        // Awards & HR actions
        \Database\Seeders\AwardTypeSeeder::class,
        \Database\Seeders\AwardSeeder::class,
        \Database\Seeders\TerminationTypeSeeder::class,
        \Database\Seeders\TerminationSeeder::class,
        \Database\Seeders\ResignationSeeder::class,
        \Database\Seeders\TravelSeeder::class,
        \Database\Seeders\PromotionSeeder::class,
        \Database\Seeders\TransferSeeder::class,
        \Database\Seeders\WarningSeeder::class,
        \Database\Seeders\ComplaintSeeder::class,

        // Recruitment
        \Database\Seeders\JobStageSeeder::class,
        \Database\Seeders\JobSeeder::class,
        \Database\Seeders\JobApplicationSeeder::class,
        \Database\Seeders\JobApplicationNoteSeeder::class,
        \Database\Seeders\JobOnBoardSeeder::class,
        \Database\Seeders\CompetencySeeder::class,

        // Salary components
        \Database\Seeders\AllowanceOptionSeeder::class,
        \Database\Seeders\LoanOptionSeeder::class,
        \Database\Seeders\DeductionOptionSeeder::class,
        \Database\Seeders\SetSalarySeeder::class,
        \Database\Seeders\AllowanceSeeder::class,
        \Database\Seeders\LoanSeeder::class,
        \Database\Seeders\SaturationDeductionSeeder::class,
        \Database\Seeders\OvertimeSeeder::class,
        \Database\Seeders\EmployeeAttendanceSeeder::class,
        \Database\Seeders\OtherPaymentSeeder::class,

        // Contracts
        \Database\Seeders\ContractTypeSeeder::class,
        \Database\Seeders\ContractSeeder::class,
        \Database\Seeders\ContractAttachmentSeeder::class,
        \Database\Seeders\ContractCommentSeeder::class,
        \Database\Seeders\ContractNoteSeeder::class,

        // Performance & Payslip
        \Database\Seeders\PerformanceTypeSeeder::class,
        \Database\Seeders\PayslipSeeder::class,
        \Database\Seeders\ProjectTaskSeeder::class,
        \Database\Seeders\TimesheetSeeder::class,

        // Accounting
        \Database\Seeders\ChartOfAccountTypeSeeder::class,
        \Database\Seeders\ChartOfAccountSubTypeSeeder::class,
        \Database\Seeders\ChartOfAccountSeeder::class,

        // Products & inventory
        \Database\Seeders\ProductServiceCategorySeeder::class,
        \Database\Seeders\ProductCategorySeeder::class,
        \Database\Seeders\ProductServiceSeeder::class,
        \Database\Seeders\ProductServiceUnitSeeder::class,
        \Database\Seeders\ProductSeeder::class,

        // Customers & Banking
        \Database\Seeders\CustomerSeeder::class,
        \Database\Seeders\BankAccountSeeder::class,
        \Database\Seeders\BankTransferSeeder::class,
        \Database\Seeders\VendorSeeder::class,
        \Database\Seeders\ClientPermissionSeeder::class,

        // Misc
        \Database\Seeders\AnnouncementSeeder::class,
        \Database\Seeders\InterviewScheduleSeeder::class,

        // Orders & plans
        \Database\Seeders\OrderSeeder::class,
        \Database\Seeders\CouponSeeder::class,
        \Database\Seeders\UserCouponSeeder::class,
        \Database\Seeders\PlanRequestSeeder::class,

        // Leave & meetings
        \Database\Seeders\LeaveTypeSeeder::class,
        \Database\Seeders\LeaveSeeder::class,
        \Database\Seeders\MeetingSeeder::class,
        \Database\Seeders\MeetingEmployeeSeeder::class,
        \Database\Seeders\ZoomMeetingSeeder::class,
        \Database\Seeders\EventSeeder::class,
        \Database\Seeders\EventEmployeeSeeder::class,

        // Billing & invoicing
        \Database\Seeders\BillSeeder::class,
        \Database\Seeders\InvoiceSeeder::class,
        \Database\Seeders\PaymentSeeder::class,
        \Database\Seeders\BillAccountSeeder::class,
        \Database\Seeders\BillPaymentSeeder::class,
        \Database\Seeders\BillProductSeeder::class,
        \Database\Seeders\InvoicePaymentSeeder::class,
        \Database\Seeders\ProjectInvoiceSeeder::class,
        \Database\Seeders\RevenueSeeder::class,

        // Warehouse & POS
        \Database\Seeders\WarehouseSeeder::class,
        \Database\Seeders\PosSeeder::class,
        \Database\Seeders\PosPaymentSeeder::class,
        \Database\Seeders\TransactionSeeder::class,

        // Assets & notes
        \Database\Seeders\AssetsSeeder::class,
        \Database\Seeders\CreditNoteSeeder::class,
        \Database\Seeders\DebitNoteSeeder::class,
        \Database\Seeders\InvoiceBankTransferSeeder::class,

        // Expenses
        \Database\Seeders\ExpenseSeeder::class,

        // Leads
        \Database\Seeders\LeadStageSeeder::class,
        \Database\Seeders\LeadSeeder::class,
        \Database\Seeders\LeadActivityLogSeeder::class,
        \Database\Seeders\LeadDiscussionSeeder::class,
        \Database\Seeders\UserLeadSeeder::class,
        \Database\Seeders\LeadEmailSeeder::class,
        \Database\Seeders\LeadFileSeeder::class,
        \Database\Seeders\LeadCallSeeder::class,
        \Database\Seeders\DealEmailSeeder::class,
        \Database\Seeders\DealCallSeeder::class,

        // Estimates & proposals
        \Database\Seeders\EstimationSeeder::class,
        \Database\Seeders\ProposalSeeder::class,
        \Database\Seeders\ProposalProductSeeder::class,

        // Purchasing
        \Database\Seeders\PurchaseSeeder::class,
        \Database\Seeders\PurchasePaymentSeeder::class,

        // Product associations
        \Database\Seeders\InvoiceProductSeeder::class,
        \Database\Seeders\PosProductSeeder::class,
        \Database\Seeders\WarehouseProductSeeder::class,

        // Calendar & time
        \Database\Seeders\HolidaySeeder::class,
        \Database\Seeders\TimeTrackerSeeder::class,
        \Database\Seeders\PlanningScheduleSeeder::class,
        \Database\Seeders\WarehouseTransferSeeder::class,

        // Misc late
        \Database\Seeders\BasicFavoritesSeeder::class,
        \Database\Seeders\JournalEntrySeeder::class,
        \Database\Seeders\JournalItemSeeder::class,
        \Database\Seeders\BudgetSeeder::class,

        // Forms
        \Database\Seeders\FormBuilderSeeder::class,
        \Database\Seeders\FormFieldSeeder::class,
        \Database\Seeders\FormFieldResponseSeeder::class,
        \Database\Seeders\FormResponseSeeder::class,

        // Support & logs
        \Database\Seeders\SupportSeeder::class,
        \Database\Seeders\SupportReplySeeder::class,
        \Database\Seeders\TrackPhotoSeeder::class,
        \Database\Seeders\LogActivitySeeder::class,
        \Database\Seeders\ActivityLogSeeder::class,
        \Database\Seeders\StockReportSeeder::class,

        // Dashboard & statements
        \Database\Seeders\DashboardSeeder::class,
        \Database\Seeders\AccountStatementSeeder::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Full Profile — Same as standard (long-version behaviour comes from
    | swapping the seeder files, not from changing the list).
    | This profile adds extra validation and data-fix seeders.
    |--------------------------------------------------------------------------
    */
    'full' => 'standard', // inherits standard, plus additions below

    'full_extras' => [
        \Database\Seeders\ContentValidationSeeder::class,
        \Database\Seeders\ProjectDataFixSeeder::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Post-run fixups (always run after main profile)
    |--------------------------------------------------------------------------
    */
    'post_run' => [
        'lead_reseed'          => \Database\Seeders\LeadSeeder::class,
        'content_validation'   => \Database\Seeders\ContentValidationSeeder::class,
    ],
];
