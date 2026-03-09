/**
 * @fileoverview TypeScript version of tests/frontend/js/pages/utils/rbac-test-utils.js
 * @generated from original JavaScript - manual review recommended
 * @module rbac-test-utils
 */

/**
 * Shared RBAC test utilities for mock pages.
 * ES module — served via http-server (port 3000) during Playwright tests.
 *
 * Consumed by:
 *   tests/frontend/js/pages/mocks/rbac/{super-admin,admin,hr,accountant,client}.html
 */

// ---------------------------------------------------------------------------
// Type Definitions
// ---------------------------------------------------------------------------
export interface RoleTemplate {
  id: string;
  name: string;
  permissions: string[];
}

export interface MockUser {
  id: string;
  name: string;
  email: string;
  role: RoleTemplate;
  permissions: string[];
  company_id: string;
  is_active: boolean;
  isAuthenticated: boolean;
  session_token: string;
  session_expires_at: Date;
  [key: string]: unknown;
}

export interface TestResult {
  name: string;
  passed: boolean;
  message: string;
  duration: number;
}

export interface TestSummary {
  total: number;
  passed: number;
  failed: number;
  duration: number;
}

// Note: Window augmentation is defined in ../utils/rbac-test-utils.ts
// This file uses the same interface but avoids redeclaring the global

// ---------------------------------------------------------------------------
// Permission constants
// ---------------------------------------------------------------------------
export const Permissions = {
  // Role shorthands
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

  // Dashboard access — long-form (guest.html style)
  SHW_ACC_DSB: "show account dashboard",
  SHW_CRM_DSB: "show crm dashboard",
  SHW_HRM_DSB: "show hrm dashboard",
  SHW_POS_DSB: "show pos dashboard",
  SHW_PRJ_DSB: "show project dashboard",
  MNG_CLT_DSB: "manage client dashboard",
  MNG_SA_DSB: "manage super admin dashboard",

  // Dashboard access — short-form (used by failing pages)
  SH_ADMIN_DB: "show admin dashboard",
  SH_ACC_DB: "show account dashboard",
  SH_HRM_DB: "show hrm dashboard",
  SH_POS_DB: "show pos dashboard",
  SH_PRJ_DB: "show project dashboard",
  SH_CLIENT_DB: "show client dashboard",
  MNG_SA_DB: "manage super admin dashboard",

  // User management
  MNG_USER: "manage user",
  MNG_USR: "manage user",
  CR_USER: "create user",
  ED_USER: "edit user",
  DEL_USER: "delete user",

  // Employee / HRM
  MNG_EMP: "manage employee",
  MNG_CLT: "manage client",
  CR_CLT: "create client",
  MNG_CST: "manage customer",
  MNG_VD: "manage vendor",

  // Finance
  MNG_BIL: "manage bill",
  MNG_BL: "manage bill",
  MNG_INV: "manage invoice",
  CR_INV: "create invoice",
  MNG_RVN: "manage revenue",
  MNG_PMT: "manage payment",
  MNG_BACC: "manage bank account",
  MNG_BTF: "manage bank transfer",
  MNG_TRT: "manage transaction",
  MNG_COA: "manage chart of account",
  MNG_JNL: "manage journal entry",
  MNG_CRD: "manage credit note",
  MNG_DBT: "manage debit note",
  MNG_RPT: "manage report",
  MNG_EXP: "manage expense",

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

  // Projects / CRM
  MNG_PRJ: "manage project",
  MNG_PRJ_TSK: "manage project task",
  MNG_TS: "manage timesheet",
  MNG_LD: "manage lead",
  MNG_DL: "manage deal",

  // POS
  MNG_POS: "manage pos",
  MNG_OD: "manage order",

  // Other
  MNG_PPS: "manage proposal",
  MNG_CTC: "manage contract",
  MNG_PRC: "manage purchase",
  MNG_RPRT: "manage report",
  VW_CRM: "view crm activity",

  // Settings
  MNG_SYS_ST: "manage system settings",
  MNG_CPN_SET: "manage company settings",
};

// ---------------------------------------------------------------------------
// Role templates — one per mock page
// ---------------------------------------------------------------------------
const RoleTemplates = {
  superAdmin: {
    id: "role-super-admin",
    name: "Super Admin",
    permissions: [Permissions.SA],
  },
  admin: {
    id: "role-admin",
    name: "Admin",
    permissions: [
      "show admin dashboard",
      "show hrm dashboard",
      "show account dashboard",
      "manage company settings",
      "manage user",
      "manage role",
    ],
  },
  hr: {
    id: "role-hr",
    name: "HR",
    permissions: [
      "show hrm dashboard",
      "manage employee",
      "manage attendance",
      "manage leave",
    ],
  },
  accountant: {
    id: "role-accountant",
    name: "Accountant",
    permissions: [
      "show account dashboard",
      "manage invoice",
      "manage bill",
      "manage expense",
    ],
  },
  client: {
    id: "role-client",
    name: "Client",
    permissions: ["show client dashboard"],
  },
  guest: {
    id: "role-guest",
    name: "Guest",
    permissions: [],
  },
};

type RoleKey = keyof typeof RoleTemplates;

