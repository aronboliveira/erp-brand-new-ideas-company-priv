#!/usr/bin/env node
/* global __dirname, console */
/**
 * cjs-to-ts-tests.cjs
 * Converts CJS test files → TS test mirrors under ts/tests/
 *
 * Mechanical transformations:
 *  1. require("../helpers/setup.cjs") → import { ... } from "../helpers/setup"
 *  2. require("fs") → import fs from "fs"
 *  3. require("path") → import path from "path"
 *  4. require("vm") → import vm from "vm"
 *  5. require("@testing-library/jest-dom") → import "@testing-library/jest-dom"
 *  6. global.xxx → (globalThis as any).xxx
 *  7. window.xxx → (window as any).xxx  (where xxx is a custom global)
 *  8. .cjs extension refs → drop
 *  9. module.exports → export
 * 10. Add TS-safe casts for DOM queries
 */

const fs = require("fs");
const path = require("path");

const LARAVEL_ROOT = path.resolve(__dirname, "..");
const TESTS_ROOT = path.join(LARAVEL_ROOT, "tests");
const TS_TESTS_ROOT = path.join(LARAVEL_ROOT, "ts", "tests");

// Files/dirs to skip (generated artifacts, config-only, coverage reports)
const SKIP_PATTERNS = [/babel\.config\./, /jest\.frontend\.config\./, /jest\.coverage\.config\./, /\/coverage\//, /\/lcov-report\//, /\/playwright-report\//, /\/trace\//, /\/node_modules\//];

function shouldSkip(filePath) {
  return SKIP_PATTERNS.some(p => p.test(filePath));
}

function walkFiles(dir, ext, files = []) {
  if (!fs.existsSync(dir)) return files;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      walkFiles(full, ext, files);
    } else if (entry.name.endsWith(ext)) {
      files.push(full);
    }
  }
  return files;
}

