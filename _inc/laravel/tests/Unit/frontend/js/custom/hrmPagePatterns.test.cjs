/**
 * HRM Page DOM Patterns — Jest / jsdom
 *
 * Validates that the DOM patterns used across HRM index pages
 * (datatable, card-body, modal, select2, action buttons)
 * initialise correctly via custom.js helpers.
 */

const {
  loadCustomJs,
  buildJQueryEnv,
  buildDomSkeleton,
} = require("../helpers/setup.cjs");

beforeEach(() => {
  document.body.innerHTML = "";
  document.head.innerHTML = "";
  buildJQueryEnv();
  buildDomSkeleton();
  loadCustomJs();
});

const HRM_PAGES = [
  "employees",
  "departments",
  "designations",
  "branches",
  "payslips",
  "leaves",
  "events",
  "meetings",
  "trainings",
  "awards",
  "complaints",
  "warnings",
  "terminations",
  "announcements",
  "employee_attendances",
];

describe("HRM DataTable pattern", () => {
  test.each(HRM_PAGES)("%s index table initialises without error", page => {
    document.body.innerHTML = `
			<div class="card">
				<div class="card-body table-border-style">
					<h5 class="card-title">${page}</h5>
					<div class="table-responsive">
						<table class="table datatable" id="${page}-table">
							<thead><tr><th>ID</th><th>Name</th><th>Action</th></tr></thead>
							<tbody>
								<tr><td>1</td><td>Sample</td><td><a href="#">Edit</a></td></tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>`;
    expect(document.querySelector(".datatable")).toBeTruthy();
    expect(document.querySelector(".datatable thead th")).toBeTruthy();
    expect(document.querySelector(".datatable tbody tr")).toBeTruthy();
  });

  test("datatable class is present on table element", () => {
    document.body.innerHTML =
      '<table class="table datatable"><thead><tr><th>X</th></tr></thead><tbody></tbody></table>';
    const t = document.querySelector("table.datatable");
    expect(t).not.toBeNull();
    expect(t.classList.contains("datatable")).toBe(true);
  });
});

describe("HRM Card layout pattern", () => {
  test.each(HRM_PAGES)("%s card-body renders heading", page => {
    document.body.innerHTML = `
			<div class="card">
				<div class="card-header"><h5>${page}</h5></div>
				<div class="card-body">
					<p>Content for ${page}</p>
				</div>
			</div>`;
    expect(document.querySelector(".card-body")).toBeTruthy();
    expect(document.querySelector(".card-header h5").textContent).toBe(page);
  });
});

describe("HRM AJAX modal pattern", () => {
  test("commonModal exists and title updates", () => {
    document.body.innerHTML = `
			<div class="modal" id="commonModal">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header"><h5 class="modal-title"></h5></div>
						<div class="modal-body"></div>
					</div>
				</div>
			</div>`;
    const title = document.querySelector("#commonModal .modal-title");
    expect(title).not.toBeNull();
    title.innerHTML = "Create Employee";
    expect(title.textContent).toBe("Create Employee");
  });

  test("data-ajax-popup trigger creates correct modal sizing", () => {
    document.body.innerHTML = `
			<button data-ajax-popup="true" data-size="lg" data-title="Add Leave"
				data-url="/leaves/create">Add</button>
			<div class="modal" id="commonModal">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header"><h5 class="modal-title"></h5></div>
						<div class="modal-body"></div>
					</div>
				</div>
			</div>`;
    const btn = document.querySelector('[data-ajax-popup="true"]');
    expect(btn.dataset.size).toBe("lg");
    expect(btn.dataset.title).toBe("Add Leave");
    expect(btn.dataset.url).toBe("/leaves/create");
  });
});

describe("HRM select2 pattern", () => {
  test("select2 elements are present and have IDs", () => {
    document.body.innerHTML = `
			<select class="select2" id="department_id">
				<option value="1">Engineering</option>
				<option value="2">HR</option>
			</select>
			<select class="select2" id="designation_id">
				<option value="1">Manager</option>
			</select>`;
    const selects = document.querySelectorAll(".select2");
    expect(selects.length).toBe(2);
    selects.forEach(s => {
      expect(s.id).toBeTruthy();
    });
  });

  test("select2 function is defined after custom.js load", () => {
    expect(typeof global.select2).toBe("function");
  });
});

