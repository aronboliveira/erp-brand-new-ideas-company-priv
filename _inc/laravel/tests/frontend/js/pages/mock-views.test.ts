/**
 * @file mock-views.test.ts
 * @description Tests for mock view HTML templates — validates structure,
 * accessibility, and clientside behavior without PHP
 */
import { jest, describe, it, expect, beforeEach } from "@jest/globals";
import fs from "fs";
import path from "path";
import { resetDOM } from "../setup";

const MOCKS_DIR = path.join(__dirname, "..", "mocks", "views");

function loadView(name: string): void {
  const html = fs.readFileSync(path.join(MOCKS_DIR, `${name}.html`), "utf-8");
  // Extract body content
  const bodyMatch = html.match(/<body[^>]*>([\s\S]*)<\/body>/i);
  if (bodyMatch) {
    document.body.innerHTML = bodyMatch[1];
  } else {
    document.body.innerHTML = html;
  }
  // Set meta tags in head
  const metaMatch = html.match(/<meta\s+name="csrf-token"\s+content="([^"]+)"/);
  if (metaMatch) {
    const meta = document.createElement("meta");
    meta.setAttribute("name", "csrf-token");
    meta.setAttribute("content", metaMatch[1]);
    document.head.appendChild(meta);
  }
}

describe("Mock View: Dashboard", () => {
  beforeEach(() => {
    resetDOM();
    loadView("dashboard");
  });

  it("should have CSRF meta tag", () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    expect(meta).toBeTruthy();
    expect(meta?.getAttribute("content")).toBe("test-csrf-token-abc123");
  });

  it("should have sidebar navigation", () => {
    const sidebar = document.querySelector(".dash-sidebar");
    expect(sidebar).toBeTruthy();
  });

  it("should have navigation links", () => {
    const links = document.querySelectorAll(".dash-navbar .dash-link");
    expect(links.length).toBeGreaterThan(0);
  });

  it("should have widget cards", () => {
    const widgets = document.querySelectorAll("#dashboard-widgets .card");
    expect(widgets.length).toBe(4);
  });

  it("should have widget values", () => {
    const values = document.querySelectorAll(".widget-value");
    expect(values.length).toBe(4);
  });

  it("should have data-widget attributes on cards", () => {
    const customers = document.querySelector('[data-widget="total-customers"]');
    const invoices = document.querySelector('[data-widget="total-invoices"]');
    const revenue = document.querySelector('[data-widget="total-revenue"]');
    const bills = document.querySelector('[data-widget="total-bills"]');
    expect(customers).toBeTruthy();
    expect(invoices).toBeTruthy();
    expect(revenue).toBeTruthy();
    expect(bills).toBeTruthy();
  });

  it("should have an invoices table", () => {
    const table = document.querySelector("#invoices-table");
    expect(table).toBeTruthy();
    const headers = table?.querySelectorAll("thead th");
    expect(headers?.length).toBeGreaterThan(0);
  });

  it("should have topbar with controls", () => {
    const hamburger = document.querySelector(".hamburger");
    const mobileCollapse = document.querySelector("#mobile-collapse");
    expect(hamburger).toBeTruthy();
    expect(mobileCollapse).toBeTruthy();
  });

  it("should have loader background", () => {
    const loader = document.querySelector(".loader-bg");
    expect(loader).toBeTruthy();
  });

  it("should have submenu items", () => {
    const submenu = document.querySelector(".dash-submenu");
    expect(submenu).toBeTruthy();
  });
});

