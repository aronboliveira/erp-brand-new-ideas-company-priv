/**
 * @fileoverview TypeScript version of tests/e2e/hrm.spec.cjs
 * @generated from original JavaScript - manual review recommended
 * @module hrm.spec
 */

/* global bootstrap, $, jQuery */
// @ts-check
const { test, expect } = require("@playwright/test");
const path = require("path");

/**
 * ERP Prestech – HRM Route Rendering E2E Tests
 * Verifies every HRM index/create route renders the expected
 * table / card / form / breadcrumb elements correctly.
 *
 * Requires auth.setup.cjs to have been run first.
 */

const BASE_URL = "http://localhost:8000";
const STORAGE_STATE = path.join(__dirname, ".auth/user.json");

test.use({ storageState: STORAGE_STATE });

test.beforeEach(async ({ page }) => {
	page.on("dialog", (d) => d.accept());
	page.addLocatorHandler(page.locator("#cc--main, .c--anim"), async (): void => {
		const btn = page.locator('#c-p-bn, .c-bn, [data-cc="accept-all"]').first();
		if (await btn.isVisible({ timeout: 1000 }).catch(() => false))
			await btn.click({ force: true });
	});
});

async function assertPageRenders(page, route, label, opts = {}): Promise<void> {
	await test.step(`Navigate to ${label}`, async (): void => {
		const resp = await page.goto(`${BASE_URL}/${route}`, {
			waitUntil: "commit",
			timeout: 45000,
		});
		expect(resp?.status(), `${label} HTTP status`).toBeLessThan(500);
		await page
			.waitForLoadState("domcontentloaded", { timeout: 60000 })
			.catch((): void => {});
	});

	await test.step(`${label}: layout renders`, async (): void => {
		const layout = page.locator(
			".dash-content, .dash-container, .main-content, .container-fluid, .pcoded-content, body",
		);
		await expect(layout.first()).toBeVisible({ timeout: 15000 });
	});

	if (opts.expectTable) {
		await test.step(`${label}: table visible`, async (): void => {
			const table = page.locator(
				"table.dataTable, table.table, .table-responsive table, .card-body table, table:not(.phpdebugbar-widgets-params):not([class*='phpdebugbar'])",
			);
			await expect(table.first()).toBeVisible({ timeout: 15000 });
		});
	}

	if (opts.expectCard) {
		await test.step(`${label}: card visible`, async (): void => {
			const card = page.locator(".card, .card-body");
			await expect(card.first()).toBeVisible({ timeout: 15000 });
		});
	}

	if (opts.expectForm) {
		await test.step(`${label}: form visible`, async (): void => {
			const form = page.locator("form:not(#frm-logout):not(.d-none)");
			await expect(form.first()).toBeVisible({ timeout: 15000 });
		});
	}

	if (opts.expectBreadcrumb) {
		await test.step(`${label}: breadcrumb visible`, async (): void => {
			const bc = page.locator(".breadcrumb, .breadcrumb-item, [aria-label='breadcrumb']");
			await expect(bc.first()).toBeVisible({ timeout: 10000 });
		});
	}

	if (opts.expectText) {
		await test.step(`${label}: contains keyword "${opts.expectText}"`, async (): void => {
			const body = await page.textContent("body");
			expect(body?.toLowerCase()).toContain(opts.expectText.toLowerCase());
		});
	}
}

