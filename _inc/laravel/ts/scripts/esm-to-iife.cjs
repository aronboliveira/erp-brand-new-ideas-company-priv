#!/usr/bin/env node
"use strict";

/**
 * esm-to-iife.cjs — Convert ESM compiled output to IIFE for production.
 *
 * Reads:  ts/dist/public/assets/js/routes/**\/*.js
 * Writes: ts/dist-iife/public/assets/js/routes/**\/*.js
 *
 * Transformations applied:
 *  1. Strips `export {};` and `export default ...;` at top level.
 *  2. Strips `import ... from "...";` statements.
 *  3. Strips `Object.defineProperty(exports, ...)` CJS shims.
 *  4. Wraps body in `(function() { "use strict"; ... })();` if not already IIFE.
 *  5. Removes source-map references (`//# sourceMappingURL=...`).
 *
 * Usage:
 *   node scripts/esm-to-iife.cjs [--dry-run] [--verbose]
 *
 * @see _inc/laravel/.notes/.llms/.guidelines/frontend/esm-iife-strategy.md
 */

const fs = require("fs");
const path = require("path");

/* ---------- Config ------------------------------------------------------ */

const DIST_DIR = path.resolve(__dirname, "..", "dist", "public", "assets", "js", "routes");
const OUT_DIR = path.resolve(__dirname, "..", "dist-iife", "public", "assets", "js", "routes");

const CORE_DIST = path.resolve(__dirname, "..", "dist", "public", "assets", "js", "core");
const CORE_OUT = path.resolve(__dirname, "..", "dist-iife", "public", "assets", "js", "core");

const DRY_RUN = process.argv.includes("--dry-run");
const VERBOSE = process.argv.includes("--verbose");
/**
 * Skip core/ by default — the originals in public/assets/js/core/ are
 * hand-written OOP singletons (from the agent branch) and must NOT be
 * overwritten by TS-compiled simplified versions.
 * Pass --include-core to force processing core (e.g. after a full TS port).
 */
const INCLUDE_CORE = process.argv.includes("--include-core");

/* ---------- Helpers ----------------------------------------------------- */

/**
 * Recursively collect all .js files under `dir`.
 * @param {string} dir
 * @returns {string[]}
 */
function collectJs(dir) {
  /** @type {string[]} */
  const results = [];
  if (!fs.existsSync(dir)) return results;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      results.push(...collectJs(full));
    } else if (entry.name.endsWith(".js")) {
      results.push(full);
    }
  }
  return results;
}

/**
 * Convert ESM content to IIFE.
 * @param {string} content
 * @returns {string}
 */
function esmToIife(content) {
  let code = content;

  // 1. Strip import statements
  code = code.replace(/^import\s+.*?from\s+['"].*?['"];?\s*$/gm, "");
  code = code.replace(/^import\s+['"].*?['"];?\s*$/gm, "");

  // 2. Strip export statements
  code = code.replace(/^export\s*\{\s*\};?\s*$/gm, "");
  code = code.replace(/^export\s+default\s+.*;?\s*$/gm, "");
  code = code.replace(/^export\s+\{[^}]*\};?\s*$/gm, "");

  // 3. Strip CJS shims that tsc sometimes emits
  code = code.replace(/^Object\.defineProperty\(exports,\s*"__esModule".*?\);\s*$/gm, "");

  // 4. Strip source map references
  code = code.replace(/^\/\/# sourceMappingURL=.*$/gm, "");

  // 4b. Strip TS-only eslint-disable directives (rules like @typescript-eslint/*
  //     are not loaded for plain JS, so they become "unused disable" warnings).
  //     Block-comment disables: drop entirely if all rules are @typescript-eslint/*.
  code = code.replace(
    /\/\*\s*eslint-disable(?:-next-line)?\s+([^*]+?)\*\//g,
    (match, ruleList) => {
      const rules = ruleList
        .split(",")
        .map((r) => r.trim())
        .filter((r) => r.length > 0);
      const nonTs = rules.filter((r) => !r.startsWith("@typescript-eslint/"));
      if (nonTs.length === 0) return "";
      return match.replace(ruleList, " " + nonTs.join(", ") + " ");
    }
  );
  //     Line-comment disables: same treatment.
  code = code.replace(
    /\/\/\s*eslint-disable(?:-next-line)?\s+([^\n]+)/g,
    (match, ruleList) => {
      const rules = ruleList
        .split(",")
        .map((r) => r.trim())
        .filter((r) => r.length > 0);
      const nonTs = rules.filter((r) => !r.startsWith("@typescript-eslint/"));
      if (nonTs.length === 0) return "";
      return match.replace(ruleList, nonTs.join(", "));
    }
  );

  // 5. Trim excess blank lines
  code = code.replace(/\n{3,}/g, "\n\n").trim();

  // 6. Wrap in IIFE if not already wrapped
  const trimmed = code.trim();
  const alreadyIife = /^\(function\s*\(/.test(trimmed) || /^\(\(\)\s*=>/.test(trimmed);

  if (!alreadyIife && trimmed.length > 0) {
    code = `(function() {\n"use strict";\n${code}\n})();`;
  }

  return code;
}

/* ---------- Main -------------------------------------------------------- */

function main() {
  console.log("ESM → IIFE conversion");
  console.log(`  Source:  ${DIST_DIR}`);
  console.log(`  Output:  ${OUT_DIR}`);
  if (DRY_RUN) console.log("  ** DRY RUN — no files will be written **");
  console.log();

  // Process route files
  const routeFiles = collectJs(DIST_DIR);

  /** @type {{ src: string, outBase: string, srcBase: string }[]} */
  const allFiles = routeFiles.map(f => ({ src: f, outBase: OUT_DIR, srcBase: DIST_DIR }));

  // Core files: only process when explicitly requested.
  // The originals in public/assets/js/core/ are the authoritative OOP singletons.
  if (INCLUDE_CORE) {
    const coreFiles = collectJs(CORE_DIST);
    allFiles.push(...coreFiles.map(f => ({ src: f, outBase: CORE_OUT, srcBase: CORE_DIST })));
    console.log("  --include-core: will process core files");
  } else {
    console.log("  Core files EXCLUDED (use --include-core to override)");
  }

  let processed = 0;
  let skipped = 0;

  for (const { src, outBase, srcBase } of allFiles) {
    const rel = path.relative(srcBase, src);
    const dest = path.join(outBase, rel);
    const content = fs.readFileSync(src, "utf8");
    const converted = esmToIife(content);

    if (converted.trim().length === 0) {
      if (VERBOSE) console.log(`  SKIP (empty): ${rel}`);
      skipped++;
      continue;
    }

    if (!DRY_RUN) {
      fs.mkdirSync(path.dirname(dest), { recursive: true });
      fs.writeFileSync(dest, converted, "utf8");
    }

    if (VERBOSE) console.log(`  OK: ${rel}`);
    processed++;
  }

  console.log(`\nDone.`);
  console.log(`  Processed: ${processed}`);
  console.log(`  Skipped:   ${skipped}`);
  console.log(`  Total:     ${allFiles.length}`);

  if (!DRY_RUN) {
    console.log(`\nOutput written to:`);
    console.log(`  Routes: ${OUT_DIR}`);
    if (INCLUDE_CORE) console.log(`  Core:   ${CORE_OUT}`);
  }
}

main();
