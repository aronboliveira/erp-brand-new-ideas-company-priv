/**
 * @file rbac-test-utils.ts
 * @description Shared utilities for RBAC testing - mock user contexts, permission checks, and API simulation
 * Generated: 2026-03-01
 */

// ============================================================================
// Types & Interfaces
// ============================================================================

export interface Permission {
  name: string;
  granted: boolean;
}

export interface UserRole {
  id: string;
  name: string;
  permissions: string[];
}

export interface MockUser {
  id: string;
  name: string;
  email: string;
  role: UserRole;
  company_id: string;
  is_active: boolean;
  session_token?: string;
  session_expires_at?: Date;
}

export interface ApiResponse<T = unknown> {
  status: number;
  statusText: string;
  data: T;
  headers: Record<string, string>;
  ok: boolean;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  code?: string;
}

export interface MockApiConfig {
  delay?: number; // Simulated network delay in ms
  shouldFail?: boolean;
  failureCode?: number;
  failureMessage?: string;
  networkError?: boolean;
  timeout?: boolean;
}

// ============================================================================
// Permission Constants (matching PHP PermissionsConstants)
// ============================================================================

export const Permissions = {
  // Roles
  SA: "super admin",
  ADM: "admin",
  ACT: "accountant",
  HR: "hr",
  CL: "client",
  CT: "customer",
  VD: "vendor",
  CPN: "company",

  // Permission management
  MNG_PERM: "manage permission",
  CR_PERM: "create permission",
  ED_PERM: "edit permission",
  DEL_PERM: "delete permission",

  // Role management
  MNG_ROLE: "manage role",
  CR_ROLE: "create role",
  ED_ROLE: "edit role",
  DEL_ROLE: "delete role",

  // Dashboards
  SHW_ACC_DSB: "show account dashboard",
  SHW_CRM_DSB: "show crm dashboard",
  SHW_HRM_DSB: "show hrm dashboard",
  SHW_POS_DSB: "show pos dashboard",
  SHW_PRJ_DSB: "show project dashboard",
  MNG_CLT_DSB: "manage client dashboard",
  MNG_SA_DSB: "manage super admin dashboard",

  // Users
  MNG_USER: "manage user",
  CR_USER: "create user",
  ED_USER: "edit user",
  DEL_USER: "delete user",

  // Employees
  MNG_EMP: "manage employee",

  // Clients/Customers/Vendors
  MNG_CLT: "manage client",
  CR_CLT: "create client",
  MNG_CST: "manage customer",
  MNG_VD: "manage vendor",

  // Financial
  MNG_BIL: "manage bill",
  MNG_INV: "manage invoice",
  MNG_RVN: "manage revenue",
  MNG_PMT: "manage payment",
  MNG_BACC: "manage bank account",
  MNG_BTF: "manage bank transfer",
  MNG_TRT: "manage transaction",
  MNG_COA: "manage chart of account",
  MNG_JNL: "manage journal entry",
  MNG_CRD: "manage credit note",
  MNG_DBT: "manage debit note",

  // Reports
  MNG_RPT: "manage report",
  BLC_RPT: "balance sheet report",
  LDG_RPT: "ledge report",
  TRL_RPT: "trial balance report",
  BIL_RPT: "bill report",
  EXP_RPT: "expense report",
  INC_RPT: "income report",
  IE_RPT: "income vs expense report",
  INV_RPT: "invoice report",
  LP_RPT: "loss & profit report",
  STT_RPT: "statement report",
  STK_RPT: "stock report",
  TAX_RPT: "tax report",

  // HRM
  MNG_ATD: "manage attendance",
  CR_ATD: "create attendance",
  MNG_LV: "manage leave",
  MNG_PSL: "manage payslip",
  CR_PSL: "create payslip",
  MNG_SSL: "manage set salary",
  MNG_APR: "manage appraisal",
  MNG_AWD: "manage award",
  MNG_TRM: "manage termination",
  MNG_RSG: "manage resignation",
  MNG_PRM: "manage promotion",
  MNG_TRF: "manage transfer",
  MNG_TRV: "manage travel",
  MNG_CPT: "manage complaint",
  MNG_WRN: "manage warning",
  MNG_TNG: "manage training",
  MNG_TNR: "manage trainer",
  MNG_HLD: "manage holiday",

  // Projects
  MNG_PRJ: "manage project",
  MNG_PRJ_TSK: "manage project task",
  MNG_PRJ_TSK_STG: "manage project task stage",
  MNG_TS: "manage timesheet",

  // CRM
  MNG_LD: "manage lead",
  MNG_LD_ST: "manage lead stage",
  MNG_DL: "manage deal",
  MNG_PPL: "manage pipeline",
  MNG_SRC: "manage source",
  MNG_LB: "manage label",
  MNG_ST: "manage stage",
  VW_CRM: "view crm activity",

  // Products & POS
  MNG_PRD_SV: "manage product & service",
  MNG_POS: "manage pos",
  MNG_OD: "manage order",
  CR_BC: "create barcode",

  // Proposals & Contracts
  MNG_PPS: "manage proposal",
  MNG_CTC: "manage contract",

  // Purchases
  MNG_PRC: "manage purchase",

  // Support & Bugs
  MNG_BUG_RPT: "manage bug report",
  MNG_BUG_STT: "manage bug status",

  // Jobs & Recruitment
  MNG_JB: "manage job",
  CR_JB: "create job",
  MNG_JB_APL: "manage job application",
  MNG_JST: "manage job stage",
  SHW_CRR: "show career",
  CR_ITV_SCHD: "create interview schedule",
  SHW_ITV_SCHD: "show interview schedule",

  // Settings & System
  MNG_SYS_ST: "manage system settings",
  MNG_CPN_SET: "manage company settings",
  MNG_PRT: "manage print settings",
  ED_WHK: "edit webhook",
  DEL_WHK: "delete webhook",

  // Plans
  MNG_PL: "manage plan",
  CR_PL: "create plan",
  ED_PL: "edit plan",
  MNG_CP_PL: "manage company plan",
  AC_PL_RQ: "accept plan request",
  VW_PL_RQ: "view plan request",
  VW_PL_DT: "view plan details",

  // Assets
  MNG_AST: "manage assets",
  CRT_AST: "create assets",
  ED_AST: "edit assets",
  VIW_AST: "view assets",
  DEL_AST: "delete assets",

  // Documents & Announcements
  MNG_DOC: "manage document",
  MNG_ANC: "manage announcement",
  MNG_EVT: "manage event",
  MNG_MT: "manage meeting",

  // Goals & Indicators
  MNG_GL: "manage goal",
  MNG_GTR: "manage goal tracking",
  MNG_IND: "manage indicator",

  // Misc
  MNG_CPN_PL: "manage company policy",
  MNG_FM_BD: "manage form builder",
  MNG_AI_TPL: "manage ai template",
  MNG_LP: "manage landing page",
  MNG_TT: "manage testimonials",
  MNG_CPN: "manage coupon",
  MNG_WRH: "manage warehouse",
  MNG_CT_CAT: "manage constant category",
  MNG_CT_TX: "manage constant tax",
  MNG_CT_UNT: "manage constant unit",
  MNG_CT_PAY: "manage constant payment method",
  MNG_CT_CST_FD: "manage constant custom field",
  MNG_CST_QT: "manage custom question",
} as const;