// ---------------------------------------------------------------------------
// createMockUser(role, overrides?)
// ---------------------------------------------------------------------------
export function createMockUser(
  role: RoleKey | string,
  overrides?: Partial<MockUser>,
): MockUser {
  const tpl = RoleTemplates[role as RoleKey] || RoleTemplates.guest,
    base: MockUser = {
      id: "user-" + role + "-" + Date.now(),
      name: "Test " + tpl.name,
      email: "test." + role.toLowerCase() + "@prestech.com.br",
      role: tpl,
      permissions: tpl.permissions,
      company_id: "company-1",
      is_active: true,
      isAuthenticated: role !== "guest",
      session_token: "token-" + Date.now(),
      session_expires_at: new Date(Date.now() + 3600000),
    };
  if (overrides)
    for (const k in overrides)
      (base as Record<string, unknown>)[k] = overrides[k as keyof MockUser];
  return base;
}

// ---------------------------------------------------------------------------
// userCan(user, permission)
// Super-admin shortcut: if user holds SA permission, all checks pass.
// ---------------------------------------------------------------------------
export function userCan(user: MockUser | null, permission: string): boolean {
  if (!user?.is_active) return false;
  if (user.role.permissions.indexOf(Permissions.SA) !== -1) return true;
  return user.role.permissions.indexOf(permission) !== -1;
}

// ---------------------------------------------------------------------------
// setUserContext(user)
// Stores session data in localStorage / window.__mockUser.
// ---------------------------------------------------------------------------
export function setUserContext(user: MockUser | null): void {
  window.__mockUser = user;
  if (user) {
    localStorage.setItem("auth_token", user.session_token ?? "");
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

// ---------------------------------------------------------------------------
// hideElementsWithoutPermission(user)
// Hides [data-permission] elements the user lacks and [data-role] elements
// (except <body>) that don't match the user's role.
// ---------------------------------------------------------------------------
export function hideElementsWithoutPermission(user: MockUser | null): void {
  document.querySelectorAll("[data-permission]").forEach(function (
    el: Element,
  ) {
    const htmlEl = el as HTMLElement;
    const req = htmlEl.getAttribute("data-permission");
    if (req && !userCan(user, req)) {
      htmlEl.style.display = "none";
      htmlEl.setAttribute("aria-hidden", "true");
    }
  });
  document.querySelectorAll("[data-role]").forEach(function (el: Element) {
    const htmlEl = el as HTMLElement;
    if (htmlEl === document.body) return;
    const reqRole = htmlEl.getAttribute("data-role");
    if (reqRole && user?.role.name.toLowerCase() !== reqRole.toLowerCase()) {
      htmlEl.style.display = "none";
      htmlEl.setAttribute("aria-hidden", "true");
    }
  });
}

// ---------------------------------------------------------------------------
// createTestRunner()
// Returns a lightweight test-runner compatible with window.runRbacTests().
// ---------------------------------------------------------------------------
export function createTestRunner() {
  const results: TestResult[] = [];
  return {
    test: function (name: string, fn: (...args: unknown[]) => unknown) {
      const start = performance.now();
      try {
        const result = fn() as { then?: (fn: () => void) => Promise<unknown> };
        if (result && typeof result.then === "function") {
          return result
            .then(function (): void {
              results.push({
                name: name,
                passed: true,
                message: "Passed",
                duration: performance.now() - start,
              });
            })
            .catch(function (err: Error) {
              results.push({
                name: name,
                passed: false,
                message: err.message || String(err),
                duration: performance.now() - start,
              });
            });
        }
        results.push({
          name: name,
          passed: true,
          message: "Passed",
          duration: performance.now() - start,
        });
        return Promise.resolve();
      } catch (err) {
        const error = err as Error;
        results.push({
          name: name,
          passed: false,
          message: error.message || String(err),
          duration: performance.now() - start,
        });
        return Promise.resolve();
      }
    },
    assert: function (cond: boolean, msg: string): void {
      if (!cond) throw new Error("Assertion failed: " + msg);
    },
    assertEqual: function (a: unknown, b: unknown, msg?: string): void {
      if (a !== b)
        throw new Error(
          msg ?? "Expected " + JSON.stringify(b) + ", got " + JSON.stringify(a),
        );
    },
    assertVisible: function (sel: string): void {
      const el = document.querySelector(sel);
      if (!el) throw new Error("Element not found: " + sel);
      const s = window.getComputedStyle(el);
      if (
        s.display === "none" ||
        s.visibility === "hidden" ||
        s.opacity === "0"
      )
        throw new Error("Element not visible: " + sel);
    },
    assertHidden: function (sel: string): void {
      const el = document.querySelector(sel);
      if (!el) return; // element absent → treated as hidden
      const s = window.getComputedStyle(el);
      if (
        s.display !== "none" &&
        s.visibility !== "hidden" &&
        s.opacity !== "0"
      )
        throw new Error("Element should be hidden: " + sel);
    },
    getResults: function (): TestResult[] {
      return results;
    },
    getSummary: function (): TestSummary {
      return {
        total: results.length,
        passed: results.filter(function (r) {
          return r.passed;
        }).length,
        failed: results.filter(function (r) {
          return !r.passed;
        }).length,
        duration: results.reduce(function (s: number, r) {
          return s + r.duration;
        }, 0),
      };
    },
  };
}