// eslint-disable-next-line no-unused-vars
function convertContent(content, _srcRelPath) {
  let out = content;

  // 1. Replace require("../helpers/setup.cjs") with import
  out = out.replace(/const\s*\{([^}]+)\}\s*=\s*require\(\s*["']([^"']*setup)(?:\.cjs)?["']\s*\)\s*;?/g, (_, imports, modPath) => {
    const cleanPath = modPath.replace(/\.cjs$/, "");
    return `import {${imports}} from "${cleanPath}";`;
  });

  // 2. Replace require("fs"), require("path"), require("vm"), require("child_process")
  out = out.replace(/const\s+(\w+)\s*=\s*require\(\s*["'](fs|path|vm)["']\s*\)\s*;?/g, (_, name, mod) => `import ${name} from "${mod}";`);
  // Destructured: const { execSync } = require("child_process")
  out = out.replace(/const\s*\{([^}]+)\}\s*=\s*require\(\s*["']child_process["']\s*\)\s*;?/g, (_, imports) => `import {${imports}} from "child_process";`);

  // 3. Replace require("@testing-library/jest-dom")
  out = out.replace(/require\(\s*["']@testing-library\/jest-dom["']\s*\)\s*;?/g, 'import "@testing-library/jest-dom";');

  // 4. Replace require("jquery") — keep as require for CommonJS compat
  out = out.replace(/const\s+(\w+)\s*=\s*require\(\s*["']jquery["']\s*\)\s*;?/g, '// eslint-disable-next-line @typescript-eslint/no-require-imports\nconst $1 = require("jquery");');

  // 5. Other require() calls — generic conversion
  // For static string requires → convert to import
  // For dynamic requires (path.join etc.) → keep require + add eslint-disable
  out = out.replace(/const\s+(\w+)\s*=\s*require\(\s*["']([^"']+)["']\s*\)\s*;?/g, (match, name, mod) => {
    if (["fs", "path", "vm", "jquery", "child_process"].includes(mod)) return match;
    if (mod.includes("setup")) return match;
    if (mod.startsWith("@testing-library")) return match;
    const cleanMod = mod.replace(/\.cjs$/, "").replace(/\.js$/, "");
    return `import ${name} from "${cleanMod}";`;
  });

  // 5b. Destructured require with STATIC string path → import
  out = out.replace(/const\s*\{([^}]+)\}\s*=\s*require\(\s*["']([^"']+)["']\s*\)\s*;?/g, (match, imports, mod) => {
    if (mod.includes("setup") || mod.includes("@testing-library")) return match;
    const cleanMod = mod.replace(/\.cjs$/, "").replace(/\.js$/, "");
    return `import {${imports}} from "${cleanMod}";`;
  });

  // 5c. Add eslint-disable before ANY remaining require() line (final pass)
  {
    const lines = out.split("\n");
    const result = [];
    for (let i = 0; i < lines.length; i++) {
      const line = lines[i];
      if (/\brequire\s*\(/.test(line) && !line.includes("eslint-disable")) {
        // Check if previous line already has the comment
        const prev = result.length > 0 ? result[result.length - 1] : "";
        if (!prev.includes("eslint-disable-next-line")) {
          const indent = line.match(/^(\s*)/)[1];
          result.push(`${indent}// eslint-disable-next-line @typescript-eslint/no-require-imports`);
        }
      }
      result.push(line);
    }
    out = result.join("\n");
  }

  // 6. global.xxx → (globalThis as any).xxx for known custom globals
  const customGlobals = ["addCommas", "arrayToJson", "postAjax", "deleteAjax", "show_toastr", "site_currency_symbol", "site_currency_symbol_position", "select2", "ERPGuard", "ERPUtils", "RouteGuard", "bootstrap", "Choices", "simpleDatatables", "flatpickr", "Swal", "CustomerBillHelpers", "JournalEntryRepeater", "translations", "$", "jQuery"];
  for (const g of customGlobals) {
    // global.xxx
    const globalRe = new RegExp(`\\bglobal\\.${g}\\b`, "g");
    out = out.replace(globalRe, `(globalThis as any).${g}`);
  }

  // 7. Remaining generic global.xxx
  out = out.replace(/\bglobal\.(\w+)\b/g, (match, name) => {
    // Skip well-known Node globals
    if (["process", "console", "setTimeout", "setInterval", "clearTimeout", "clearInterval", "Buffer", "require", "__dirname", "__filename", "alert", "fetch"].includes(name)) {
      return match;
    }
    return `(globalThis as any).${name}`;
  });

  // 8. module.exports = { ... } → export { ... }
  out = out.replace(/module\.exports\s*=\s*\{([^}]+)\}\s*;?/g, (_, exports) => `export {${exports}};`);

  // 9. Remove .cjs from string literals
  out = out.replace(/["']([^"']*?)\.cjs["']/g, (_, p) => `"${p}"`);

  // 10. let ajaxSpy; → let ajaxSpy: jest.Mock;
  out = out.replace(/^(\s*let\s+ajaxSpy)\s*;/m, "$1: jest.Mock;");

  // 11. Remove @ts-check (unnecessary in .ts)
  out = out.replace(/^\/\/\s*@ts-check\s*\n?/gm, "");

  // 12. Remove redundant "use strict"; (TS modules are always strict)
  out = out.replace(/^["']use strict["'];\s*\n?/gm, "");

  // 13. Convert JSDoc type casts: /** @type {HTMLElement} */ (expr) → (expr) as HTMLElement
  out = out.replace(/\/\*\*\s*@type\s*\{(\w+)\}\s*\*\/\s*\(([^)]+)\)/g, (_, type, expr) => `(${expr}) as ${type}`);

  // 14. Fix @file header: .cjs → .ts
  out = out.replace(/@file\s+(\S+)\.(?:cjs|js)\b/g, "@file $1.ts");

  // 15. Deduplicate eslint-disable lines (safety measure)
  const lines = out.split("\n");
  const deduped = [];
  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    if (line.trim().startsWith("// eslint-disable-next-line")) {
      // Skip if the next line is also an eslint-disable
      if (i + 1 < lines.length && lines[i + 1].trim().startsWith("// eslint-disable-next-line")) {
        continue;
      }
    }
    deduped.push(line);
  }
  out = deduped.join("\n");

  return out;
}

function getTargetPath(srcPath) {
  const rel = path.relative(TESTS_ROOT, srcPath);
  const tsRel = rel
    .replace(/\.test\.cjs$/, ".test.ts")
    .replace(/\.spec\.cjs$/, ".spec.ts")
    .replace(/\.cjs$/, ".ts")
    .replace(/\.js$/, ".ts");
  return path.join(TS_TESTS_ROOT, tsRel);
}

function main() {
  const jsFiles = [...walkFiles(TESTS_ROOT, ".cjs"), ...walkFiles(TESTS_ROOT, ".js")].filter(f => !shouldSkip(f));

  let created = 0,
    skipped = 0,
    errors = 0;

  for (const srcPath of jsFiles) {
    const targetPath = getTargetPath(srcPath);

    if (fs.existsSync(targetPath)) {
      skipped++;
      continue;
    }

    try {
      const content = fs.readFileSync(srcPath, "utf8");
      const converted = convertContent(content, path.relative(TESTS_ROOT, srcPath));

      fs.mkdirSync(path.dirname(targetPath), { recursive: true });
      fs.writeFileSync(targetPath, converted, "utf8");
      created++;
      console.log(`✓ ${path.relative(TS_TESTS_ROOT, targetPath)}`);
    } catch (err) {
      errors++;
      console.error(`✗ ${srcPath}: ${err.message}`);
    }
  }

  console.log(`\nDone: ${created} created, ${skipped} skipped (already exist), ${errors} errors`);
  console.log(`Total JS/CJS files processed: ${jsFiles.length}`);
}

main();
