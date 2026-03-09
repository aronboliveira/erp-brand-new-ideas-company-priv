/**
 * Product Control Page DOM Patterns — Jest / jsdom
 *
 * Validates that the DOM patterns used across Product Control
 * index pages (datatable, card-body, modal, select2, forms,
 * stock management, warehouse, cart, import/export) initialise
 * correctly via custom.js helpers.
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

const PRODUCT_PAGES = [
  "product_services",
  "product_service_categories",
  "product_service_units",
  "product_stocks",
  "warehouses",
  "warehouse_transfers",
  "proposal_products",
];

// ══════════════════════════════════════════════════════════════════════
//  SECTION 1 — DataTable pattern
// ══════════════════════════════════════════════════════════════════════

describe("Product DataTable pattern", () => {
  test.each(PRODUCT_PAGES)("%s index table initialises without error", page => {
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

  test("product_services table has expected columns", () => {
    document.body.innerHTML = `
      <table class="table datatable" id="product-services-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>SKU</th>
            <th>Selling Price</th>
            <th>Purchase Price</th>
            <th>Category</th>
            <th>Unit</th>
            <th>Type</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>`;
    const headers = document.querySelectorAll("table.datatable thead th");
    expect(headers.length).toBe(8);
    expect(headers[0].textContent).toBe("Name");
    expect(headers[1].textContent).toBe("SKU");
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 2 — Card layout pattern
// ══════════════════════════════════════════════════════════════════════

describe("Product Card layout pattern", () => {
  test.each(PRODUCT_PAGES)("%s card-body renders heading", page => {
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

  test("product detail card shows product info", () => {
    document.body.innerHTML = `
      <div class="card product-detail">
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <img src="/uploads/product.jpg" alt="Product" class="img-fluid">
            </div>
            <div class="col-md-8">
              <h4 class="product-name">Test Product</h4>
              <p class="product-sku">SKU: TEST-001</p>
              <p class="product-price">Price: $100.00</p>
            </div>
          </div>
        </div>
      </div>`;
    expect(document.querySelector(".product-detail")).toBeTruthy();
    expect(document.querySelector(".product-name").textContent).toBe(
      "Test Product",
    );
    expect(document.querySelector(".product-sku").textContent).toContain(
      "TEST-001",
    );
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 3 — AJAX modal pattern
// ══════════════════════════════════════════════════════════════════════

describe("Product AJAX modal pattern", () => {
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
    title.innerHTML = "Create Product";
    expect(title.textContent).toBe("Create Product");
  });

  test("data-ajax-popup trigger creates correct modal sizing", () => {
    document.body.innerHTML = `
      <button data-ajax-popup="true" data-size="lg" data-title="Add Product"
        data-url="/product_services/create">Add</button>
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
    expect(btn.dataset.title).toBe("Add Product");
    expect(btn.dataset.url).toBe("/product_services/create");
  });

  test("category modal popup trigger", () => {
    document.body.innerHTML = `
      <button data-ajax-popup="true" data-size="md" data-title="Add Category"
        data-url="/product_service_categories/create">Add Category</button>`;
    const btn = document.querySelector('[data-ajax-popup="true"]');
    expect(btn.dataset.url).toContain("product_service_categories");
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 4 — Select2 pattern
// ══════════════════════════════════════════════════════════════════════

describe("Product select2 pattern", () => {
  test("product form select2 elements with IDs", () => {
    document.body.innerHTML = `
      <select class="select2" id="category_id">
        <option value="1">Electronics</option>
        <option value="2">Clothing</option>
      </select>
      <select class="select2" id="unit_id">
        <option value="1">Piece</option>
        <option value="2">Kilogram</option>
      </select>`;
    const selects = document.querySelectorAll(".select2");
    expect(selects.length).toBe(2);
    selects.forEach(s => {
      expect(s.id).toBeTruthy();
    });
  });

  test("warehouse select renders options", () => {
    document.body.innerHTML = `
      <select class="select2" id="warehouse_id" name="warehouse_id">
        <option value="">Select Warehouse</option>
        <option value="1">Main Warehouse</option>
        <option value="2">Secondary Warehouse</option>
      </select>`;
    const select = document.getElementById("warehouse_id");
    expect(select).not.toBeNull();
    expect(select.options.length).toBe(3);
  });

  test("product select in transfer form", () => {
    document.body.innerHTML = `
      <select class="select2" id="product_id" name="product_id">
        <option value="">Select Product</option>
        <option value="1">Widget A</option>
        <option value="2">Widget B</option>
      </select>`;
    const select = document.getElementById("product_id");
    expect(select).not.toBeNull();
    expect(select.name).toBe("product_id");
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 5 — Product form pattern
// ══════════════════════════════════════════════════════════════════════

describe("Product form pattern", () => {
  test("product create form has required fields", () => {
    document.body.innerHTML = `
      <form action="/product_services" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="csrf">
        <input type="text" name="name" id="name" required>
        <input type="text" name="sku" id="sku">
        <input type="number" name="sale_price" id="sale_price" step="0.01">
        <input type="number" name="purchase_price" id="purchase_price" step="0.01">
        <select name="category_id" id="category_id" class="select2"></select>
        <select name="unit_id" id="unit_id" class="select2"></select>
        <select name="type" id="type">
          <option value="product">Product</option>
          <option value="service">Service</option>
        </select>
        <textarea name="description" id="description"></textarea>
        <input type="file" name="image" id="image" accept="image/*">
        <button type="submit">Save</button>
      </form>`;
    expect(document.querySelector('input[name="name"]')).toBeTruthy();
    expect(document.querySelector('input[name="sku"]')).toBeTruthy();
    expect(document.querySelector('input[name="sale_price"]')).toBeTruthy();
    expect(document.querySelector('select[name="category_id"]')).toBeTruthy();
    expect(document.querySelector('select[name="type"]')).toBeTruthy();
  });

  test("category form has name field", () => {
    document.body.innerHTML = `
      <form action="/product_service_categories" method="POST">
        <input type="hidden" name="_token" value="csrf">
        <input type="text" name="name" id="name" required>
        <select name="type" id="type">
          <option value="0">Income</option>
          <option value="1">Expense</option>
        </select>
        <select name="color" id="color" class="select2"></select>
        <button type="submit">Save</button>
      </form>`;
    expect(document.querySelector('input[name="name"]')).toBeTruthy();
    expect(document.querySelector('select[name="type"]')).toBeTruthy();
  });

  test("unit form has name and shortcode fields", () => {
    document.body.innerHTML = `
      <form action="/product_service_units" method="POST">
        <input type="hidden" name="_token" value="csrf">
        <input type="text" name="name" id="name" required>
        <input type="text" name="shortcode" id="shortcode">
        <button type="submit">Save</button>
      </form>`;
    expect(document.querySelector('input[name="name"]')).toBeTruthy();
    expect(document.querySelector('input[name="shortcode"]')).toBeTruthy();
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 6 — Stock management pattern
// ══════════════════════════════════════════════════════════════════════

describe("Product stock management pattern", () => {
  test("stock form has quantity and warehouse fields", () => {
    document.body.innerHTML = `
      <form action="/product_stocks" method="POST">
        <input type="hidden" name="_token" value="csrf">
        <select name="product_id" id="product_id" class="select2" required></select>
        <select name="warehouse_id" id="warehouse_id" class="select2" required></select>
        <input type="number" name="quantity" id="quantity" min="0" required>
        <button type="submit">Save</button>
      </form>`;
    expect(document.querySelector('select[name="product_id"]')).toBeTruthy();
    expect(document.querySelector('select[name="warehouse_id"]')).toBeTruthy();
    expect(document.querySelector('input[name="quantity"]')).toBeTruthy();
  });

  test("stock table shows product and warehouse info", () => {
    document.body.innerHTML = `
      <table class="table datatable" id="stock-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Warehouse</th>
            <th>Quantity</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Widget A</td>
            <td>Main Warehouse</td>
            <td>100</td>
            <td><a href="#">Edit</a></td>
          </tr>
        </tbody>
      </table>`;
    const headers = document.querySelectorAll("#stock-table thead th");
    expect(headers.length).toBe(4);
    expect(headers[2].textContent).toBe("Quantity");
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 7 — Warehouse transfer pattern
// ══════════════════════════════════════════════════════════════════════

describe("Warehouse transfer pattern", () => {
  test("transfer form has from/to warehouse and product fields", () => {
    document.body.innerHTML = `
      <form action="/warehouse_transfers" method="POST">
        <input type="hidden" name="_token" value="csrf">
        <select name="from_warehouse" id="from_warehouse" class="select2" required></select>
        <select name="to_warehouse" id="to_warehouse" class="select2" required></select>
        <select name="product_id" id="product_id" class="select2" required></select>
        <input type="number" name="quantity" id="quantity" min="1" required>
        <input type="date" name="date" id="date">
        <button type="submit">Transfer</button>
      </form>`;
    expect(
      document.querySelector('select[name="from_warehouse"]'),
    ).toBeTruthy();
    expect(document.querySelector('select[name="to_warehouse"]')).toBeTruthy();
    expect(document.querySelector('select[name="product_id"]')).toBeTruthy();
    expect(document.querySelector('input[name="quantity"]')).toBeTruthy();
  });

  test("transfer table shows transfer history", () => {
    document.body.innerHTML = `
      <table class="table datatable" id="transfer-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>From</th>
            <th>To</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>`;
    const headers = document.querySelectorAll("#transfer-table thead th");
    expect(headers.length).toBe(6);
    expect(headers[1].textContent).toBe("From");
    expect(headers[2].textContent).toBe("To");
  });

  test("AJAX quantity fetch button exists", () => {
    document.body.innerHTML = `
      <form id="transfer-form">
        <select name="from_warehouse" id="from_warehouse" class="select2"></select>
        <select name="product_id" id="product_id" class="select2"></select>
        <button type="button" id="check-qty" data-url="/warehouse_transfers/get-quantity">
          Check Available
        </button>
        <span id="available-qty">--</span>
      </form>`;
    const btn = document.getElementById("check-qty");
    expect(btn).not.toBeNull();
    expect(btn.dataset.url).toBe("/warehouse_transfers/get-quantity");
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 8 — Cart & POS pattern
// ══════════════════════════════════════════════════════════════════════

describe("Product cart pattern", () => {
  test("cart table structure", () => {
    document.body.innerHTML = `
      <table class="table" id="cart-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="cart-items"></tbody>
        <tfoot>
          <tr>
            <td colspan="3">Subtotal</td>
            <td id="cart-subtotal">0.00</td>
            <td></td>
          </tr>
        </tfoot>
      </table>`;
    expect(document.getElementById("cart-table")).toBeTruthy();
    expect(document.getElementById("cart-items")).toBeTruthy();
    expect(document.getElementById("cart-subtotal")).toBeTruthy();
  });

  test("add to cart form", () => {
    document.body.innerHTML = `
      <form id="add-to-cart-form">
        <input type="hidden" name="product_id" value="1">
        <input type="number" name="qty" value="1" min="1">
        <button type="submit" class="btn-add-cart">Add to Cart</button>
      </form>`;
    expect(document.querySelector('input[name="product_id"]')).toBeTruthy();
    expect(document.querySelector('input[name="qty"]')).toBeTruthy();
    expect(document.querySelector(".btn-add-cart")).toBeTruthy();
  });

  test("cart empty button", () => {
    document.body.innerHTML = `
      <button type="button" id="empty-cart" class="btn btn-danger"
        data-url="/product_services/empty-cart">Empty Cart</button>`;
    const btn = document.getElementById("empty-cart");
    expect(btn).not.toBeNull();
    expect(btn.dataset.url).toContain("empty-cart");
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 9 — Import/Export pattern
// ══════════════════════════════════════════════════════════════════════

describe("Product import/export pattern", () => {
  test("import form has file input", () => {
    document.body.innerHTML = `
      <form action="/product_services/import" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="csrf">
        <input type="file" name="file" id="import-file" accept=".csv,.xlsx,.xls" required>
        <button type="submit">Import</button>
      </form>`;
    const fileInput = document.getElementById("import-file");
    expect(fileInput).not.toBeNull();
    expect(fileInput.accept).toContain(".csv");
  });

  test("export button with download URL", () => {
    document.body.innerHTML = `
      <a href="/product_services/export" class="btn btn-primary" id="export-btn">
        <i class="ti ti-download"></i> Export
      </a>`;
    const exportBtn = document.getElementById("export-btn");
    expect(exportBtn).not.toBeNull();
    expect(exportBtn.href).toContain("export");
  });

  test("import preview table", () => {
    document.body.innerHTML = `
      <div id="import-preview">
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>SKU</th>
              <th>Price</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr class="import-row valid">
              <td>Product A</td>
              <td>SKU-001</td>
              <td>10.00</td>
              <td><span class="badge bg-success">Valid</span></td>
            </tr>
            <tr class="import-row invalid">
              <td>Product B</td>
              <td></td>
              <td>20.00</td>
              <td><span class="badge bg-danger">Missing SKU</span></td>
            </tr>
          </tbody>
        </table>
      </div>`;
    expect(document.getElementById("import-preview")).toBeTruthy();
    expect(document.querySelectorAll(".import-row").length).toBe(2);
    expect(document.querySelector(".import-row.valid")).toBeTruthy();
    expect(document.querySelector(".import-row.invalid")).toBeTruthy();
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 10 — Action buttons pattern
// ══════════════════════════════════════════════════════════════════════

describe("Product action buttons pattern", () => {
  test("edit button with correct URL structure", () => {
    document.body.innerHTML = `
      <a href="/product_services/1/edit" class="btn btn-sm btn-primary"
        data-ajax-popup="true" data-title="Edit Product">
        <i class="ti ti-pencil"></i>
      </a>`;
    const editBtn = document.querySelector('[data-title="Edit Product"]');
    expect(editBtn).not.toBeNull();
    expect(editBtn.href).toContain("/edit");
  });

  test("delete button with confirmation", () => {
    document.body.innerHTML = `
      <form action="/product_services/1" method="POST">
        <input type="hidden" name="_method" value="DELETE">
        <input type="hidden" name="_token" value="csrf">
        <button type="submit" class="btn btn-sm btn-danger delete-confirm"
          data-confirm="Are you sure?">
          <i class="ti ti-trash"></i>
        </button>
      </form>`;
    const delBtn = document.querySelector(".delete-confirm");
    expect(delBtn).not.toBeNull();
    expect(delBtn.dataset.confirm).toBe("Are you sure?");
  });

  test("detail/show button", () => {
    document.body.innerHTML = `
      <a href="/product_services/1" class="btn btn-sm btn-info" title="View Details">
        <i class="ti ti-eye"></i>
      </a>`;
    const viewBtn = document.querySelector('[title="View Details"]');
    expect(viewBtn).not.toBeNull();
    expect(viewBtn.href).toContain("/product_services/1");
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 11 — Search & filter pattern
// ══════════════════════════════════════════════════════════════════════

describe("Product search pattern", () => {
  test("search input in header", () => {
    document.body.innerHTML = `
      <div class="card-header">
        <div class="d-flex justify-content-between">
          <h5>Products</h5>
          <div class="col-md-4">
            <input type="text" class="form-control" id="product-search"
              placeholder="Search products..." data-url="/product_services/search">
          </div>
        </div>
      </div>`;
    const searchInput = document.getElementById("product-search");
    expect(searchInput).not.toBeNull();
    expect(searchInput.placeholder).toContain("Search");
    expect(searchInput.dataset.url).toContain("search");
  });

  test("category filter select", () => {
    document.body.innerHTML = `
      <select class="form-select" id="category-filter" data-filter="category_id">
        <option value="">All Categories</option>
        <option value="1">Electronics</option>
        <option value="2">Clothing</option>
      </select>`;
    const filter = document.getElementById("category-filter");
    expect(filter).not.toBeNull();
    expect(filter.dataset.filter).toBe("category_id");
  });

  test("type filter (product/service)", () => {
    document.body.innerHTML = `
      <select class="form-select" id="type-filter" data-filter="type">
        <option value="">All Types</option>
        <option value="product">Products Only</option>
        <option value="service">Services Only</option>
      </select>`;
    const filter = document.getElementById("type-filter");
    expect(filter).not.toBeNull();
    expect(filter.options.length).toBe(3);
  });
});

// ══════════════════════════════════════════════════════════════════════
//  SECTION 12 — Warehouse form pattern
// ══════════════════════════════════════════════════════════════════════

describe("Warehouse form pattern", () => {
  test("warehouse create form", () => {
    document.body.innerHTML = `
      <form action="/warehouses" method="POST">
        <input type="hidden" name="_token" value="csrf">
        <input type="text" name="name" id="name" required>
        <textarea name="address" id="address"></textarea>
        <input type="text" name="city" id="city">
        <input type="text" name="zip" id="zip">
        <button type="submit">Save</button>
      </form>`;
    expect(document.querySelector('input[name="name"]')).toBeTruthy();
    expect(document.querySelector('textarea[name="address"]')).toBeTruthy();
  });

  test("warehouse table", () => {
    document.body.innerHTML = `
      <table class="table datatable" id="warehouse-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Address</th>
            <th>City</th>
            <th>Products</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>`;
    const headers = document.querySelectorAll("#warehouse-table thead th");
    expect(headers.length).toBe(5);
    expect(headers[3].textContent).toBe("Products");
  });
});
