/**
 * @fileoverview TypeScript version of tests/e2e/security-api.spec.cjs
 * @generated from original JavaScript - manual review recommended
 * @module security-api.spec
 */
/* eslint-disable @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars, @typescript-eslint/no-var-requires */

/* global $, jQuery */
// @ts-check
const { test, expect } = require("@playwright/test");

const BASE_URL = process.env.APP_URL ?? "http://127.0.0.1:18081";

const sensitiveFinanceRoutes = [
  "/vendors/purchases/1",
  "/customers/proposals/1",
  "/invoices",
  "/bills",
  "/payments",
  "/bank_transfers",
  "/payslips",
];

test.describe("Security API severity checks", (): void => {
  test("sensitive finance routes should not be public for anonymous requests", async ({ request }) => {
    const violations = [];

    for (const path of sensitiveFinanceRoutes) {
      const res = await request.get(`${BASE_URL}${path}`, {
        maxRedirects: 0,
      });

      const code = res.status();
      if (![301, 302, 303, 307, 308, 401, 403].includes(code)) {
        violations.push({ path, status: code });
      }
    }

    expect(violations, JSON.stringify(violations)).toEqual([]);
  });

  test("framework debug and ignition endpoints should not be reachable", async ({ request }) => {
    const paths = [
      "/_debugbars/open",
      "/_debugbars/assets/javascript",
      "/_ignitions/health-check",
      "/_ignitions/update-config",
    ];
    const violations = [];

    for (const path of paths) {
      const method = path.includes("update-config") ? "post" : "get";
      const res = await request[method](`${BASE_URL}${path}`, {
        data: method === "post" ? { key: "x", value: "y" } : undefined,
        maxRedirects: 0,
      });
      const code = res.status();
      if (![401, 403, 404, 405].includes(code)) {
        violations.push({ path, status: code });
      }
    }

    expect(violations, JSON.stringify(violations)).toEqual([]);
  });

  test("mutating webhook/payment endpoints should reject missing auth and CSRF", async ({ request }) => {
    const postTargets = [
      "/payment-i-p-n",
      "/apis/stop-tracker",
      "/apis/add-tracker",
      "/vendors/imports/index",
    ];
    const violations = [];

    for (const path of postTargets) {
      const res = await request.post(`${BASE_URL}${path}`, {
        maxRedirects: 0,
        form: {
          id: "1",
          _token: "invalid",
        },
      });

      const code = res.status();
      if (![301, 302, 303, 307, 308, 401, 403, 419, 422, 429].includes(code)) {
        violations.push({ path, status: code });
      }
    }

    expect(violations, JSON.stringify(violations)).toEqual([]);
  });
});
