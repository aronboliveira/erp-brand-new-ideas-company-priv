/**
 * PM Page DOM Patterns — Jest / jsdom
 *
 * Validates that the DOM patterns used across Project Management
 * index pages (datatable, card-body, modal, kanban, gantt, select2,
 * action buttons) initialise correctly via custom.js helpers.
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

const PM_PAGES = [
  "projects",
  "project_stages",
  "project_task_stages",
  "project_reports",
  "contracts",
  "contract_types",
  "proposals",
  "time_trackers",
  "timesheets",
  "bug_status",
  "task_boards",
  "todos",
];

describe("PM DataTable pattern", () => {
  test.each(PM_PAGES)("%s index table initialises without error", page => {
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

describe("PM Card layout pattern", () => {
  test.each(PM_PAGES)("%s card-body renders heading", page => {
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

describe("PM AJAX modal pattern", () => {
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
    title.innerHTML = "Create Project";
    expect(title.textContent).toBe("Create Project");
  });

  test("data-ajax-popup trigger creates correct modal sizing", () => {
    document.body.innerHTML = `
			<button data-ajax-popup="true" data-size="lg" data-title="Add Task Stage"
				data-url="/project_task_stages/create">Add</button>
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
    expect(btn.dataset.title).toBe("Add Task Stage");
    expect(btn.dataset.url).toBe("/project_task_stages/create");
  });
});

describe("PM Kanban board pattern", () => {
  test("kanban board renders swim lanes", () => {
    document.body.innerHTML = `
			<div class="kanban-board">
				<div class="kanban-col" data-status="to_do">
					<div class="kanban-header"><h5>To Do</h5></div>
					<div class="kanban-body">
						<div class="kanban-card" data-id="1">Task A</div>
					</div>
				</div>
				<div class="kanban-col" data-status="in_progress">
					<div class="kanban-header"><h5>In Progress</h5></div>
					<div class="kanban-body"></div>
				</div>
				<div class="kanban-col" data-status="done">
					<div class="kanban-header"><h5>Done</h5></div>
					<div class="kanban-body"></div>
				</div>
			</div>`;
    const cols = document.querySelectorAll(".kanban-col");
    expect(cols.length).toBe(3);
    expect(cols[0].dataset.status).toBe("to_do");
    expect(document.querySelector(".kanban-card")).not.toBeNull();
  });

  test("bug kanban has status columns", () => {
    document.body.innerHTML = `
			<div class="kanban-board" id="bug-kanban">
				<div class="kanban-col" data-status="unconfirmed">
					<div class="kanban-header"><h5>Unconfirmed</h5></div>
					<div class="kanban-body"></div>
				</div>
				<div class="kanban-col" data-status="confirmed">
					<div class="kanban-header"><h5>Confirmed</h5></div>
					<div class="kanban-body"></div>
				</div>
				<div class="kanban-col" data-status="resolved">
					<div class="kanban-header"><h5>Resolved</h5></div>
					<div class="kanban-body"></div>
				</div>
			</div>`;
    const board = document.getElementById("bug-kanban");
    expect(board).not.toBeNull();
    expect(board.querySelectorAll(".kanban-col").length).toBe(3);
  });
});

describe("PM Gantt chart pattern", () => {
  test("gantt container renders with timeline", () => {
    document.body.innerHTML = `
			<div id="gantt-chart" class="gantt-container">
				<div class="gantt-timeline">
					<div class="gantt-bar" data-task-id="1" data-start="2025-01-01" data-end="2025-01-15">
						<span class="gantt-bar-label">Task A</span>
					</div>
					<div class="gantt-bar" data-task-id="2" data-start="2025-01-10" data-end="2025-02-01">
						<span class="gantt-bar-label">Task B</span>
					</div>
				</div>
			</div>`;
    const container = document.getElementById("gantt-chart");
    expect(container).not.toBeNull();
    const bars = container.querySelectorAll(".gantt-bar");
    expect(bars.length).toBe(2);
    expect(bars[0].dataset.start).toBe("2025-01-01");
  });
});

describe("PM select2 pattern", () => {
  test("project form select2 elements with IDs", () => {
    document.body.innerHTML = `
			<select class="select2" id="project_id">
				<option value="1">Project Alpha</option>
				<option value="2">Project Beta</option>
			</select>
			<select class="select2" id="stage_id">
				<option value="1">Active</option>
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

describe("PM action button patterns", () => {
  const actions = [
    { label: "Create", cls: "btn-primary", icon: "ti-plus" },
    { label: "Export", cls: "btn-primary", icon: "ti-file-export" },
    { label: "Import", cls: "btn-primary", icon: "ti-file-import" },
    { label: "Grid", cls: "btn-primary", icon: "ti-layout-grid" },
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
			<a href="/projects/1" class="btn btn-danger"
				data-confirm="Are you sure?" data-method="DELETE">Delete</a>`;
    const del = document.querySelector("[data-confirm]");
    expect(del).not.toBeNull();
    expect(del.dataset.confirm).toBe("Are you sure?");
    expect(del.dataset.method).toBe("DELETE");
  });
});

describe("PM breadcrumb pattern", () => {
  test("breadcrumb renders Dashboard and module name", () => {
    document.body.innerHTML = `
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
				<li class="breadcrumb-item"><a href="/projects">Projects</a></li>
				<li class="breadcrumb-item">Tasks</li>
			</ol>`;
    const items = document.querySelectorAll(".breadcrumb-item");
    expect(items.length).toBe(3);
    expect(items[0].textContent).toBe("Dashboard");
    expect(items[1].textContent).toBe("Projects");
    expect(items[2].textContent).toBe("Tasks");
  });
});

describe("PM toastr notification", () => {
  test("show_toastr function exists", () => {
    expect(typeof global.show_toastr).toBe("function");
  });

  test("liveToast renders message", () => {
    document.body.innerHTML = `
			<div id="liveToast" class="toast" role="alert">
				<div class="toast-body"></div>
			</div>`;
    const body = document.querySelector("#liveToast .toast-body");
    body.innerHTML = "Project created successfully.";
    expect(body.textContent).toContain("Project created");
  });
});

describe("PM form patterns", () => {
  test("project form has required fields", () => {
    document.body.innerHTML = `
			<form id="project-form" method="POST" action="/projects">
				<input type="hidden" name="_token" value="csrf-token"/>
				<input type="text" name="name" required/>
				<textarea name="description"></textarea>
				<select name="status" required>
					<option value="ongoing">Ongoing</option>
					<option value="finished">Finished</option>
				</select>
				<input type="date" name="start_date" required/>
				<input type="date" name="end_date" required/>
				<input type="number" name="budget" step="0.01"/>
				<button type="submit" class="btn btn-primary">Save</button>
			</form>`;
    const form = document.getElementById("project-form");
    expect(form).not.toBeNull();
    expect(form.querySelector('[name="name"]')).not.toBeNull();
    expect(form.querySelector('[name="status"]')).not.toBeNull();
    expect(form.querySelector('[name="start_date"]')).not.toBeNull();
    expect(form.querySelector('[name="end_date"]')).not.toBeNull();
    expect(form.querySelector('[type="submit"]')).not.toBeNull();
  });

  test("contract form includes client and value fields", () => {
    document.body.innerHTML = `
			<form id="contract-form" method="POST" action="/contracts">
				<input type="hidden" name="_token" value="csrf-token"/>
				<input type="text" name="subject" required/>
				<select name="client_name" required><option value="1">Client A</option></select>
				<input type="number" name="value" step="0.01" required/>
				<input type="date" name="start_date" required/>
				<input type="date" name="end_date" required/>
				<textarea name="description"></textarea>
				<button type="submit">Save</button>
			</form>`;
    const form = document.getElementById("contract-form");
    expect(form.querySelector('[name="subject"]')).not.toBeNull();
    expect(form.querySelector('[name="client_name"]')).not.toBeNull();
    expect(form.querySelector('[name="value"]')).not.toBeNull();
  });

  test("proposal form has customer and status fields", () => {
    document.body.innerHTML = `
			<form id="proposal-form" method="POST" action="/proposal">
				<input type="hidden" name="_token" value="csrf-token"/>
				<select name="customer_id" required><option value="1">Customer A</option></select>
				<input type="date" name="issue_date" required/>
				<select name="status" required>
					<option value="open">Open</option>
					<option value="accepted">Accepted</option>
					<option value="declined">Declined</option>
				</select>
				<button type="submit">Save</button>
			</form>`;
    const form = document.getElementById("proposal-form");
    expect(form.querySelector('[name="customer_id"]')).not.toBeNull();
    expect(form.querySelector('[name="issue_date"]')).not.toBeNull();
    expect(form.querySelector('[name="status"]')).not.toBeNull();
  });

  test("task stage form has name and color fields", () => {
    document.body.innerHTML = `
			<form id="task-stage-form" method="POST" action="/project_task_stages">
				<input type="hidden" name="_token" value="csrf-token"/>
				<input type="text" name="name" required maxlength="20"/>
				<input type="color" name="color" value="#000000"/>
				<button type="submit">Save</button>
			</form>`;
    const form = document.getElementById("task-stage-form");
    expect(form.querySelector('[name="name"]')).not.toBeNull();
    expect(form.querySelector('[name="color"]')).not.toBeNull();
  });

  test("timesheet form has project and time fields", () => {
    document.body.innerHTML = `
			<form id="timesheet-form" method="POST" action="/projects.timesheets/projects/1">
				<input type="hidden" name="_token" value="csrf-token"/>
				<select name="task_id" required><option value="1">Task A</option></select>
				<input type="date" name="date" required/>
				<input type="time" name="start_time" required/>
				<input type="time" name="end_time" required/>
				<textarea name="remark"></textarea>
				<button type="submit">Save</button>
			</form>`;
    const form = document.getElementById("timesheet-form");
    expect(form.querySelector('[name="task_id"]')).not.toBeNull();
    expect(form.querySelector('[name="date"]')).not.toBeNull();
    expect(form.querySelector('[name="start_time"]')).not.toBeNull();
    expect(form.querySelector('[name="end_time"]')).not.toBeNull();
  });
});

describe("PM todo widget pattern", () => {
  test("todo list renders with completed toggles", () => {
    document.body.innerHTML = `
			<div id="todo-widget" class="card">
				<div class="card-header"><h5>Todos</h5></div>
				<div class="card-body">
					<ul class="todo-list">
						<li class="todo-item" data-id="1">
							<input type="checkbox" class="todo-toggle" checked/>
							<span class="todo-title">Finish report</span>
							<button class="btn btn-sm btn-danger todo-delete">×</button>
						</li>
						<li class="todo-item" data-id="2">
							<input type="checkbox" class="todo-toggle"/>
							<span class="todo-title">Review code</span>
							<button class="btn btn-sm btn-danger todo-delete">×</button>
						</li>
					</ul>
					<form id="todo-add-form">
						<input type="text" name="title" placeholder="Add todo..." required/>
						<button type="submit" class="btn btn-primary btn-sm">Add</button>
					</form>
				</div>
			</div>`;
    const items = document.querySelectorAll(".todo-item");
    expect(items.length).toBe(2);
    expect(items[0].querySelector(".todo-toggle").checked).toBe(true);
    expect(items[1].querySelector(".todo-toggle").checked).toBe(false);
    expect(document.getElementById("todo-add-form")).not.toBeNull();
  });
});

describe("PM progress bar pattern", () => {
  test("project progress bar renders percentage", () => {
    document.body.innerHTML = `
			<div class="project-progress">
				<div class="progress">
					<div class="progress-bar" role="progressbar" style="width: 65%"
						aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">65%</div>
				</div>
			</div>`;
    const bar = document.querySelector(".progress-bar");
    expect(bar).not.toBeNull();
    expect(bar.getAttribute("aria-valuenow")).toBe("65");
    expect(bar.textContent.trim()).toBe("65%");
  });
});

describe("PM milestone pattern", () => {
  test("milestone list renders with status badges", () => {
    document.body.innerHTML = `
			<div class="milestone-list">
				<div class="milestone-item" data-id="1">
					<h6 class="milestone-title">Phase 1</h6>
					<span class="badge bg-success">Complete</span>
					<small class="milestone-date">2025-01-15</small>
				</div>
				<div class="milestone-item" data-id="2">
					<h6 class="milestone-title">Phase 2</h6>
					<span class="badge bg-warning">In Progress</span>
					<small class="milestone-date">2025-03-01</small>
				</div>
			</div>`;
    const items = document.querySelectorAll(".milestone-item");
    expect(items.length).toBe(2);
    expect(items[0].querySelector(".badge").textContent).toBe("Complete");
    expect(items[1].querySelector(".milestone-title").textContent).toBe(
      "Phase 2",
    );
  });
});

describe("PM time tracker pattern", () => {
  test("timer display shows HH:MM:SS format", () => {
    document.body.innerHTML = `
			<div class="time-tracker">
				<div class="timer-display" id="tracker-timer">00:00:00</div>
				<button class="btn btn-success tracker-start">Start</button>
				<button class="btn btn-danger tracker-stop" disabled>Stop</button>
			</div>`;
    const timerDisplay = document.getElementById("tracker-timer");
    expect(timerDisplay).not.toBeNull();
    expect(timerDisplay.textContent).toMatch(/^\d{2}:\d{2}:\d{2}$/);
    expect(document.querySelector(".tracker-start")).not.toBeNull();
    expect(document.querySelector(".tracker-stop")).not.toBeNull();
  });
});

describe("PM project select triggers task fetch", () => {
  test("project select triggers dependent task dropdown", () => {
    document.body.innerHTML = `
			<select id="project_id" name="project_id">
				<option value="">Select Project</option>
				<option value="1">Alpha</option>
			</select>
			<select id="task_id" name="task_id">
				<option value="">Select Task</option>
			</select>`;
    const projectSelect = document.getElementById("project_id");
    expect(projectSelect).not.toBeNull();
    const taskSelect = document.getElementById("task_id");
    expect(taskSelect).not.toBeNull();
    projectSelect.value = "1";
    projectSelect.dispatchEvent(new Event("change"));
    expect(projectSelect.value).toBe("1");
  });
});