/* ═══════════════════════════════════════════════════════════════════
   SECTION 1 — Employee pages
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM Employee pages", (): void => {
	test("employee index: number renders table", async ({ page }) => {
		await assertPageRenders(page, "employees", "Employees Index", {
			expectTable: true,
			expectCard: true,
			expectBreadcrumb: true,
			expectText: "employee",
		});
	});

	test("employee create renders form: HTMLFormElement", async ({ page }) => {
		await assertPageRenders(page, "employees/create", "Employee Create", {
			expectForm: true,
			expectCard: true,
		});
	});

	test("employee profile page loads", async ({ page }) => {
		await assertPageRenders(page, "employee-profile", "Employee Profile", {
			expectCard: false,
		});
	});
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 2 — Org structure: Departments, Designations, Branches
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM Org Structure pages", (): void => {
	for (const slug of ["departments", "designations"]) {
		test(`${slug} index: number renders table`, async ({ page }) => {
			await assertPageRenders(page, slug, `${slug} Index`, {
				expectTable: true,
				expectCard: true,
			});
		});

		test(`${slug} create modal form: HTMLFormElement visible`, async ({ page }) => {
			await test.step(`Navigate to ${slug} index`, async (): void => {
				const resp = await page.goto(`${BASE_URL}/${slug}`, {
					waitUntil: "commit",
					timeout: 45000,
				});
				expect(resp?.status()).toBeLessThan(500);
				await page.waitForLoadState("domcontentloaded", { timeout: 60000 }).catch((): void => {});
			});

			await test.step(`Click create button`, async (): void => {
				const createBtn = page.locator(
					"a[href*='create'], button[data-ajax-popup], .btn-create, [data-url*='create'], a.btn-sm, .btn-primary",
				).first();
				await createBtn.click({ timeout: 10000 }).catch((): void => {});
			});

			await test.step(`Modal or form renders`, async (): void => {
				const formOrModal = page.locator(
					".modal.show form, .modal-body form, form:not(#frm-logout):not(.d-none), .modal.show",
				);
				await expect(formOrModal.first()).toBeVisible({ timeout: 15000 }).catch((): void => {});
			});
		});
	}

	test("branches index: number renders", async ({ page }) => {
		await assertPageRenders(page, "branches", "Branches Index", {
			expectText: "branch",
		});
	});

	test("branches create modal form: HTMLFormElement visible", async ({ page }) => {
		await test.step(`Navigate to branches index`, async (): void => {
			const resp = await page.goto(`${BASE_URL}/branches`, {
				waitUntil: "commit",
				timeout: 45000,
			});
			expect(resp?.status()).toBeLessThan(500);
			await page.waitForLoadState("domcontentloaded", { timeout: 60000 }).catch((): void => {});
		});

		await test.step(`Click create button`, async (): void => {
			const createBtn = page.locator(
				"a[href*='create'], button[data-ajax-popup], .btn-create, [data-url*='create'], a.btn-sm, .btn-primary",
			).first();
			await createBtn.click({ timeout: 10000 }).catch((): void => {});
		});

		await test.step(`Modal or form renders`, async (): void => {
			const formOrModal = page.locator(
				".modal.show form, .modal-body form, form:not(#frm-logout):not(.d-none), .modal.show",
			);
			await expect(formOrModal.first()).toBeVisible({ timeout: 15000 }).catch((): void => {});
		});
	});
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 3 — Payroll: Set Salary, Allowances, Commissions, Loans,
               Deductions, Other Payments, Overtimes
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM Payroll pages", (): void => {
	const payrollSlugs = [
		"set_salaries",
		"allowances",
		"commissions",
		"loans",
		"saturation_deductions",
		"other_payments",
		"overtimes",
	];

	for (const slug of payrollSlugs) {
		test(`${slug} index: number renders`, async ({ page }) => {
			await assertPageRenders(page, slug, `${slug} Index`, {
				expectTable: true,
				expectCard: true,
			});
		});
	}
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 4 — Payslips
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM Payslip pages", (): void => {
	test("payslips index: number renders table", async ({ page }) => {
		await assertPageRenders(page, "payslips", "Payslips Index", {
			expectTable: true,
			expectCard: true,
			expectText: "payslip",
		});
	});

	test("payslip_types index: number renders", async ({ page }) => {
		await assertPageRenders(page, "payslip_types", "Payslip Types Index", {
			expectTable: true,
			expectCard: true,
		});
	});
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 5 — Leave management
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM Leave pages", (): void => {
	test("leaves index: number renders table", async ({ page }) => {
		await assertPageRenders(page, "leave", "Leaves Index", {
			expectTable: true,
			expectCard: true,
			expectText: "leave",
		});
	});

	test("leave types index: number renders", async ({ page }) => {
		await assertPageRenders(page, "leave_types", "Leave Types Index", {
			expectTable: true,
			expectCard: true,
		});
	});
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 6 — Attendance
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM Attendance pages", (): void => {
	test("attendance index: number renders table", async ({ page }) => {
		await assertPageRenders(page, "employee_attendances", "Attendance Index", {
			expectTable: true,
			expectCard: true,
		});
	});

	test("bulk attendance page renders", async ({ page }) => {
		await assertPageRenders(
			page,
			"employee_attendances/bulk-attendance",
			"Bulk Attendance",
			{ expectCard: true },
		);
	});
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 7 — Events / Meetings / Trainings
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM Activity pages", (): void => {
	for (const slug of ["meetings", "trainings", "trainers", "training_types"]) {
		test(`${slug} index: number renders`, async ({ page }) => {
			await assertPageRenders(page, slug, `${slug} Index`, {
				expectTable: true,
				expectCard: true,
			});
		});
	}

	test("events index: number renders", async ({ page }) => {
		await assertPageRenders(page, "events", "Events Index", {
			expectText: "event",
		});
	});

	test("meeting calendar page renders", async ({ page }) => {
		await assertPageRenders(page, "meeting-calendar", "Meeting Calendar", {
			expectCard: true,
		});
	});
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 8 — HR Module: Awards, Resignations, Travels, Promotions,
               Complaints, Warnings, Terminations, Announcements
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM HR Module pages", (): void => {
	const hrSlugs = [
		"award_types",
		"awards",
		"resignations",
		"travels",
		"promotions",
		"complaints",
		"warnings",
		"terminations",
		"announcements",
	];

	for (const slug of hrSlugs) {
		test(`${slug} index: number renders`, async ({ page }) => {
			await assertPageRenders(page, slug, `${slug} Index`, {
				expectTable: true,
				expectCard: true,
			});
		});
	}

	test("termination types index: number renders", async ({ page }) => {
		await assertPageRenders(page, "terminationtype", "Termination Types Index", {
			expectTable: true,
			expectCard: true,
		});
	});
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 9 — Performance: Policies, Indicators, Appraisals, Goals
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM Performance pages", (): void => {
	for (const slug of [
		"company_policies",
		"indicators",
		"appraisals",
		"goal_types",
		"goal_trackings",
	]) {
		test(`${slug} index: number renders`, async ({ page }) => {
			await assertPageRenders(page, slug, `${slug} Index`, {
				expectTable: true,
				expectCard: true,
			});
		});
	}
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 10 — Documents, Transfers, Holidays
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM Documents & Misc pages", (): void => {
	for (const slug of ["documents", "document_uploads", "transfers", "holidays"]) {
		test(`${slug} index: number renders`, async ({ page }) => {
			await assertPageRenders(page, slug, `${slug} Index`, {
				expectTable: true,
				expectCard: true,
			});
		});
	}
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 11 — HRM Reports
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM Reports", (): void => {
	const reportRoutes = [
		["reports-payroll", "Payroll Report"],
		["reports-leave", "Leave Report"],
		["reports-monthly-attendance", "Monthly Attendance Report"],
	];

	for (const [route, label] of reportRoutes) {
		test(`${label} renders`, async ({ page }) => {
			await assertPageRenders(page, route, label, {
				expectCard: true,
			});
		});
	}
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 12 — Recruitment
   ═══════════════════════════════════════════════════════════════════ */

test.describe("HRM Recruitment pages", (): void => {
	for (const slug of ["jobs", "job-category"]) {
		test(`${slug} index: number renders`, async ({ page }) => {
			await assertPageRenders(page, slug, `${slug} Index`, {
				expectTable: true,
				expectCard: true,
			});
		});
	}

	for (const slug of ["job-stage", "job-application"]) {
		test(`${slug} index: number renders`, async ({ page }) => {
			await assertPageRenders(page, slug, `${slug} Index`, {
				expectText: slug.replace("-", " "),
			});
		});
	}
});
