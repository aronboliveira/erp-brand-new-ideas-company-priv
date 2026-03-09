#!/usr/bin/env node
/**
 * @file convert-route-guards.js
 * @description Automated conversion of route guard files to use ERPGuard singleton
 * @usage node convert-route-guards.js [--dry-run] [--batch-size=50]
 */

const fs = require('fs');
const path = require('path');

const ROUTES_DIR = path.join(__dirname, 'routes');
const DRY_RUN = process.argv.includes('--dry-run');
const BATCH_SIZE = parseInt(process.argv.find(arg => arg.startsWith('--batch-size='))?.split('=')[1] || '50');

// Conversion patterns
const patterns = [
  {
    name: 'Click Guard - Single Element by ID',
    detect: /getElementById\("([^"]+)"\)[\s\S]*?addEventListener\("click"/,
    transform: (content, match) => {
      const [, id] = match;
      const msgMatch = content.match(/getAttribute\("data-guard-msg"\)[^\n]*\?\?[^\n]*"([^"]+)"/);
      const msg = msgMatch ? msgMatch[1] : 'Route unavailable';
      return `/**
 * @file Auto-converted Route Guard
 * @description Guards route element using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard('#${id}', {
    fallbackMsg: '${msg}',
  });
})();`;
    }
  },
  {
    name: 'Click Guard - Multiple Elements by Selector',
    detect: /querySelectorAll\("([^"]+)"\)[\s\S]*?forEach.*addEventListener\("click"/,
    transform: (content, match) => {
      const [, selector] = match;
      const msgMatch = content.match(/getAttribute\("data-guard-msg"\)[^\n]*\?\?[^\n]*"([^"]+)"/);
      const msg = msgMatch ? msgMatch[1] : 'Route unavailable';
      return `/**
 * @file Auto-converted Route Guard
 * @description Guards route elements using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard('${selector}', {
    fallbackMsg: '${msg}',
  });
})();`;
    }
  },
  {
    name: 'Submit Guard - Form by ID',
    detect: /getElementById\("([^"]+)"\)[\s\S]*?addEventListener\("submit"/,
    transform: (content, match) => {
      const [, id] = match;
      const msgMatch = content.match(/getAttribute\("data-guard-msg"\)[^\n]*\?\?[^\n]*"([^"]+)"/);
      const msg = msgMatch ? msgMatch[1] : 'Route unavailable';
      return `/**
 * @file Auto-converted Route Guard
 * @description Guards form submission using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#${id}', {
    fallbackMsg: '${msg}',
  });
})();`;
    }
  },
  {
    name: 'Change Guard - Element by ID',
    detect: /getElementById\("([^"]+)"\)[\s\S]*?addEventListener\("change"/,
    transform: (content, match) => {
      const [, id] = match;
      const msgMatch = content.match(/getAttribute\("data-guard-msg"\)[^\n]*\?\?[^\n]*"([^"]+)"/);
      const msg = msgMatch ? msgMatch[1] : 'Route unavailable';
      return `/**
 * @file Auto-converted Route Guard
 * @description Guards element change using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindChangeGuard('#${id}', {
    fallbackMsg: '${msg}',
  });
})();`;
    }
  }
];

// Check if file should be converted
function shouldConvert(filePath, content) {
  if (content.includes('window.ERPGuard')) return false; // Already converted
  if (!content.includes('data-listener-active') && !content.includes('toast-container')) return false;
  if (content.length < 30) return false; // Too small
  if (content.split('\n').length > 200) return false; // Too complex, manual review needed
  return true;
}

// Try to convert file
function convertFile(filePath) {
  try {
    const content = fs.readFileSync(filePath, 'utf8');
    
    if (!shouldConvert(filePath, content)) {
      return { status: 'skip', reason: 'Already converted or too complex' };
    }

    for (const pattern of patterns) {
      const match = content.match(pattern.detect);
      if (match) {
        const newContent = pattern.transform(content, match);
        
        if (!DRY_RUN) {
          fs.writeFileSync(filePath, newContent, 'utf8');
        }
        
        const oldLines = content.split('\n').length;
        const newLines = newContent.split('\n').length;
        return {
          status: 'converted',
          pattern: pattern.name,
          oldLines,
          newLines,
          reduction: ((1 - newLines / oldLines) * 100).toFixed(1) + '%'
        };
      }
    }

    return { status: 'no-match', reason: 'No pattern matched' };
  } catch (err) {
    return { status: 'error', reason: err.message };
  }
}

// Recursively find JS files
function findJSFiles(dir, files = []) {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  
  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory() && entry.name !== 'lang') {
      findJSFiles(fullPath, files);
    } else if (entry.isFile() && entry.name.endsWith('.js')) {
      files.push(fullPath);
    }
  }
  
  return files;
}

// Main execution
function main() {
  console.log(`\n${'='.repeat(60)}`);
  console.log('Route Guard Conversion Script');
  console.log(`${'='.repeat(60)}\n`);
  console.log(`Mode: ${DRY_RUN ? 'DRY RUN (no changes)' : 'LIVE (writing files)'}`);
  console.log(`Batch size: ${BATCH_SIZE} files\n`);

  const files = findJSFiles(ROUTES_DIR);
  console.log(`Found ${files.length} JS files\n`);

  const stats = {
    converted: 0,
    skipped: 0,
    noMatch: 0,
    errors: 0,
    totalOldLines: 0,
    totalNewLines: 0
  };

  let batch = 0;
  for (let i = 0; i < files.length; i++) {
    if (i % BATCH_SIZE === 0) {
      batch++;
      console.log(`\nProcessing batch ${batch} (files ${i + 1}-${Math.min(i + BATCH_SIZE, files.length)})...`);
    }

    const file = files[i];
    const relativePath = path.relative(ROUTES_DIR, file);
    const result = convertFile(file);

    if (result.status === 'converted') {
      stats.converted++;
      stats.totalOldLines += result.oldLines;
      stats.totalNewLines += result.newLines;
      console.log(`  ✓ ${relativePath} (${result.oldLines} → ${result.newLines} lines, -${result.reduction})`);
    } else if (result.status === 'skip') {
      stats.skipped++;
    } else if (result.status === 'no-match') {
      stats.noMatch++;
    } else if (result.status === 'error') {
      stats.errors++;
      console.log(`  ✗ ${relativePath}: ${result.reason}`);
    }

    if (stats.converted >= BATCH_SIZE && DRY_RUN) {
      console.log(`\n[Dry run: stopping after ${BATCH_SIZE} successful conversions]`);
      break;
    }
  }

  console.log(`\n${'='.repeat(60)}`);
  console.log('Conversion Summary');
  console.log(`${'='.repeat(60)}\n`);
  console.log(`✓ Converted: ${stats.converted}`);
  console.log(`⊘ Skipped: ${stats.skipped}`);
  console.log(`? No match: ${stats.noMatch}`);
  console.log(`✗ Errors: ${stats.errors}`);
  
  if (stats.converted > 0) {
    const reduction = ((1 - stats.totalNewLines / stats.totalOldLines) * 100).toFixed(1);
    console.log(`\nTotal line reduction: ${stats.totalOldLines} → ${stats.totalNewLines} (-${reduction}%)`);
  }
  
  console.log(`\n${DRY_RUN ? 'Run without --dry-run to apply changes' : 'Conversion complete!'}\n`);
}

main();
