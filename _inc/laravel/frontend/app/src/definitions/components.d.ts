import {
  Allowance,
  AllowanceOption,
  EmployeeAttendance,
  EmployeeAssetOption,
  FolderResult,
  HomeDataType,
  NlHtEl,
  Option,
  RMouseEvent,
  RcDispatch,
} from "./helpers";
import { Dispatch, ReactNode } from "react";

export interface Parent {
  children: ReactNode;
  addChildren?: ReactNode;
}

export interface Emitter<T> {
  dispatch: RcDispatch<T>;
  state?: T;
}

export interface AdminLayoutProps extends Parent {
  title?: string;
  breadcrumb?: ReactNode;
  actionBtn?: ReactNode;
  settings: {
    title_text?: string;
    meta_title?: string;
    meta_desc?: string;
    meta_image?: string;
    SITE_RTL?: boolean;
    cust_darklayout?: boolean;
    color?: string;
    company_favicon?: string;
    logo?: string;
    APP_URL: string;
    csrf_token: string;
    chatifyPath: string;
  };
}

export interface AllowanceFormModalProps {
  open: boolean;
  onClose: () => void;
  employeeId: number | string;
  allowanceOptions: Option[];
  allowanceTypes: Option[];
  onSubmitSuccess?: (data: any) => void;
}

export interface AllowanceOptionModalProps {
  open: boolean;
  onClose: () => void;
  onSubmitSuccess?: (data: any) => void;
}

export interface AllowanceOptionUpdateModalProps {
  open: boolean;
  onClose: () => void;
  allowanceOption: AllowanceOption;
  onSubmitSuccess?: (data: any) => void;
}

export interface AllowanceUpdateModalProps {
  open: boolean;
  onClose: () => void;
  allowance: Allowance;
  allowanceOptions: Option[];
  allowanceTypes: Option[];
  onSubmitSuccess?: (data: any) => void;
}

export interface AnnouncementFormProps {
  onCancel: () => void;
  onSubmitSuccess: () => void;
}

export interface AnnouncementUpdateFormProps {
  announcement: Announcement;
  branch: Record<string, string>;
  departments: Record<string, string>;
  plan: { chatgpt: number };
}

export interface AssetEditModalProps {
  employeeOptions: Record<string, string>;
  initialData: {
    employee_id: string[];
    name: string;
    amount: number;
    purchase_date: string;
    supported_date: string;
    description: string;
  };
  onClose: () => void;
  onSubmit: (data: AssetEditModalProps["initialData"]) => void;
  aiEnabled?: boolean;
}

export interface AssetsCreateProps {
  open: boolean;
  onClose: () => void;
  employeeOptions: EmployeeOption[];
  plan: { chatgpt: number };
}

export interface AssetsPageProps {
  assets: Asset[];
  userCanCreate: boolean;
  userCanEdit: boolean;
  userCanDelete: boolean;
  dateFormat: (date: string) => string;
  priceFormat: (price: number) => string;
  avatarBasePath: string;
}

export interface AttendanceBulkProps {
  branchOptions: Record<string, string>;
  departmentOptions: Record<string, string>;
}

export interface AttendanceCreateProps {
  employees: EmployeeAssetOption[];
  onSuccess?: () => void;
}

export interface AttendanceEditProps {
  EmployeeAttendance: EmployeeAttendance;
  employees: EmployeeAssetOption[];
  onClose: () => void;
  onSuccess: () => void;
}

export interface AttendanceImportModalProps {
  open: boolean;
  onClose: () => void;
  sampleUrl: string;
}

export interface AuthLayoutProps extends Parent {
  metaTitle?: string;
  metaDescription?: string;
  metaImage?: string;
}

export interface ContractLayoutProps extends Parent {}

export interface CookieConsentProps {
  cookieTitle: string;
  cookieDescription: string;
  strictlyCookieTitle: string;
  strictlyCookieDescription: string;
  moreInfoDescription: string;
  contactUrl: string;
}

export interface LayoutProps extends Parent {
  actionButton?: ReactNode;
}

export interface ManageAnnouncementProps {
  announcements: Announcement[];
  branchOptions: Option[];
  canCreate: boolean;
  canEdit: boolean;
  canDelete: boolean;
}

export interface PerformanceRatingsProps {
  performanceTypes: PerformanceType[];
  ratings: Record<number, number>;
  rating: Record<number, number>;
}

export interface ProjectDashboardProps {
  userType: string;
  homeData: HomeDataType;
}

export interface ResetPasswordProps {
  token: string;
}

export interface ShareProjectLayoutProps extends Parent {
  params: { id: string };
}

export interface SystemCheckData {
  requiredPython: string;
  pythonAllowed: boolean;
  pythonCurrentVersion: string;
  folderResults: FolderResult[];
  hasError: boolean;
}

export interface AnchorComponentProps {
  navLinks: string[];
  anchorEl: NlHtEl;
  handleMenuClose: (event: RMouseEvent<HTMLElement>) => void;
}
