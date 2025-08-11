export type Bin = "on" | "off";
export type RcDispatch<T> = React.Dispatch<React.SetStateAction<T>>;
export interface Settings {
  color?: string;
  SITE_RTL?: "on" | "off";
  cust_darklayout?: "on" | "off";
  meta_title?: string;
  meta_desc?: string;
  meta_image?: string;
  company_favicon?: string;
  company_logo_dark?: string;
  company_logo_light?: string;
  title_text?: string;
  footer_text?: string;
  enable_cookie?: "on" | "off";
}
export type LanguagesAcronyms =
  | "ar"
  | "zh"
  | "da"
  | "de"
  | "en"
  | "es"
  | "fr"
  | "he"
  | "it"
  | "ja"
  | "nl"
  | "pl"
  | "pt"
  | "ru"
  | "tr"
  | "pt-br";
export type LanguageCompleteNames =
  | "العربية"
  | "中文"
  | "Dansk"
  | "Deutsch"
  | "English"
  | "Español"
  | "Français"
  | "עברית"
  | "Italiano"
  | "日本語"
  | "Nederlands"
  | "Polski"
  | "Português"
  | "Русский"
  | "Türkçe"
  | "Português (Brasil)";
export interface Languages {
  [K in LanguagesAcronyms]: LanguageCompleteNames;
}
export interface ProjectStatus {
  total: number;
  percentage: number;
}
export interface HomeDataType {
  total_project?: { total: number; percentage: number };
  total_task?: { total: number; percentage: number };
  total_expense?: { total: number; percentage: number };
  total_user?: number;
  task_overview?: Record<string, number>;
  timesheet_logged?: Record<string, number>;
  project_status?: Record<string, ProjectStatus>;
  due_project?: Array<{ id: number; name: string; status: string }>;
  due_tasks?: Array<{
    id: number;
    name: string;
    project: { id: number; name: string };
    priority: string;
    taskProgress: (task: any) => { percentage: number };
  }>;
  todo?: Array<{
    id: number;
    title: string;
    is_complete: boolean;
    updateUrl: string;
    deleteUrl: string;
  }>;
}
export interface Option {
  value: string;
  label: string;
}
export interface FolderResult {
  folderKey: string;
  permission: string;
  ok: boolean;
}
export interface Allowance {
  id: number | string;
  allowance_option: string;
  title: string;
  type: string;
  amount: number;
}
export interface AllowanceOption {
  id: number | string;
  name: string;
}
export interface AllowanceBodyMethodProps {
  id: string;
  formData: any;
  submitDispatch: Dispatch;
  close: Function;
}
export interface NavLink {
  href: string;
  label: string;
}
export interface HRNavLink {
  href: string;
  label: string;
  permission?: string;
}
export interface HRSideNavProps {
  permissions?: string[];
}
export interface Project {
  id: number;
  name: string;
  createdBy: number;
}
export interface Branch {
  id: number;
  name: string;
}
export interface Department {
  id: number;
  name: string;
}
export interface Designation {
  name: string;
}
export interface Employee {
  id: string;
  name: string;
  branch?: { id: string; name: string };
  department?: Department;
  designation?: Designation;
}
export interface EmployeeAttendance extends Employee {
  date: string;
  clock_in: string;
  clock_out: string;
}
export interface Announcement {
  id: number;
  title: string;
  branch_id: number | string;
  department_id: number | string;
  start_date: string;
  end_date: string;
  description: string;
}
export interface AnnouncementFormData {
  title: string;
  branch_id: string;
  department_id: string;
  employee_id: string;
  start_date: string;
  end_date: string;
  description: string;
}
export interface IdentifiedHttpMethod<T> {
  id: string;
  url: string;
  dispatch: RcDispatch<T>;
}
export interface BranchOption {
  id: string;
  name: string;
}
export interface EmployeeOption {
  id: string;
  name: string;
}
export interface EmployeeAssetOption {
  value: string;
  label: string;
}
export interface AppraisalFormData {
  branchId: string;
  employeeId: string;
  appraisal_date: string;
  remark?: string;
}
export interface Appraisal {
  id: string;
  appraisal_date: string;
  branch?: Branch;
  employee: Employee;
  remark?: string;
  targetRating?: number;
  overallRating?: number;
}
export interface Type {
  id: number;
  name: string;
}
export interface PerformanceType {
  id: number;
  name: string;
  types: Type[];
}
export interface User {
  id: string;
  name: string;
  avatar?: string;
}
export interface Asset {
  id: string;
  name: string;
  users: User[];
  purchase_date: string;
  supported_date: string;
  amount: number;
  description?: string;
}
export interface AttendanceStatus {
  status: string;
  clock_in: string;
  clock_out: string;
}
export interface AttendanceBulkFilter {
  date: string;
  branch: string;
  department: string;
}
export interface GetAttendanceListParams {
  filterType: "monthly" | "daily";
  month?: string;
  date?: string;
  branch?: string;
  department?: string;
}

// components
export type NlHtEl = null | HTMLElement;

// events
export type RMouseEvent<T> = React.MouseEvent<T> | MouseEvent<T>;