// ============================================================================
// Role Templates
// ============================================================================

export const RoleTemplates: Record<string, UserRole> = {
  superAdmin: {
    id: "role-sa",
    name: "Super Admin",
    permissions: Object.values(Permissions),
  },
  admin: {
    id: "role-admin",
    name: "Admin",
    permissions: Object.values(Permissions).filter(
      p => p !== Permissions.MNG_SA_DSB && p !== Permissions.MNG_SYS_ST,
    ),
  },
  hr: {
    id: "role-hr",
    name: "HR",
    permissions: [
      Permissions.SHW_HRM_DSB,
      Permissions.MNG_EMP,
      Permissions.MNG_ATD,
      Permissions.CR_ATD,
      Permissions.MNG_LV,
      Permissions.MNG_PSL,
      Permissions.CR_PSL,
      Permissions.MNG_SSL,
      Permissions.MNG_APR,
      Permissions.MNG_AWD,
      Permissions.MNG_TRM,
      Permissions.MNG_RSG,
      Permissions.MNG_PRM,
      Permissions.MNG_TRF,
      Permissions.MNG_TRV,
      Permissions.MNG_CPT,
      Permissions.MNG_WRN,
      Permissions.MNG_TNG,
      Permissions.MNG_TNR,
      Permissions.MNG_HLD,
      Permissions.MNG_JB,
      Permissions.CR_JB,
      Permissions.MNG_JB_APL,
      Permissions.MNG_JST,
      Permissions.SHW_CRR,
      Permissions.CR_ITV_SCHD,
      Permissions.SHW_ITV_SCHD,
    ],
  },
  accountant: {
    id: "role-accountant",
    name: "Accountant",
    permissions: [
      Permissions.SHW_ACC_DSB,
      Permissions.MNG_BIL,
      Permissions.MNG_INV,
      Permissions.MNG_RVN,
      Permissions.MNG_PMT,
      Permissions.MNG_BACC,
      Permissions.MNG_BTF,
      Permissions.MNG_TRT,
      Permissions.MNG_COA,
      Permissions.MNG_JNL,
      Permissions.MNG_CRD,
      Permissions.MNG_DBT,
      Permissions.MNG_RPT,
      Permissions.BLC_RPT,
      Permissions.LDG_RPT,
      Permissions.TRL_RPT,
      Permissions.BIL_RPT,
      Permissions.EXP_RPT,
      Permissions.INC_RPT,
      Permissions.IE_RPT,
      Permissions.INV_RPT,
      Permissions.LP_RPT,
      Permissions.STT_RPT,
      Permissions.TAX_RPT,
    ],
  },
  client: {
    id: "role-client",
    name: "Client",
    permissions: [
      Permissions.MNG_CLT_DSB,
      Permissions.SHW_PRJ_DSB,
      Permissions.MNG_PRJ,
    ],
  },
  guest: {
    id: "role-guest",
    name: "Guest",
    permissions: [],
  },
};