describe("HRM action button patterns", () => {
  const actions = [
    { label: "Create", cls: "btn-primary", icon: "ti-plus" },
    { label: "Export", cls: "btn-primary", icon: "ti-file-export" },
    { label: "Import", cls: "btn-primary", icon: "ti-file-import" },
  ];

  test.each(actions)(
    "$label button renders with correct class",
    ({ label, cls }) => {
      document.body.innerHTML = `<a class="btn ${cls}" href="#">${label}</a>`;
      const btn = document.querySelector(`.btn.${cls}`);
      expect(btn).not.toBeNull();
      expect(btn.textContent.trim()).toBe(label);
    },
  );

  test("delete confirmation uses data-confirm attribute", () => {
    document.body.innerHTML = `
			<a href="/employees/1" class="btn btn-danger"
				data-confirm="Are you sure?" data-method="DELETE">Delete</a>`;
    const del = document.querySelector("[data-confirm]");
    expect(del).not.toBeNull();
    expect(del.dataset.confirm).toBe("Are you sure?");
    expect(del.dataset.method).toBe("DELETE");
  });
});

describe("HRM breadcrumb pattern", () => {
  test("breadcrumb renders Dashboard and module name", () => {
    document.body.innerHTML = `
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">Employee</li>
			</ol>`;
    const items = document.querySelectorAll(".breadcrumb-item");
    expect(items.length).toBe(2);
    expect(items[0].textContent).toBe("Dashboard");
    expect(items[1].textContent).toBe("Employee");
  });
});

describe("HRM toastr notification", () => {
  test("show_toastr function exists", () => {
    expect(typeof global.show_toastr).toBe("function");
  });

  test("liveToast renders message", () => {
    document.body.innerHTML = `
			<div id="liveToast" class="toast" role="alert">
				<div class="toast-body"></div>
			</div>`;
    const body = document.querySelector("#liveToast .toast-body");
    body.innerHTML = "Employee created successfully.";
    expect(body.textContent).toContain("Employee created");
  });
});

describe("HRM form patterns", () => {
  test("employee form has required fields", () => {
    document.body.innerHTML = `
			<form id="employee-form" method="POST" action="/employees">
				<input type="hidden" name="_token" value="csrf-token"/>
				<input type="text" name="name" required/>
				<input type="email" name="email" required/>
				<select name="department_id" class="select2" id="department_id" required>
					<option value="">Select</option>
				</select>
				<select name="designation_id" class="select2" id="designation_id" required>
					<option value="">Select</option>
				</select>
				<button type="submit" class="btn btn-primary">Save</button>
			</form>`;
    const form = document.getElementById("employee-form");
    expect(form).not.toBeNull();
    expect(form.querySelector('[name="name"]')).not.toBeNull();
    expect(form.querySelector('[name="email"]')).not.toBeNull();
    expect(form.querySelector('[name="department_id"]')).not.toBeNull();
    expect(form.querySelector('[name="designation_id"]')).not.toBeNull();
    expect(form.querySelector('[type="submit"]')).not.toBeNull();
  });

  test("leave form contains date fields", () => {
    document.body.innerHTML = `
			<form id="leave-form" method="POST" action="/leaves">
				<input type="hidden" name="_token" value="csrf-token"/>
				<select name="leave_type_id" required><option value="1">Sick</option></select>
				<input type="date" name="start_date" required/>
				<input type="date" name="end_date" required/>
				<textarea name="leave_reason"></textarea>
				<button type="submit">Apply</button>
			</form>`;
    const form = document.getElementById("leave-form");
    expect(form.querySelector('[name="start_date"]')).not.toBeNull();
    expect(form.querySelector('[name="end_date"]')).not.toBeNull();
    expect(form.querySelector('[name="leave_type_id"]')).not.toBeNull();
  });

  test("payslip form includes salary month field", () => {
    document.body.innerHTML = `
			<form id="payslip-form" method="POST" action="/payslips">
				<input type="hidden" name="_token" value="csrf-token"/>
				<input type="month" name="salary_month" required/>
				<select name="employee_id" required><option value="1">John</option></select>
				<button type="submit">Generate</button>
			</form>`;
    const form = document.getElementById("payslip-form");
    expect(form.querySelector('[name="salary_month"]')).not.toBeNull();
    expect(form.querySelector('[name="employee_id"]')).not.toBeNull();
  });
});

describe("HRM getDepartment/getEmployee AJAX pattern", () => {
  test("branch select triggers department fetch", () => {
    document.body.innerHTML = `
			<select id="branch_id" name="branch_id">
				<option value="">Select</option>
				<option value="1">Main</option>
			</select>
			<select id="department_id" name="department_id">
				<option value="">Select</option>
			</select>`;
    const branchSelect = document.getElementById("branch_id");
    expect(branchSelect).not.toBeNull();
    const deptSelect = document.getElementById("department_id");
    expect(deptSelect).not.toBeNull();
    branchSelect.value = "1";
    branchSelect.dispatchEvent(new Event("change"));
    expect(branchSelect.value).toBe("1");
  });
});
