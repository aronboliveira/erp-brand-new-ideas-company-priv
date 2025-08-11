# MIGRATIONS

## FILES AND CLASSES NAMING

- Files that had their base name changed need to be renamed in migrations as well (ex.:
  AnnouncementEmployee -> EmployeeAnnouncement,
  AttendanceEmployee -> EmployeeAttendance,
  Contract_attachment -> ContractAttachment,
  GenerateOfferLetter -> GeneratedOfferLetter,
  Vender -> Vendor,
  Projectstages -> ProjectStages,
  TrialBalancExport → TrialBalanceExport
  task_reportExport → TaskReportExport,
  );
  puserhConfig → PusherConfig

## FIELDS NAMING

- Misspell on Purchase::$statues (corrected to $statuses)
- Misspel on SaturationDeduction::$saturationDeductiontype (corrected to $saturationDeductionType)
- Misspel on ZoomMeetingTrait::MEETING_TYPE_SCHEDULE (corrected to MEETING_TYPE_SCHEDULED)
- Activity→get_activity (corrected to getActivity)
- ActivityLog→{userdetail (to userDetail), fetchgetRemark(to fetchGetRemark)}
- Comission::$comissiontype → comissionType
- DocumentUploads table named as ducument_uploads;

## METHODS NAMING

- Several methods from the original fork had their naming casing not following what is expected from Laravel (thus renamed in new versions), as in the list:

- AppraisalController.getemployee
- DealController.deal_list
- EventController.get_event_data
- EventController.getdepartment
- EventController.getemployee
- LeadController.lead_list
- MeetingController.getdepartment
- MeetingController.getemployee
- MeetingController.get_meeting_data
- PosController.getproduct
- PosController.cartdiscount
- AuthenticatedSessionController.get_device_type
- BenefitPaymentController.call_back
- CreditNoteController.getinvoice
- DebitNoteController.getbill
- PayslipController.showemployee
- PayslipController.search_json
- PayslipController.paysalary
- PayslipController.bulk_pay_create
- PayslipController.bulkpayment
- PayslipController.employeepayslip
- OtherPaymentController.otherpaymentCreate
- BranchController.getdepartment
- BranchController.getemployee
- ContractController.contract_status_edit
- ContractController.contract_descriptionStore
- ContractController.clientwiseproject
- ContractController.copycontract
- ContractController.copycontractstore
- ContractController.sendmailContract
- ContractController.pdffromcontract
- UserController::{todo_store, todo_update, todo_destroy}
- ProductServiceController.warehouseemptyCart
- InterviewScheduleController.get_interview_data
- LeaveController.changeaction
- LeaveController.jsonCount
- ProjectController.copyproject
- ProjectController.copyprojectstore
- ProjectController.copylink_setting_create
- ProjectController.copylinksetting
- ProjectController.projectlink
- DashboardController.{account_dashboard_index, project_dashboard_index, hrm_dashboard_index, crm_dashboard_index,
  pos_dashboard_index}
- ReportController.{stock_report, PayrollReportExport, LeaveReportExport, getdepartment, getemployee, leadreport, dealreport, monthlyCashflow, ReceivablesExport, ReceivablesPrint, PayablesReport, PayablesPrint}
- VendorController.{editprofile, changeLanqage}
- WarehouseTransferController.{getproduct, getquantity}
- ZoomMeetingController.{projectwiseuser, get_zoom_meeting_data}
- SystyemController.{offerletterupdate, joiningletterupdate, experienceCertificateupdate, CookieConsent, chatgptSetting}
- DiscoverController.{discover_create, discover_store, discover_edit, discover_update, discover_delete}
- FaqController.{faq_create, faq_store, faq_edit, faq_update, faq_delete}
- FeaturesController.{feature_create, feature_store, feature_edit, feature_update, feature_delete, feature_highlight_create, features_create, features_store, features_edit, features_update, features_delete}
- ScreenshootsController.{screenshots_createm screenshots_store, screenshots_edit, screenshots_update, screenshots_delete}
- TestimonialsController.{testimonials_create, testimonials_store, testimonials_edit, testimonials_update, testimonials_delete}
- ZoomMeetingTraits.{createmitting, meetingUpdate, get}