// ============================================================================
// Mock User Factory
// ============================================================================

export function createMockUser(
  role: keyof typeof RoleTemplates,
  overrides: Partial<MockUser> = {},
): MockUser {
  const roleTemplate = RoleTemplates[role];
  const baseUser: MockUser = {
    id: `user-${role}-${Date.now()}`,
    name: `Test ${roleTemplate.name}`,
    email: `test.${role}@prestech.com.br`,
    role: roleTemplate,
    company_id: "company-1",
    is_active: true,
    session_token: `token-${Date.now()}`,
    session_expires_at: new Date(Date.now() + 3600000), // 1 hour from now
  };
  return { ...baseUser, ...overrides };
}

// ============================================================================
// Permission Checking
// ============================================================================

export function userCan(user: MockUser | null, permission: string): boolean {
  if (!user || !user.is_active) return false;
  if (user.role.permissions.includes(Permissions.SA)) return true; // Super admin bypass
  return user.role.permissions.includes(permission);
}

export function userCanAny(
  user: MockUser | null,
  permissions: string[],
): boolean {
  return permissions.some(p => userCan(user, p));
}

export function userCanAll(
  user: MockUser | null,
  permissions: string[],
): boolean {
  return permissions.every(p => userCan(user, p));
}

export function isSessionValid(user: MockUser | null): boolean {
  if (!user || !user.session_expires_at) return false;
  return new Date() < user.session_expires_at;
}

// ============================================================================
// Mock API Response Factory
// ============================================================================

export function createSuccessResponse<T>(
  data: T,
  status = 200,
): ApiResponse<T> {
  return {
    status,
    statusText: getStatusText(status),
    data,
    headers: { "content-type": "application/json" },
    ok: true,
  };
}

export function createErrorResponse(
  status: number,
  message: string,
  errors?: Record<string, string[]>,
): ApiResponse<ApiError> {
  return {
    status,
    statusText: getStatusText(status),
    data: { message, errors },
    headers: { "content-type": "application/json" },
    ok: false,
  };
}

