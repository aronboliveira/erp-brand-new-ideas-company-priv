/**
 * Generate mock HTML harness pages from view-JS mapping
 * 
 * Usage: node generate-harness.cjs [view-map.json] [output-dir]
 */

const fs = require('fs');
const path = require('path');

const mapFile = process.argv[2] || '.tmp/copilot/view-js-map.json';
const outputDir = process.argv[3] || '_inc/laravel/ts/tests/harness/pages';

const baseDir = path.resolve(__dirname, '../../..');

/**
 * Generate base layout HTML template
 */
function getBaseLayout() {
  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="test-csrf-token">
  <title>{{TITLE}} - Test Harness</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
        rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
        crossorigin="anonymous">
  <style>
    .test-status { padding: 1rem; margin: 1rem 0; border-radius: 0.5rem; }
    .test-status.pass { background: #d4edda; color: #155724; }
    .test-status.fail { background: #f8d7da; color: #721c24; }
    .test-status.loading { background: #fff3cd; color: #856404; }
    .mock-data { display: none; }
  </style>
</head>
<body>
  <div class="container-fluid py-3">
    <h1 class="h4 mb-3">{{TITLE}}</h1>
    <div class="test-status loading" id="test-status">Loading scripts...</div>
    
    <!-- Mock content area -->
    {{CONTENT}}
    
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
          integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
          crossorigin="anonymous"></script>
  
  {{SCRIPTS}}
  
  <script type="module">
    window.addEventListener('load', () => {
      const status = document.getElementById('test-status');
      // Check if scripts loaded without errors
      const hasErrors = window.__testErrors?.length > 0;
      status.textContent = hasErrors ? 'Script errors detected' : 'Scripts loaded successfully';
      status.className = 'test-status ' + (hasErrors ? 'fail' : 'pass');
    });
    window.__testErrors = [];
    window.onerror = (msg, url, line, col, err) => {
      window.__testErrors.push({ msg, url, line, col, err: err?.message });
    };
  </script>
</body>
</html>`;
}

/**
 * Generate mock content based on view type
 */
function generateMockContent(routeName, jsRefs) {
  const type = getViewType(routeName);
  
  switch (type) {
    case 'index':
      return generateIndexContent(routeName);
    case 'create':
      return generateFormContent(routeName, 'create');
    case 'edit':
      return generateFormContent(routeName, 'edit');
    case 'show':
      return generateShowContent(routeName);
    case 'delete':
      return generateDeleteContent(routeName);
    default:
      return generateGenericContent(routeName);
  }
}

function getViewType(routeName) {
  const parts = routeName.split('.');
  const last = parts[parts.length - 1];
  if (['index', 'list'].includes(last)) return 'index';
  if (['create', 'store'].includes(last)) return 'create';
  if (['edit', 'update'].includes(last)) return 'edit';
  if (['show', 'view'].includes(last)) return 'show';
  if (['delete', 'destroy'].includes(last)) return 'delete';
  return 'generic';
}

function generateIndexContent(routeName) {
  const resource = routeName.split('.')[0];
  return `
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>${resource} List</span>
        <a href="#" class="btn btn-primary btn-sm" id="create-btn">Create New</a>
      </div>
      <div class="card-body">
        <table class="table table-striped" id="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr data-id="1">
              <td>1</td>
              <td>Test Item 1</td>
              <td><span class="badge bg-success">Active</span></td>
              <td>
                <a href="#" class="btn btn-sm btn-info view-btn" data-id="1">View</a>
                <a href="#" class="btn btn-sm btn-warning edit-btn" data-id="1">Edit</a>
                <a href="#" class="btn btn-sm btn-danger delete-btn" data-id="1" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</a>
              </td>
            </tr>
            <tr data-id="2">
              <td>2</td>
              <td>Test Item 2</td>
              <td><span class="badge bg-warning">Pending</span></td>
              <td>
                <a href="#" class="btn btn-sm btn-info view-btn" data-id="2">View</a>
                <a href="#" class="btn btn-sm btn-warning edit-btn" data-id="2">Edit</a>
                <a href="#" class="btn btn-sm btn-danger delete-btn" data-id="2" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this item?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
          </div>
        </div>
      </div>
    </div>`;
}

function generateFormContent(routeName, action) {
  const resource = routeName.split('.')[0];
  const isEdit = action === 'edit';
  return `
    <div class="card">
      <div class="card-header">${isEdit ? 'Edit' : 'Create'} ${resource}</div>
      <div class="card-body">
        <form id="main-form" action="#" method="POST">
          <input type="hidden" name="_token" value="test-csrf-token">
          ${isEdit ? '<input type="hidden" name="_method" value="PUT">' : ''}
          
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="${isEdit ? 'Test Item' : ''}" required>
            <div class="invalid-feedback">Name is required</div>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3">${isEdit ? 'Test description' : ''}</textarea>
          </div>
          
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
              <option value="active" ${isEdit ? 'selected' : ''}>Active</option>
              <option value="inactive">Inactive</option>
              <option value="pending">Pending</option>
            </select>
          </div>
          
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" id="submit-btn">${isEdit ? 'Update' : 'Create'}</button>
            <a href="#" class="btn btn-secondary" id="cancel-btn">Cancel</a>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Toast container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="successToast" class="toast" role="alert">
        <div class="toast-header">
          <strong class="me-auto">Success</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">Operation completed successfully.</div>
      </div>
      <div id="errorToast" class="toast bg-danger text-white" role="alert">
        <div class="toast-header bg-danger text-white">
          <strong class="me-auto">Error</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">An error occurred.</div>
      </div>
    </div>`;
}

function generateShowContent(routeName) {
  const resource = routeName.split('.')[0];
  return `
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>${resource} Details</span>
        <div>
          <a href="#" class="btn btn-warning btn-sm" id="edit-btn">Edit</a>
          <a href="#" class="btn btn-secondary btn-sm" id="back-btn">Back</a>
        </div>
      </div>
      <div class="card-body">
        <dl class="row" id="detail-view">
          <dt class="col-sm-3">ID</dt>
          <dd class="col-sm-9">1</dd>
          
          <dt class="col-sm-3">Name</dt>
          <dd class="col-sm-9">Test Item</dd>
          
          <dt class="col-sm-3">Description</dt>
          <dd class="col-sm-9">This is a test item for harness testing</dd>
          
          <dt class="col-sm-3">Status</dt>
          <dd class="col-sm-9"><span class="badge bg-success">Active</span></dd>
          
          <dt class="col-sm-3">Created At</dt>
          <dd class="col-sm-9">2026-03-08 10:00:00</dd>
        </dl>
      </div>
    </div>`;
}

function generateDeleteContent(routeName) {
  const resource = routeName.split('.')[0];
  return `
    <div class="card">
      <div class="card-header bg-danger text-white">Confirm Delete</div>
      <div class="card-body">
        <p class="lead">Are you sure you want to delete this ${resource}?</p>
        
        <div class="alert alert-warning">
          <strong>Warning:</strong> This action cannot be undone.
        </div>
        
        <div class="mock-data" id="item-data" data-id="1" data-name="Test Item"></div>
        
        <form id="delete-form" action="#" method="POST">
          <input type="hidden" name="_token" value="test-csrf-token">
          <input type="hidden" name="_method" value="DELETE">
          <input type="hidden" name="id" value="1">
          
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-danger" id="confirm-delete-btn">Delete</button>
            <a href="#" class="btn btn-secondary" id="cancel-btn">Cancel</a>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Data display for testing -->
    <div class="card mt-3">
      <div class="card-header">Delete Links (for testing)</div>
      <div class="card-body">
        <ul class="list-group">
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Item 1
            <a href="#" class="delete-link text-danger" data-id="1">Delete</a>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Item 2
            <a href="/test/delete/2" class="delete-link text-danger" data-id="2">Delete</a>
          </li>
        </ul>
      </div>
    </div>`;
}

function generateGenericContent(routeName) {
  return `
    <div class="card">
      <div class="card-header">${routeName}</div>
      <div class="card-body">
        <p>Generic test content for route: <code>${routeName}</code></p>
        
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Sample Input</label>
              <input type="text" class="form-control" id="sample-input" placeholder="Enter value">
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Sample Select</label>
              <select class="form-select" id="sample-select">
                <option value="1">Option 1</option>
                <option value="2">Option 2</option>
              </select>
            </div>
          </div>
        </div>
        
        <button type="button" class="btn btn-primary" id="action-btn">Execute Action</button>
      </div>
    </div>`;
}

/**
 * Convert asset path to dist path
 */
function assetToDistPath(assetPath) {
  // Convert assets/js/routes/foo/bar.js -> /dist/public/assets/js/routes/foo/bar.js
  if (assetPath.startsWith('assets/js/')) {
    return '/dist/public/' + assetPath;
  }
  // For libs and vendor files, return null (we'll use CDN or skip)
  if (assetPath.includes('libs/') || assetPath.includes('vendor/')) {
    return null;
  }
  return '/dist/public/' + assetPath;
}

/**
 * Generate script tags from JS refs
 */
function generateScriptTags(jsRefs) {
  const scripts = [];
  
  for (const ref of jsRefs) {
    const distPath = assetToDistPath(ref.path);
    if (distPath && ref.path.includes('/routes/')) {
      scripts.push(`  <script type="module" src="${distPath}"></script>`);
    }
  }
  
  return scripts.join('\n');
}

/**
 * Sanitize route name to valid filename
 */
function routeToFilename(routeName) {
  return routeName.replace(/\./g, '-').replace(/[^a-z0-9-]/gi, '_');
}

/**
 * Generate harness page for a view
 */
function generateHarnessPage(viewData) {
  const { route, path: viewPath, js_refs } = viewData;
  
  const content = generateMockContent(route, js_refs);
  const scripts = generateScriptTags(js_refs);
  
  let html = getBaseLayout();
  html = html.replace(/\{\{TITLE\}\}/g, route);
  html = html.replace('{{CONTENT}}', content);
  html = html.replace('{{SCRIPTS}}', scripts);
  
  return html;
}

// Main execution
console.log('Loading view map from:', mapFile);

const mapData = JSON.parse(fs.readFileSync(path.resolve(baseDir, mapFile), 'utf-8'));
const views = mapData.views;

console.log(`Found ${Object.keys(views).length} views with JS references`);

const outputPath = path.resolve(baseDir, outputDir);
fs.mkdirSync(outputPath, { recursive: true });

let generated = 0;
let skipped = 0;
const generated_files = [];

for (const [viewPath, viewData] of Object.entries(views)) {
  // Skip views without route JS files
  const hasRouteJs = viewData.js_refs.some(ref => ref.path.includes('/routes/'));
  if (!hasRouteJs) {
    skipped++;
    continue;
  }
  
  const filename = routeToFilename(viewData.route) + '.html';
  const filepath = path.join(outputPath, filename);
  
  const html = generateHarnessPage(viewData);
  fs.writeFileSync(filepath, html);
  
  generated++;
  generated_files.push({
    route: viewData.route,
    file: filename,
    js_refs: viewData.js_refs.filter(r => r.path.includes('/routes/')).map(r => r.path)
  });
}

// Write manifest
const manifest = {
  generated_at: new Date().toISOString(),
  total_generated: generated,
  total_skipped: skipped,
  files: generated_files
};

fs.writeFileSync(
  path.join(outputPath, '_manifest.json'),
  JSON.stringify(manifest, null, 2)
);

console.log(`Generated: ${generated} harness pages`);
console.log(`Skipped: ${skipped} views (no route JS)`);
console.log(`Manifest: ${path.join(outputPath, '_manifest.json')}`);
