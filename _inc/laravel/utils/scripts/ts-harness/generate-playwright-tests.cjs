/**
 * Generate Playwright test specs from harness manifest
 * 
 * Usage: node generate-playwright-tests.cjs [manifest.json] [output-dir]
 */

const fs = require('fs');
const path = require('path');

const manifestFile = process.argv[2] || '_inc/laravel/ts/tests/harness/pages/_manifest.json';
const outputDir = process.argv[3] || '_inc/laravel/ts/tests/harness-specs';

const baseDir = path.resolve(__dirname, '../../..');

/**
 * Generate view type from route
 */
function getViewType(route) {
  const parts = route.split('.');
  const last = parts[parts.length - 1];
  if (['index', 'list'].includes(last)) return 'index';
  if (['create', 'store'].includes(last)) return 'create';
  if (['edit', 'update'].includes(last)) return 'edit';
  if (['show', 'view'].includes(last)) return 'show';
  if (['delete', 'destroy'].includes(last)) return 'delete';
  return 'generic';
}

/**
 * Generate test content based on view type
 */
function generateTestContent(route, viewType, htmlFile) {
  const pageUrl = `/harness/pages/${htmlFile}`;
  const resource = route.split('.')[0].replace(/_/g, ' ');
  
  let specificTests = '';
  
  switch (viewType) {
    case 'index':
      specificTests = `
  test('should have data table', async ({ page }) => {
    await page.goto('${pageUrl}');
    await expect(page.locator('#data-table')).toBeVisible();
  });

  test('should have action buttons', async ({ page }) => {
    await page.goto('${pageUrl}');
    await expect(page.locator('.view-btn').first()).toBeVisible();
    await expect(page.locator('.edit-btn').first()).toBeVisible();
    await expect(page.locator('.delete-btn').first()).toBeVisible();
  });

  test('should have create button', async ({ page }) => {
    await page.goto('${pageUrl}');
    await expect(page.locator('#create-btn')).toBeVisible();
  });`;
      break;
      
    case 'create':
      specificTests = `
  test('should have form', async ({ page }) => {
    await page.goto('${pageUrl}');
    await expect(page.locator('#main-form')).toBeVisible();
  });

  test('should have required inputs', async ({ page }) => {
    await page.goto('${pageUrl}');
    await expect(page.locator('#name')).toBeVisible();
    await expect(page.locator('#submit-btn')).toBeVisible();
  });

  test('form should have CSRF token', async ({ page }) => {
    await page.goto('${pageUrl}');
    const token = await page.locator('input[name="_token"]').getAttribute('value');
    expect(token).toBeTruthy();
  });`;
      break;
      
    case 'edit':
      specificTests = `
  test('should have form with existing values', async ({ page }) => {
    await page.goto('${pageUrl}');
    await expect(page.locator('#main-form')).toBeVisible();
    const name = await page.locator('#name').inputValue();
    expect(name).toBeTruthy();
  });

  test('should have method spoofing for PUT', async ({ page }) => {
    await page.goto('${pageUrl}');
    const method = await page.locator('input[name="_method"]').getAttribute('value');
    expect(method).toBe('PUT');
  });`;
      break;
      
    case 'show':
      specificTests = `
  test('should display detail view', async ({ page }) => {
    await page.goto('${pageUrl}');
    await expect(page.locator('#detail-view')).toBeVisible();
  });

  test('should have edit and back buttons', async ({ page }) => {
    await page.goto('${pageUrl}');
    await expect(page.locator('#edit-btn')).toBeVisible();
    await expect(page.locator('#back-btn')).toBeVisible();
  });`;
      break;
      
    case 'delete':
      specificTests = `
  test('should have delete form', async ({ page }) => {
    await page.goto('${pageUrl}');
    await expect(page.locator('#delete-form')).toBeVisible();
  });

  test('should have confirm button', async ({ page }) => {
    await page.goto('${pageUrl}');
    await expect(page.locator('#confirm-delete-btn')).toBeVisible();
  });

  test('should have method spoofing for DELETE', async ({ page }) => {
    await page.goto('${pageUrl}');
    const method = await page.locator('input[name="_method"]').getAttribute('value');
    expect(method).toBe('DELETE');
  });`;
      break;
      
    default:
      specificTests = `
  test('should have action button', async ({ page }) => {
    await page.goto('${pageUrl}');
    await expect(page.locator('#action-btn')).toBeVisible();
  });`;
  }

  return `
test.describe('${route}', () => {
  test('should load without JavaScript errors', async ({ page }) => {
    const errors: string[] = [];
    page.on('pageerror', err => errors.push(err.message));
    
    await page.goto('${pageUrl}');
    await page.waitForLoadState('networkidle');
    
    // Allow some time for scripts to execute
    await page.waitForTimeout(500);
    
    expect(errors).toHaveLength(0);
  });

  test('should show pass status', async ({ page }) => {
    await page.goto('${pageUrl}');
    await page.waitForLoadState('load');
    await page.waitForTimeout(1000);
    
    const status = page.locator('#test-status');
    await expect(status).toHaveClass(/pass/);
  });
${specificTests}
});
`;
}

/**
 * Group routes by resource
 */
function groupByResource(files) {
  const groups = {};
  
  for (const file of files) {
    const resource = file.route.split('.')[0];
    if (!groups[resource]) {
      groups[resource] = [];
    }
    groups[resource].push(file);
  }
  
  return groups;
}

// Main execution
console.log('Loading manifest from:', manifestFile);

const manifest = JSON.parse(fs.readFileSync(path.resolve(baseDir, manifestFile), 'utf-8'));
const files = manifest.files;

console.log(`Found ${files.length} harness pages`);

const outputPath = path.resolve(baseDir, outputDir);
fs.mkdirSync(outputPath, { recursive: true });

// Group by resource for organized test files
const groups = groupByResource(files);

let totalTests = 0;
let totalFiles = 0;

for (const [resource, resourceFiles] of Object.entries(groups)) {
  // Create one test file per resource
  const filename = `${resource}.spec.ts`;
  const filepath = path.join(outputPath, filename);
  
  let content = `/**
 * Auto-generated Playwright tests for ${resource} routes
 * Generated: ${new Date().toISOString()}
 * 
 * Routes covered: ${resourceFiles.map(f => f.route).join(', ')}
 */
import { test, expect } from '@playwright/test';

`;

  for (const file of resourceFiles) {
    const viewType = getViewType(file.route);
    const testContent = generateTestContent(file.route, viewType, file.file);
    content += testContent + '\n\n';
    
    // Count tests (approximate: 3-5 per route)
    totalTests += viewType === 'generic' ? 3 : 5;
  }
  
  fs.writeFileSync(filepath, content);
  totalFiles++;
  console.log(`  Generated: ${filename} (${resourceFiles.length} routes)`);
}

// Keep the existing attendances-delete.spec.ts if it exists
const existingSpec = path.join(outputPath, 'attendances-delete.spec.ts');
if (!fs.existsSync(existingSpec)) {
  console.log('Note: attendances-delete.spec.ts was not found, may have been overwritten');
}

console.log(`\nTotal: ${totalFiles} test files, ~${totalTests} tests`);
console.log(`Output: ${outputPath}`);