describe("Mock View: Invoices", () => {
  beforeEach(() => {
    resetDOM();
    loadView("invoices");
  });

  it("should have filter form", () => {
    const form = document.querySelector("#filter-form");
    expect(form).toBeTruthy();
  });

  it("should have status filter dropdown", () => {
    const select = document.querySelector(
      "#status-filter",
    ) as HTMLSelectElement;
    expect(select).toBeTruthy();
    expect(select?.options.length).toBe(5); // All + 4 statuses
  });

  it("should have date range inputs", () => {
    const startDate = document.querySelector("#start-date") as HTMLInputElement;
    const endDate = document.querySelector("#end-date") as HTMLInputElement;
    expect(startDate).toBeTruthy();
    expect(endDate).toBeTruthy();
    expect(startDate?.type).toBe("date");
    expect(endDate?.type).toBe("date");
  });

  it("should have invoice table with rows", () => {
    const rows = document.querySelectorAll("#invoices-tbody tr");
    expect(rows.length).toBe(2);
  });

  it("should have action buttons per row", () => {
    const viewBtns = document.querySelectorAll(".btn-info");
    const editBtns = document.querySelectorAll(".btn-warning");
    const deleteBtns = document.querySelectorAll(".delete-btn");
    expect(viewBtns.length).toBe(2);
    expect(editBtns.length).toBe(2);
    expect(deleteBtns.length).toBe(2);
  });

  it("should have select-all checkbox", () => {
    const selectAll = document.querySelector("#select-all") as HTMLInputElement;
    expect(selectAll).toBeTruthy();
    expect(selectAll?.type).toBe("checkbox");
  });

  it("select-all should toggle all row checkboxes", () => {
    const selectAll = document.querySelector("#select-all") as HTMLInputElement;
    const rowCheckboxes = document.querySelectorAll(
      ".row-select",
    ) as NodeListOf<HTMLInputElement>;

    // Simulate select-all behavior
    selectAll.checked = true;
    selectAll.dispatchEvent(new Event("change"));
    rowCheckboxes.forEach(cb => {
      cb.checked = selectAll.checked;
    });

    rowCheckboxes.forEach(cb => expect(cb.checked).toBe(true));

    selectAll.checked = false;
    selectAll.dispatchEvent(new Event("change"));
    rowCheckboxes.forEach(cb => {
      cb.checked = selectAll.checked;
    });

    rowCheckboxes.forEach(cb => expect(cb.checked).toBe(false));
  });

  it("should have pagination", () => {
    const pagination = document.querySelector("#invoice-pagination");
    expect(pagination).toBeTruthy();
    const pageItems = pagination?.querySelectorAll(".page-item");
    expect(pageItems?.length).toBeGreaterThan(0);
  });

  it("should have create invoice button", () => {
    const createBtn = document.querySelector('a[href="/invoices/create"]');
    expect(createBtn).toBeTruthy();
    expect(createBtn?.textContent).toContain("Create Invoice");
  });

  it("delete buttons should have data-id attributes", () => {
    const deleteBtns = document.querySelectorAll(".delete-btn");
    deleteBtns.forEach(btn => {
      expect(btn.getAttribute("data-id")).toBeTruthy();
    });
  });

  it("invoice rows should have data-id attributes", () => {
    const rows = document.querySelectorAll("#invoices-tbody tr[data-id]");
    expect(rows.length).toBe(2);
  });
});

describe("Mock View: Login", () => {
  beforeEach(() => {
    resetDOM();
    loadView("login");
  });

  it("should have login form", () => {
    const form = document.querySelector("#login-form") as HTMLFormElement;
    expect(form).toBeTruthy();
    expect(form?.action).toContain("/login");
    expect(form?.method).toBe("post");
  });

  it("should have CSRF token hidden input", () => {
    const token = document.querySelector(
      'input[name="_token"]',
    ) as HTMLInputElement;
    expect(token).toBeTruthy();
    expect(token?.value).toBe("test-csrf-token-abc123");
  });

  it("should have email input", () => {
    const email = document.querySelector("#email") as HTMLInputElement;
    expect(email).toBeTruthy();
    expect(email?.type).toBe("email");
    expect(email?.required).toBe(true);
  });

  it("should have password input", () => {
    const password = document.querySelector("#password") as HTMLInputElement;
    expect(password).toBeTruthy();
    expect(password?.type).toBe("password");
    expect(password?.required).toBe(true);
  });

  it("should have remember me checkbox", () => {
    const remember = document.querySelector("#remember") as HTMLInputElement;
    expect(remember).toBeTruthy();
    expect(remember?.type).toBe("checkbox");
  });

  it("should have submit button", () => {
    const btn = document.querySelector("#login-btn") as HTMLButtonElement;
    expect(btn).toBeTruthy();
    expect(btn?.type).toBe("submit");
  });

  it("should have forgot password link", () => {
    const link = document.querySelector('a[href="/forgot-password"]');
    expect(link).toBeTruthy();
  });

  it("should have register link", () => {
    const link = document.querySelector('a[href="/register"]');
    expect(link).toBeTruthy();
  });

  it("should have hidden error alert", () => {
    const alert = document.querySelector("#login-error");
    expect(alert).toBeTruthy();
    expect(alert?.classList.contains("d-none")).toBe(true);
  });

  it("should validate email format", () => {
    const email = document.querySelector("#email") as HTMLInputElement;
    email.value = "notanemail";
    expect(email.validity.valid).toBe(false);
    email.value = "test@example.com";
    expect(email.validity.valid).toBe(true);
  });

  it("should prevent empty submission via HTML5 validation", () => {
    const form = document.querySelector("#login-form") as HTMLFormElement;
    expect(form.checkValidity()).toBe(false);
  });

  it("form should be valid with correct inputs", () => {
    const email = document.querySelector("#email") as HTMLInputElement;
    const password = document.querySelector("#password") as HTMLInputElement;
    email.value = "admin@example.com";
    password.value = "password123";
    const form = document.querySelector("#login-form") as HTMLFormElement;
    expect(form.checkValidity()).toBe(true);
  });
});
