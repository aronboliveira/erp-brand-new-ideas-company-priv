/**
 * @file rbac-test-utils.ts
 * @description Shared utilities for RBAC testing - mock user contexts, permission checks, and API simulation
 * Plain JavaScript version for browser usage in mock pages
 * Generated: 2026-03-01
 */

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
};

// ============================================================================
// Role Templates
// ============================================================================

const allPermissions = Object.values(Permissions);

export const RoleTemplates = {
  superAdmin: {
    id: "role-sa",
    name: "Super Admin",
    permissions: allPermissions,
  },
  admin: {
    id: "role-admin",
    name: "Admin",
    permissions: allPermissions.filter(
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

/**
 * Create a mock user with the specified role
 * @param {string} role - One of: 'superAdmin', 'admin', 'hr', 'accountant', 'client', 'guest'
 * @param {Object} overrides - Optional properties to override
 * @returns {Object} Mock user object
 */
export function createMockUser(role, overrides = {}) {
  const roleTemplate = RoleTemplates[role];
  if (!roleTemplate) {
    console.warn(`Unknown role: ${role}, defaulting to guest`);
    return createMockUser("guest", overrides);
  }

  const baseUser = {
    id: `user-${role}-${Date.now()}`,
    name: `Test ${roleTemplate.name}`,
    email: `test.${role}@prestech.com.br`,
    role: roleTemplate,
    company_id: "company-1",
    is_active: true,
    isAuthenticated: role !== "guest",
    session_token: `token-${Date.now()}`,
    session_expires_at: new Date(Date.now() + 3600000), // 1 hour from now
  };
  return { ...baseUser, ...overrides };
}

// ============================================================================
// Permission Checking
// ============================================================================

/**
 * Check if user has a specific permission
 * @param {Object|null} user - Mock user object
 * @param {string} permission - Permission string to check
 * @returns {boolean}
 */
export function userCan(user, permission) {
  if (!user || !user.is_active) return false;
  if (user.role.permissions.includes(Permissions.SA)) return true; // Super admin bypass
  return user.role.permissions.includes(permission);
}

/**
 * Check if user has any of the specified permissions
 * @param {Object|null} user - Mock user object
 * @param {string[]} permissions - Array of permission strings
 * @returns {boolean}
 */
export function userCanAny(user, permissions) {
  return permissions.some(p => userCan(user, p));
}

/**
 * Check if user has all of the specified permissions
 * @param {Object|null} user - Mock user object
 * @param {string[]} permissions - Array of permission strings
 * @returns {boolean}
 */
export function userCanAll(user, permissions) {
  return permissions.every(p => userCan(user, p));
}

/**
 * Check if user session is valid
 * @param {Object|null} user - Mock user object
 * @returns {boolean}
 */
export function isSessionValid(user) {
  if (!user || !user.session_expires_at) return false;
  return new Date() < user.session_expires_at;
}

// ============================================================================
// Mock API Response Factory
// ============================================================================

/**
 * Create a success API response
 * @param {*} data - Response data
 * @param {number} status - HTTP status code
 * @returns {Object} API response object
 */
export function createSuccessResponse(data, status = 200) {
  return {
    status,
    statusText: getStatusText(status),
    data,
    headers: { "content-type": "application/json" },
    ok: true,
  };
}

/**
 * Create an error API response
 * @param {number} status - HTTP status code
 * @param {string} message - Error message
 * @param {Object} errors - Field-level errors
 * @returns {Object} API response object
 */
export function createErrorResponse(status, message, errors = null) {
  return {
    status,
    statusText: getStatusText(status),
    data: { message, errors },
    headers: { "content-type": "application/json" },
    ok: false,
  };
}

export function create401Response() {
  return createErrorResponse(401, "Unauthenticated. Please log in.");
}

export function create403Response(permission) {
  return createErrorResponse(
    403,
    permission
      ? `You do not have permission to ${permission}.`
      : "You do not have permission to perform this action.",
  );
}

export function create404Response(resource = "Resource") {
  return createErrorResponse(404, `${resource} not found.`);
}

export function create422Response(errors) {
  return createErrorResponse(422, "The given data was invalid.", errors);
}

export function create500Response(message = "Internal server error.") {
  return createErrorResponse(500, message);
}

function getStatusText(status) {
  const statusTexts = {
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

/**
 * Create a mock fetch function
 * @param {Object} config - Configuration options
 * @param {number} config.delay - Simulated network delay in ms
 * @param {boolean} config.requireAuth - Whether to require authentication
 * @param {Object} config.defaultResponse - Default response data
 * @param {Object} config.routes - Route-specific handlers
 * @returns {Function} Mock fetch function
 */
export function createMockFetch(config = {}) {
  const {
    delay = 100,
    requireAuth = false,
    defaultResponse = { status: 200, data: {} },
    routes = {},
    user = null,
  } = config;

  return async function mockFetch(url, options = {}) {
    // Simulate network delay
    await new Promise(resolve => setTimeout(resolve, delay));

    // Check authentication
    if (requireAuth && (!user || !user.isAuthenticated)) {
      return {
        ok: false,
        status: 401,
        statusText: "Unauthorized",
        json: async () => ({ message: "Unauthenticated. Please log in." }),
        text: async () =>
          JSON.stringify({ message: "Unauthenticated. Please log in." }),
      };
    }

    // Check for route-specific handler
    for (const [pattern, handler] of Object.entries(routes)) {
      if (url.includes(pattern)) {
        const response =
          typeof handler === "function" ? handler(url, options) : handler;
        return {
          ok: response.status >= 200 && response.status < 300,
          status: response.status || 200,
          statusText: getStatusText(response.status || 200),
          json: async () => response.data || response,
          text: async () => JSON.stringify(response.data || response),
        };
      }
    }

    // Default response
    return {
      ok: defaultResponse.status >= 200 && defaultResponse.status < 300,
      status: defaultResponse.status,
      statusText: getStatusText(defaultResponse.status),
      json: async () => defaultResponse.data,
      text: async () => JSON.stringify(defaultResponse.data),
    };
  };
}

// ============================================================================
// DOM Helpers for Testing
// ============================================================================

let __mockUser = null;

/**
 * Set the current user context
 * @param {Object|null} user - Mock user object
 */
export function setUserContext(user) {
  __mockUser = user;
  if (typeof window !== "undefined") {
    window.__mockUser = user;
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

/**
 * Get the current user context
 * @returns {Object|null}
 */
export function getUserContext() {
  if (typeof window !== "undefined" && window.__mockUser) {
    return window.__mockUser;
  }
  return __mockUser;
}

/**
 * Hide DOM elements that require permissions the user doesn't have
 * @param {Object|null} user - Mock user object
 */
export function hideElementsWithoutPermission(user) {
  if (typeof document === "undefined") return;

  // Handle data-permission attribute
  document.querySelectorAll("[data-permission]").forEach(el => {
    const required = el.dataset.permission;
    if (required && !userCan(user, required)) {
      el.style.display = "none";
      el.setAttribute("aria-hidden", "true");
    }
  });

  // Handle data-permissions-any attribute (comma-separated)
  document.querySelectorAll("[data-permissions-any]").forEach(el => {
    const required =
      el.dataset.permissionsAny?.split(",").map(s => s.trim()) || [];
    if (!userCanAny(user, required)) {
      el.style.display = "none";
      el.setAttribute("aria-hidden", "true");
    }
  });

  // Handle data-permissions-all attribute (comma-separated)
  document.querySelectorAll("[data-permissions-all]").forEach(el => {
    const required =
      el.dataset.permissionsAll?.split(",").map(s => s.trim()) || [];
    if (!userCanAll(user, required)) {
      el.style.display = "none";
      el.setAttribute("aria-hidden", "true");
    }
  });

  // Handle data-role attribute (exact match required)
  document.querySelectorAll("[data-role]").forEach(el => {
    const requiredRole = el.dataset.role;
    // Skip body element which uses data-role differently
    if (el === document.body) return;
    if (
      requiredRole &&
      user?.role.name.toLowerCase() !== requiredRole.toLowerCase()
    ) {
      el.style.display = "none";
      el.setAttribute("aria-hidden", "true");
    }
  });
}

/**
 * Show access denied message in a container
 * @param {HTMLElement} container - Container element
 * @param {string} permission - Optional permission that was denied
 */
export function showAccessDeniedMessage(container, permission) {
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

/**
 * Create a test runner for inline page tests
 * @returns {Object} Test runner with assertion methods
 */
export function createTestRunner() {
  const results = [];

  return {
    /**
     * Run a test
     * @param {string} name - Test name
     * @param {Function} fn - Test function (can be async)
     */
    test: async (name, fn) => {
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

    /**
     * Assert a condition is true
     * @param {boolean} condition - Condition to check
     * @param {string} message - Error message if fails
     */
    assert: (condition, message) => {
      if (!condition) throw new Error(`Assertion failed: ${message}`);
    },

    /**
     * Assert two values are equal
     * @param {*} actual - Actual value
     * @param {*} expected - Expected value
     * @param {string} message - Optional error message
     */
    assertEqual: (actual, expected, message) => {
      if (actual !== expected) {
        throw new Error(
          message ||
            `Expected ${JSON.stringify(expected)}, got ${JSON.stringify(actual)}`,
        );
      }
    },

    /**
     * Assert value is not null or undefined
     * @param {*} value - Value to check
     * @param {string} message - Optional error message
     */
    assertNotNull: (value, message) => {
      if (value === null || value === undefined) {
        throw new Error(message || `Expected non-null value, got ${value}`);
      }
    },

    /**
     * Assert an element is visible
     * @param {string} selector - CSS selector
     */
    assertVisible: selector => {
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

    /**
     * Assert an element is hidden
     * @param {string} selector - CSS selector
     */
    assertHidden: selector => {
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

    /**
     * Get all test results
     * @returns {Array}
     */
    getResults: () => results,

    /**
     * Get test summary
     * @returns {Object}
     */
    getSummary: () => ({
      total: results.length,
      passed: results.filter(r => r.passed).length,
      failed: results.filter(r => !r.passed).length,
      duration: results.reduce((sum, r) => sum + r.duration, 0),
    }),
  };
}

// ============================================================================
// Export for global access in browser
// ============================================================================

if (typeof window !== "undefined") {
  window.RBACTestUtils = {
    Permissions,
    RoleTemplates,
    createMockUser,
    userCan,
    userCanAny,
    userCanAll,
    isSessionValid,
    createSuccessResponse,
    createErrorResponse,
    create401Response,
    create403Response,
    create404Response,
    create422Response,
    create500Response,
    createMockFetch,
    setUserContext,
    getUserContext,
    hideElementsWithoutPermission,
    showAccessDeniedMessage,
    createTestRunner,
  };
}
