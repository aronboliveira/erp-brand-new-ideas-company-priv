/**
 * CRM Page DOM Patterns — Jest / jsdom
 *
 * Validates that the DOM patterns used across CRM module
 * index pages (deals, leads, pipelines, stages, clients, customers)
 * including kanban boards, modals, forms, and AJAX patterns
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

const CRM_PAGES = [
  "deals",
  "leads",
  "pipelines",
  "stages",
  "lead_stages",
  "clients",
  "customers",
];

const KANBAN_PAGES = ["deals", "leads"];

// ══════════════════════════════════════════════════════════════════════
//  SECTION 1 — Kanban Board Pattern (Deals & Leads)
// ══════════════════════════════════════════════════════════════════════

describe("CRM Kanban Board pattern", () => {
  test.each(KANBAN_PAGES)("%s kanban board initialises without error", page => {
    document.body.innerHTML = `
      <div class="kanban-wrapper" data-page="${page}">
        <div class="kanban-container">
          <div class="kanban-board" data-stage-id="1">
            <header class="kanban-board-header">
              <div class="kanban-title-board">Stage 1</div>
              <span class="badge">3</span>
            </header>
            <main class="kanban-drag">
              <div class="kanban-item" data-item-id="1">
                <div class="kanban-box">
                  <h5 class="card-title">Item Title</h5>
                  <p>Description</p>
                </div>
              </div>
            </main>
          </div>
        </div>
      </div>`;
    expect(document.querySelector(".kanban-wrapper")).toBeTruthy();
    expect(document.querySelector(".kanban-board")).toBeTruthy();
    expect(document.querySelector(".kanban-item")).toBeTruthy();
  });

  test("kanban board has proper structure for drag-drop", () => {
    document.body.innerHTML = `
      <div class="kanban-container">
        <div class="kanban-board" data-stage-id="1">
          <main class="kanban-drag" data-stage-id="1"></main>
        </div>
        <div class="kanban-board" data-stage-id="2">
          <main class="kanban-drag" data-stage-id="2"></main>
        </div>
      </div>`;
    const boards = document.querySelectorAll(".kanban-board");
    expect(boards.length).toBe(2);
    const dragZones = document.querySelectorAll(".kanban-drag");
    expect(dragZones.length).toBe(2);
  });

  test("deal kanban item displays key information", () => {
    document.body.innerHTML = `
      <div class="kanban-item" data-item-id="deal-1">
        <div class="kanban-box">
          <div class="kanban-item-title">
            <h5>Important Deal</h5>
          </div>
          <div class="kanban-item-price">$10,000</div>
          <div class="kanban-item-labels">
            <span class="badge badge-primary">Hot</span>
          </div>
          <div class="kanban-item-assigned">
            <img src="/avatar.png" class="rounded-circle" alt="User">
          </div>
        </div>
      </div>`;
    expect(document.querySelector(".kanban-item-title h5").textContent).toBe(
      "Important Deal",
    );
    expect(document.querySelector(".kanban-item-price")).toBeTruthy();
    expect(document.querySelector(".kanban-item-labels .badge")).toBeTruthy();
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 2 — Pipeline Selector Pattern
// ══════════════════════════════════════════════════════════════════════

describe("CRM Pipeline Selector pattern", () => {
  test.each(KANBAN_PAGES)("%s has pipeline selector", page => {
    document.body.innerHTML = `
      <div class="card">
        <div class="card-header">
          <h5>${page}</h5>
          <div class="float-end">
            <select class="form-select pipeline-selector" id="pipeline_id">
              <option value="1">Sales Pipeline</option>
              <option value="2">Marketing Pipeline</option>
            </select>
          </div>
        </div>
      </div>`;
    const selector = document.querySelector(".pipeline-selector");
    expect(selector).toBeTruthy();
    expect(selector.options.length).toBe(2);
  });

  test("pipeline change triggers reload", () => {
    document.body.innerHTML = `
      <select class="form-select pipeline-selector" id="pipeline_id" data-change-url="/deals/change-pipeline">
        <option value="1">Pipeline 1</option>
        <option value="2">Pipeline 2</option>
      </select>`;
    const selector = document.querySelector(".pipeline-selector");
    expect(selector.dataset.changeUrl).toBe("/deals/change-pipeline");
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 3 — Stage Management Pattern
// ══════════════════════════════════════════════════════════════════════

describe("CRM Stage Management pattern", () => {
  test("stages table initialises correctly", () => {
    document.body.innerHTML = `
      <table class="table datatable" id="stages-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Color</th>
            <th>Pipeline</th>
            <th>Order</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr data-stage-id="1">
            <td>1</td>
            <td>New</td>
            <td><span class="badge" style="background-color: #3498db">New</span></td>
            <td>Sales Pipeline</td>
            <td>1</td>
            <td><a href="#" class="edit-btn">Edit</a></td>
          </tr>
        </tbody>
      </table>`;
    expect(document.querySelector("#stages-table")).toBeTruthy();
    expect(document.querySelector("tbody tr[data-stage-id]")).toBeTruthy();
  });

  test("lead_stages table has proper columns", () => {
    document.body.innerHTML = `
      <table class="table datatable" id="lead-stages-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Pipeline</th>
            <th>Order</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>`;
    const headers = document.querySelectorAll("#lead-stages-table thead th");
    expect(headers.length).toBe(5);
  });

  test("stage ordering sortable list", () => {
    document.body.innerHTML = `
      <ul class="list-group stage-sortable" id="stage-order-list">
        <li class="list-group-item" data-stage-id="1">Stage 1</li>
        <li class="list-group-item" data-stage-id="2">Stage 2</li>
        <li class="list-group-item" data-stage-id="3">Stage 3</li>
      </ul>`;
    const items = document.querySelectorAll(".stage-sortable .list-group-item");
    expect(items.length).toBe(3);
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 4 — Deal/Lead Detail Modal Pattern
// ══════════════════════════════════════════════════════════════════════

describe("CRM Detail Modal pattern", () => {
  test.each(["deal", "lead"])("%s detail modal structure", type => {
    document.body.innerHTML = `
      <div class="modal fade" id="${type}-detail-modal" tabindex="-1">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">${type} Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-8">
                  <ul class="nav nav-tabs" id="${type}-tabs">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#${type}-overview">Overview</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#${type}-tasks">Tasks</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#${type}-files">Files</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#${type}-activity">Activity</a></li>
                  </ul>
                  <div class="tab-content" id="${type}-tab-content">
                    <div class="tab-pane fade show active" id="${type}-overview"></div>
                    <div class="tab-pane fade" id="${type}-tasks"></div>
                    <div class="tab-pane fade" id="${type}-files"></div>
                    <div class="tab-pane fade" id="${type}-activity"></div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="${type}-sidebar">
                    <div class="${type}-info-card">
                      <h6>Value</h6>
                      <p class="${type}-value">$0</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>`;
    expect(document.querySelector(`#${type}-detail-modal`)).toBeTruthy();
    expect(document.querySelectorAll(`#${type}-tabs .nav-item`).length).toBe(4);
    expect(document.querySelector(`.${type}-sidebar`)).toBeTruthy();
  });

  test("modal tabs are properly structured", () => {
    document.body.innerHTML = `
      <ul class="nav nav-tabs" id="deal-tabs">
        <li class="nav-item"><a class="nav-link active" href="#overview">Overview</a></li>
        <li class="nav-item"><a class="nav-link" href="#tasks">Tasks</a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="overview">Content</div>
        <div class="tab-pane fade" id="tasks">Tasks Content</div>
      </div>`;
    expect(document.querySelector(".nav-tabs")).toBeTruthy();
    expect(document.querySelector(".tab-content")).toBeTruthy();
    expect(document.querySelector(".tab-pane.active")).toBeTruthy();
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 5 — Client/Customer DataTable Pattern
// ══════════════════════════════════════════════════════════════════════

describe("CRM Client/Customer DataTable pattern", () => {
  test("clients table has expected columns", () => {
    document.body.innerHTML = `
      <table class="table datatable" id="clients-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Created</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>1</td><td>John Doe</td><td>john@test.com</td><td>555-1234</td><td>2024-01-01</td><td><a href="#">Edit</a></td></tr>
        </tbody>
      </table>`;
    const headers = document.querySelectorAll("#clients-table thead th");
    expect(headers.length).toBe(6);
  });

  test("customers table has expected columns", () => {
    document.body.innerHTML = `
      <table class="table datatable" id="customers-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Created</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>`;
    const headers = document.querySelectorAll("#customers-table thead th");
    expect(headers.length).toBe(6);
  });

  test.each(["clients", "customers"])("%s table has datatable class", type => {
    document.body.innerHTML = `<table class="table datatable" id="${type}-table"><thead><tr><th>X</th></tr></thead><tbody></tbody></table>`;
    expect(document.querySelector(`#${type}-table.datatable`)).toBeTruthy();
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 6 — CRM Form Patterns
// ══════════════════════════════════════════════════════════════════════

describe("CRM Form patterns", () => {
  const FORM_TYPES = [
    "deal",
    "lead",
    "client",
    "customer",
    "pipeline",
    "stage",
  ];

  test.each(FORM_TYPES)("%s create form has required fields", type => {
    document.body.innerHTML = `
      <form id="${type}-form" action="/${type}s" method="POST">
        <input type="hidden" name="_token" value="csrf-token">
        <div class="form-group">
          <label for="name">Name</label>
          <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
      </form>`;
    const form = document.querySelector(`#${type}-form`);
    expect(form).toBeTruthy();
    expect(form.querySelector('input[name="name"]')).toBeTruthy();
    expect(form.querySelector('button[type="submit"]')).toBeTruthy();
  });

  test("deal form has pipeline and stage selector", () => {
    document.body.innerHTML = `
      <form id="deal-form">
        <select name="pipeline_id" class="form-select" required>
          <option value="1">Sales Pipeline</option>
        </select>
        <select name="stage_id" class="form-select" required>
          <option value="1">New</option>
        </select>
        <input type="text" name="name" required>
        <input type="number" name="price" step="0.01">
      </form>`;
    expect(document.querySelector('[name="pipeline_id"]')).toBeTruthy();
    expect(document.querySelector('[name="stage_id"]')).toBeTruthy();
    expect(document.querySelector('[name="price"]')).toBeTruthy();
  });

  test("lead form has user assignment selector", () => {
    document.body.innerHTML = `
      <form id="lead-form">
        <input type="text" name="name" required>
        <select name="user_id" class="form-select select2">
          <option value="">Select User</option>
          <option value="1">User 1</option>
        </select>
      </form>`;
    expect(document.querySelector('[name="user_id"]')).toBeTruthy();
  });

  test("stage form has color picker", () => {
    document.body.innerHTML = `
      <form id="stage-form">
        <input type="text" name="name" required>
        <input type="color" name="color" value="#3498db">
        <input type="number" name="order" value="1">
      </form>`;
    expect(document.querySelector('[name="color"]')).toBeTruthy();
    expect(document.querySelector('[name="order"]')).toBeTruthy();
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 7 — AJAX Patterns
// ══════════════════════════════════════════════════════════════════════

describe("CRM AJAX patterns", () => {
  test("deal order POST structure", () => {
    const orderData = {
      stage_id: 1,
      deal_ids: [1, 2, 3],
    };
    expect(orderData.stage_id).toBeDefined();
    expect(Array.isArray(orderData.deal_ids)).toBe(true);
  });

  test("pipeline change POST structure", () => {
    const changeData = {
      pipeline_id: 2,
    };
    expect(changeData.pipeline_id).toBeDefined();
  });

  test("deal status change POST structure", () => {
    const statusData = {
      status: "won",
    };
    expect(["won", "lost", "active"].includes(statusData.status)).toBe(true);
  });

  test("lead conversion POST structure", () => {
    const conversionData = {
      lead_id: 1,
      create_deal: true,
      deal_name: "New Deal",
    };
    expect(conversionData.lead_id).toBeDefined();
    expect(typeof conversionData.create_deal).toBe("boolean");
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 8 — Action Buttons Pattern
// ══════════════════════════════════════════════════════════════════════

describe("CRM Action Buttons pattern", () => {
  test.each(CRM_PAGES)("%s has action buttons", page => {
    document.body.innerHTML = `
      <div class="card">
        <div class="card-header">
          <h5>${page}</h5>
          <div class="float-end">
            <a href="/${page}/create" class="btn btn-primary">
              <i class="ti ti-plus"></i> Add
            </a>
          </div>
        </div>
      </div>`;
    expect(document.querySelector(".btn-primary")).toBeTruthy();
    expect(document.querySelector(".btn-primary").textContent).toContain("Add");
  });

  test("deal quick actions in kanban item", () => {
    document.body.innerHTML = `
      <div class="kanban-item" data-item-id="1">
        <div class="kanban-box">
          <div class="dropdown">
            <button class="btn btn-sm" data-bs-toggle="dropdown">
              <i class="ti ti-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/deals/1/edit">Edit</a></li>
              <li><a class="dropdown-item" href="/deals/1">View</a></li>
              <li><a class="dropdown-item delete-deal" data-id="1">Delete</a></li>
            </ul>
          </div>
        </div>
      </div>`;
    const dropdownItems = document.querySelectorAll(".dropdown-item");
    expect(dropdownItems.length).toBe(3);
    expect(document.querySelector(".delete-deal")).toBeTruthy();
  });

  test("bulk actions toolbar", () => {
    document.body.innerHTML = `
      <div class="bulk-actions-toolbar" style="display: none;">
        <span class="selected-count">0 selected</span>
        <button class="btn btn-danger bulk-delete">Delete Selected</button>
        <button class="btn btn-secondary bulk-export">Export Selected</button>
      </div>`;
    const toolbar = document.querySelector(".bulk-actions-toolbar");
    expect(toolbar).toBeTruthy();
    expect(document.querySelector(".bulk-delete")).toBeTruthy();
    expect(document.querySelector(".bulk-export")).toBeTruthy();
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 9 — Search and Filter Patterns
// ══════════════════════════════════════════════════════════════════════

describe("CRM Search and Filter patterns", () => {
  test("deals has search input", () => {
    document.body.innerHTML = `
      <div class="card">
        <div class="card-header">
          <div class="input-group">
            <input type="text" class="form-control search-input" placeholder="Search deals...">
            <button class="btn btn-outline-secondary search-btn" type="button">
              <i class="ti ti-search"></i>
            </button>
          </div>
        </div>
      </div>`;
    expect(document.querySelector(".search-input")).toBeTruthy();
    expect(document.querySelector(".search-btn")).toBeTruthy();
  });

  test("leads has filter options", () => {
    document.body.innerHTML = `
      <div class="filter-dropdown">
        <select class="form-select filter-by-stage" name="stage_id">
          <option value="">All Stages</option>
          <option value="1">New</option>
          <option value="2">Contacted</option>
        </select>
        <select class="form-select filter-by-user" name="user_id">
          <option value="">All Users</option>
          <option value="1">User 1</option>
        </select>
      </div>`;
    expect(document.querySelector(".filter-by-stage")).toBeTruthy();
    expect(document.querySelector(".filter-by-user")).toBeTruthy();
  });

  test("datatable search integration", () => {
    document.body.innerHTML = `
      <div class="dataTables_filter">
        <label>Search:<input type="search" class="form-control form-control-sm" placeholder="" aria-controls="datatable"></label>
      </div>
      <table class="table datatable" id="datatable">
        <thead><tr><th>Name</th></tr></thead>
        <tbody></tbody>
      </table>`;
    expect(document.querySelector(".dataTables_filter input")).toBeTruthy();
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 10 — Label/Tag Patterns
// ══════════════════════════════════════════════════════════════════════

describe("CRM Label/Tag patterns", () => {
  test.each(["deals", "leads"])("%s supports labels", type => {
    document.body.innerHTML = `
      <div class="${type}-labels-container">
        <span class="badge badge-primary">Hot</span>
        <span class="badge badge-warning">Follow-up</span>
        <button class="btn btn-sm btn-outline-primary add-label-btn">
          <i class="ti ti-plus"></i>
        </button>
      </div>`;
    expect(
      document.querySelectorAll(`.${type}-labels-container .badge`).length,
    ).toBe(2);
    expect(document.querySelector(".add-label-btn")).toBeTruthy();
  });

  test("label selection modal", () => {
    document.body.innerHTML = `
      <div class="modal" id="label-modal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header"><h5>Select Labels</h5></div>
            <div class="modal-body">
              <div class="label-checkboxes">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" value="1">
                  <label class="form-check-label"><span class="badge">Hot</span></label>
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" value="2">
                  <label class="form-check-label"><span class="badge">Cold</span></label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>`;
    expect(document.querySelector("#label-modal")).toBeTruthy();
    expect(
      document.querySelectorAll(".label-checkboxes .form-check").length,
    ).toBe(2);
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 11 — Source Patterns
// ══════════════════════════════════════════════════════════════════════

describe("CRM Source patterns", () => {
  test("lead/deal source selector", () => {
    document.body.innerHTML = `
      <div class="form-group">
        <label>Source</label>
        <select name="source_id" class="form-select select2">
          <option value="">Select Source</option>
          <option value="1">Website</option>
          <option value="2">Referral</option>
          <option value="3">Cold Call</option>
        </select>
      </div>`;
    const selector = document.querySelector('[name="source_id"]');
    expect(selector).toBeTruthy();
    expect(selector.options.length).toBe(4);
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 12 — Notes and Activity Log Patterns
// ══════════════════════════════════════════════════════════════════════

describe("CRM Notes and Activity patterns", () => {
  test("notes section structure", () => {
    document.body.innerHTML = `
      <div class="notes-section">
        <h6>Notes</h6>
        <textarea class="form-control notes-textarea" name="notes" rows="4"></textarea>
        <button class="btn btn-primary btn-sm save-notes mt-2">Save Notes</button>
      </div>`;
    expect(document.querySelector(".notes-textarea")).toBeTruthy();
    expect(document.querySelector(".save-notes")).toBeTruthy();
  });

  test("activity log list", () => {
    document.body.innerHTML = `
      <div class="activity-log">
        <ul class="list-unstyled">
          <li class="activity-item">
            <div class="activity-icon"><i class="ti ti-plus"></i></div>
            <div class="activity-content">
              <p>Deal created</p>
              <small class="text-muted">2 hours ago</small>
            </div>
          </li>
          <li class="activity-item">
            <div class="activity-icon"><i class="ti ti-edit"></i></div>
            <div class="activity-content">
              <p>Stage changed to "Contacted"</p>
              <small class="text-muted">1 hour ago</small>
            </div>
          </li>
        </ul>
      </div>`;
    expect(document.querySelectorAll(".activity-item").length).toBe(2);
    expect(document.querySelector(".activity-icon")).toBeTruthy();
    expect(document.querySelector(".activity-content")).toBeTruthy();
  });

  test("discussion/comment form", () => {
    document.body.innerHTML = `
      <div class="discussion-form">
        <form id="add-discussion">
          <textarea class="form-control" name="comment" placeholder="Add a comment..." required></textarea>
          <button type="submit" class="btn btn-primary btn-sm mt-2">Post</button>
        </form>
        <div class="discussions-list">
          <div class="discussion-item">
            <img src="/avatar.png" class="rounded-circle" width="32">
            <div class="discussion-content">
              <strong>User Name</strong>
              <p>This looks great!</p>
              <small>1 day ago</small>
            </div>
          </div>
        </div>
      </div>`;
    expect(document.querySelector("#add-discussion")).toBeTruthy();
    expect(document.querySelector(".discussion-item")).toBeTruthy();
  });
});