export function create401Response(): ApiResponse<ApiError> {
  return createErrorResponse(401, "Unauthenticated. Please log in.");
}

export function create403Response(permission?: string): ApiResponse<ApiError> {
  return createErrorResponse(
    403,
    permission
      ? `You do not have permission to ${permission}.`
      : "You do not have permission to perform this action.",
  );
}

export function create404Response(
  resource = "Resource",
): ApiResponse<ApiError> {
  return createErrorResponse(404, `${resource} not found.`);
}

export function create422Response(
  errors: Record<string, string[]>,
): ApiResponse<ApiError> {
  return createErrorResponse(422, "The given data was invalid.", errors);
}

export function create500Response(
  message = "Internal server error.",
): ApiResponse<ApiError> {
  return createErrorResponse(500, message);
}

function getStatusText(status: number): string {
  const statusTexts: Record<number, string> = {
    200: "OK",
    201: "Created",
    204: "No Content",
    400: "Bad Request",
    401: "Unauthorized",
    403: "Forbidden",
    404: "Not Found",
    422: "Unprocessable Entity",
    500: "Internal Server Error",
    502: "Bad Gateway",
    503: "Service Unavailable",
  };
  return statusTexts[status] || "Unknown";
}

// ============================================================================
// Mock Fetch Handler
// ============================================================================

export type MockFetchHandler = (
  url: string,
  options?: RequestInit,
) => Promise<ApiResponse<unknown>>;

export function createMockFetch(
  user: MockUser | null,
  handlers: Record<string, MockFetchHandler>,
  config: MockApiConfig = {},
): typeof fetch {
  return async (
    input: RequestInfo | URL,
    init?: RequestInit,
  ): Promise<Response> => {
    const url = typeof input === "string" ? input : input.toString();
    const delay = config.delay ?? 50;

    // Simulate network delay
    await new Promise(resolve => setTimeout(resolve, delay));

    // Simulate network error
    if (config.networkError) {
      throw new TypeError("Failed to fetch");
    }

    // Simulate timeout
    if (config.timeout) {
      await new Promise(resolve => setTimeout(resolve, 30000));
      throw new TypeError("The operation timed out.");
    }

    // Check authentication for protected routes
    if (
      !url.includes("/login") &&
      !url.includes("/register") &&
      !url.includes("/public")
    ) {
      if (!user || !isSessionValid(user)) {
        const response = create401Response();
        return new Response(JSON.stringify(response.data), {
          status: response.status,
          statusText: response.statusText,
          headers: response.headers,
        });
      }
    }

    // Check for forced failure
    if (config.shouldFail) {
      const response = createErrorResponse(
        config.failureCode ?? 500,
        config.failureMessage ?? "Simulated failure",
      );
      return new Response(JSON.stringify(response.data), {
        status: response.status,
        statusText: response.statusText,
        headers: response.headers,
      });
    }

    // Find matching handler
    for (const [pattern, handler] of Object.entries(handlers)) {
      const regex = new RegExp(pattern);
      if (regex.test(url)) {
        const response = await handler(url, init);
        return new Response(JSON.stringify(response.data), {
          status: response.status,
          statusText: response.statusText,
          headers: response.headers,
        });
      }
    }

    // Default 404 for unhandled routes
    const response = create404Response("Endpoint");
    return new Response(JSON.stringify(response.data), {
      status: response.status,
      statusText: response.statusText,
      headers: response.headers,
    });
  };
}

// ============================================================================
// DOM Helpers for Testing
// ============================================================================

export function setUserContext(user: MockUser | null): void {
  if (typeof window !== "undefined") {
    (window as unknown as { __mockUser: MockUser | null }).__mockUser = user;
    if (user) {
      localStorage.setItem("auth_token", user.session_token || "");
      localStorage.setItem("user_role", user.role.name);
      localStorage.setItem(
        "user_permissions",
        JSON.stringify(user.role.permissions),
      );
    } else {
      localStorage.removeItem("auth_token");
      localStorage.removeItem("user_role");
      localStorage.removeItem("user_permissions");
    }
  }
}

export function getUserContext(): MockUser | null {
  if (typeof window !== "undefined") {
    return (
      (window as unknown as { __mockUser: MockUser | null }).__mockUser || null
    );
  }
  return null;
}

