# Naming & Migration History

> Extracted from KNOWN_ISSUES.md on 2026-03-04.
> These are structural naming decisions inherited from the original fork
> and corrected in the current codebase. Kept for audit reference only.

---

## Files and Classes Renamed

| Original             | Corrected            |
| -------------------- | -------------------- |
| AnnouncementEmployee | EmployeeAnnouncement |
| AttendanceEmployee   | EmployeeAttendance   |
| Contract_attachment  | ContractAttachment   |
| GenerateOfferLetter  | GeneratedOfferLetter |
| Vender               | Vendor               |
| Projectstages        | ProjectStages        |
| TrialBalancExport    | TrialBalanceExport   |
| task_reportExport    | TaskReportExport     |
| puserhConfig         | PusherConfig         |
| ContractNotes.php    | ContractNote.php     |

---

## Fields Renamed

| Original                                      | Corrected                |
| --------------------------------------------- | ------------------------ |
| Purchase::$statues                            | $statuses                |
| SaturationDeduction::$saturationDeductiontype | $saturationDeductionType |
| ZoomMeetingTrait::MEETING_TYPE_SCHEDULE       | MEETING_TYPE_SCHEDULED   |
| Activity→get_activity                         | getActivity              |
| ActivityLog→userdetail                        | userDetail               |
| ActivityLog→fetchgetRemark                    | fetchGetRemark           |
| Comission::$comissiontype                     | comissionType            |
| DocumentUploads table (ducument_uploads)      | document_uploads         |

---

## Methods Renamed (snake_case / run-together → camelCase)

### Auth & Session

- AuthenticatedSessionController.get_device_type

### HR / Payroll

- PayslipController: showemployee, search_json, paysalary, bulk_pay_create, bulkpayment, employeepayslip
- OtherPaymentController: otherpaymentCreate
- LeaveController: changeaction, jsonCount
- InterviewScheduleController: get_interview_data
- BenefitPaymentController: call_back

### CRM

- LeadController: lead_list
- DealController: deal_list
- EventController: get_event_data, getdepartment, getemployee
- MeetingController: getdepartment, getemployee, get_meeting_data
- ZoomMeetingController: projectwiseuser, get_zoom_meeting_data

### Projects & Tasks

- ProjectController: copyproject, copyprojectstore, copylink_setting_create, copylinksetting, projectlink
- ContractController: contract_status_edit, contract_descriptionStore, clientwiseproject, copycontract, copycontractstore, sendmailContract, pdffromcontract
- UserController: todo_store, todo_update, todo_destroy

### POS / Inventory

- PosController: getproduct, cartdiscount
- ProductServiceController: warehouseemptyCart
- WarehouseTransferController: getproduct, getquantity

### Finance

- CreditNoteController: getinvoice
- DebitNoteController: getbill

### Dashboard / Reports

- DashboardController: account_dashboard_index, project_dashboard_index, hrm_dashboard_index, crm_dashboard_index, pos_dashboard_index
- ReportController: stock_report, PayrollReportExport, LeaveReportExport, getdepartment, getemployee, leadreport, dealreport, monthlyCashflow, ReceivablesExport, ReceivablesPrint, PayablesReport, PayablesPrint

### Admin / System

- SystyemController: offerletterupdate, joiningletterupdate, experienceCertificateupdate, CookieConsent, chatgptSetting
- BranchController: getdepartment, getemployee
- VendorController: editprofile, changeLanqage
- AppraisalController: getemployee

### Landing Page Module

- DiscoverController: discover_create, discover_store, discover_edit, discover_update, discover_delete
- FaqController: faq_create, faq_store, faq_edit, faq_update, faq_delete
- FeaturesController: feature_create, feature_store, feature_edit, feature_update, feature_delete, feature_highlight_create, features_create, features_store, features_edit, features_update, features_delete
- ScreenshootsController: screenshots_createm, screenshots_store, screenshots_edit, screenshots_update, screenshots_delete
- TestimonialsController: testimonials_create, testimonials_store, testimonials_edit, testimonials_update, testimonials_delete

### Traits

- ZoomMeetingTraits: createmitting, meetingUpdate, get
