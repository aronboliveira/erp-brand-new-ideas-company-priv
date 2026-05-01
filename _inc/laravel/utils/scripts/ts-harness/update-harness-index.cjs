/**
 * Update harness index.html with generated pages from manifest
 * 
 * Usage: node update-harness-index.cjs
 */

const fs = require('fs');
const path = require('path');

const baseDir = path.resolve(__dirname, '../../..');
const manifestPath = path.join(baseDir, '_inc/laravel/ts/tests/harness/pages/_manifest.json');
const indexPath = path.join(baseDir, '_inc/laravel/ts/tests/harness/index.html');

const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf-8'));

// Group by resource
const groups = {};
for (const file of manifest.files) {
  const resource = file.route.split('.')[0];
  if (!groups[resource]) {
    groups[resource] = [];
  }
  groups[resource].push(file);
}

// Sort groups alphabetically
const sortedGroups = Object.keys(groups).sort();

let listHtml = '';
for (const resource of sortedGroups) {
  const files = groups[resource];
  listHtml += `
      <div class="col-md-4 col-lg-3 mb-3">
        <div class="card h-100">
          <div class="card-header py-2">
            <strong>${resource.replace(/_/g, ' ')}</strong>
            <span class="badge bg-secondary float-end">${files.length}</span>
          </div>
          <ul class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">`;
  
  for (const file of files) {
    const action = file.route.split('.').pop();
    listHtml += `
            <li class="list-group-item py-1">
              <a href="pages/${file.file}" class="text-decoration-none">${action}</a>
            </li>`;
  }
  
  listHtml += `
          </ul>
        </div>
      </div>`;
}

const html = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test Harness Index - ERP Brand New Ideas Company</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
        rel="stylesheet" crossorigin="anonymous">
  <style>
    .search-box { position: sticky; top: 0; z-index: 100; background: white; padding: 1rem 0; }
    .card-header { font-size: 0.9rem; }
    .list-group-item { font-size: 0.85rem; }
  </style>
</head>
<body>
  <div class="container-fluid py-3">
    <h1 class="h3 mb-3">Test Harness - ERP Brand New Ideas Company</h1>
    
    <div class="search-box mb-3">
      <input type="text" class="form-control" id="search" placeholder="Filter pages...">
      <div class="mt-2">
        <span class="badge bg-primary">${manifest.total_generated} pages</span>
        <span class="badge bg-info">${sortedGroups.length} resources</span>
        <small class="text-muted ms-2">Generated: ${manifest.generated_at}</small>
      </div>
    </div>
    
    <div class="row" id="page-list">
      ${listHtml}
    </div>
  </div>
  
  <script>
    document.getElementById('search').addEventListener('input', (e) => {
      const term = e.target.value.toLowerCase();
      document.querySelectorAll('#page-list .col-md-4').forEach(col => {
        const text = col.textContent.toLowerCase();
        col.style.display = text.includes(term) ? '' : 'none';
      });
    });
  </script>
</body>
</html>`;

fs.writeFileSync(indexPath, html);
console.log(`Updated index with ${manifest.total_generated} pages in ${sortedGroups.length} resource groups`);