export function hideElementsWithoutPermission(user: MockUser | null): void {
  if (typeof document === "undefined") return;

  document.querySelectorAll<HTMLElement>("[data-permission]").forEach(el => {
    const required = el.dataset.permission;
    if (required && !userCan(user, required)) {
      el.style.display = "none";
      el.setAttribute("aria-hidden", "true");
    }
  });

  document
    .querySelectorAll<HTMLElement>("[data-permissions-any]")
    .forEach(el => {
      const required =
        el.dataset.permissionsAny?.split(",").map(s => s.trim()) || [];
      if (!userCanAny(user, required)) {
        el.style.display = "none";
        el.setAttribute("aria-hidden", "true");
      }
    });

  document
    .querySelectorAll<HTMLElement>("[data-permissions-all]")
    .forEach(el => {
      const required =
        el.dataset.permissionsAll?.split(",").map(s => s.trim()) || [];
      if (!userCanAll(user, required)) {
        el.style.display = "none";
        el.setAttribute("aria-hidden", "true");
      }
    });

  document.querySelectorAll<HTMLElement>("[data-role]").forEach(el => {
    const requiredRole = el.dataset.role;
    if (
      requiredRole &&
      user?.role.name.toLowerCase() !== requiredRole.toLowerCase()
    ) {
      el.style.display = "none";
      el.setAttribute("aria-hidden", "true");
    }
  });
}

export function showAccessDeniedMessage(
  container: HTMLElement,
  permission?: string,
): void {
  container.innerHTML = `
    <div class="access-denied" role="alert">
      <h2>Access Denied</h2>
      <p>${permission ? `You do not have the "${permission}" permission.` : "You do not have permission to access this resource."}</p>
      <a href="/dashboard">Return to Dashboard</a>
    </div>
  `;
}

// ============================================================================
// Test Assertions
// ============================================================================

export interface TestResult {
  name: string;
  passed: boolean;
  message: string;
  duration: number;
}

export function createTestRunner() {
  const results: TestResult[] = [];

  return {
    test: async (
      name: string,
      fn: () => Promise<void> | void,
    ): Promise<void> => {
      const start = performance.now();
      try {
        await fn();
        results.push({
          name,
          passed: true,
          message: "Passed",
          duration: performance.now() - start,
        });
      } catch (error) {
        results.push({
          name,
          passed: false,
          message: error instanceof Error ? error.message : String(error),
          duration: performance.now() - start,
        });
      }
    },
    assert: (condition: boolean, message: string): void => {
      if (!condition) throw new Error(`Assertion failed: ${message}`);
    },
    assertEqual: <T>(actual: T, expected: T, message?: string): void => {
      if (actual !== expected) {
        throw new Error(
          message ||
            `Expected ${JSON.stringify(expected)}, got ${JSON.stringify(actual)}`,
        );
      }
    },
    assertNotNull: <T>(
      value: T | null | undefined,
      message?: string,
    ): asserts value is T => {
      if (value === null || value === undefined) {
        throw new Error(message || `Expected non-null value, got ${value}`);
      }
    },
    assertVisible: (selector: string): void => {
      const el = document.querySelector(selector);
      if (!el) throw new Error(`Element not found: ${selector}`);
      const style = window.getComputedStyle(el);
      if (
        style.display === "none" ||
        style.visibility === "hidden" ||
        style.opacity === "0"
      ) {
        throw new Error(`Element not visible: ${selector}`);
      }
    },
    assertHidden: (selector: string): void => {
      const el = document.querySelector(selector);
      if (!el) return; // Not found = hidden
      const style = window.getComputedStyle(el);
      if (
        style.display !== "none" &&
        style.visibility !== "hidden" &&
        style.opacity !== "0"
      ) {
        throw new Error(`Element should be hidden: ${selector}`);
      }
    },
    getResults: () => results,
    getSummary: () => ({
      total: results.length,
      passed: results.filter(r => r.passed).length,
      failed: results.filter(r => !r.passed).length,
      duration: results.reduce((sum, r) => sum + r.duration, 0),
    }),
  };
}
