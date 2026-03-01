#!/usr/bin/env node
/**
 * refactor-routes.mjs — Batch refactor route JS files to use ERPGuard/ERPBootstrap
 *
 * This script:
 *  1. Reads each target file
 *  2. Removes boilerplate function blocks (ensureToastContainer, showErrorNow,
 *     scheduleInteractiveError, schedulePointerupError, getMsg/localize, hasBS, ensureJq)
 *  3. Adds ERPBootstrap.require() at the IIFE top
 *  4. Replaces calls to local boilerplate with guard singleton methods
 *  5. Writes the result back
 *
 * Usage: node refactor-routes.mjs [--dry-run]
 */
import { readFileSync, writeFileSync, existsSync } from "fs";
import { resolve, basename, dirname, relative } from "path";

const BASE = resolve(
  import.meta.dirname ||
    new URL(".", import.meta.url).pathname.replace(/\/$/, ""),
  "public/assets/js/routes",
);

const DRY_RUN = process.argv.includes("--dry-run");

// ─── Files with heavy boilerplate (ensureToastContainer + showErrorNow +
//     scheduleInteractiveError/schedulePointerupError + getMsg) ──────────
const HEAVY_FILES = [
  "ai/generate/copy.js",
  "ai/grammar/init.js",
  "contracts/note,js",
  "contracts/shared/helpers.js",
  "leads/order.js",
  "pos/index.js",
  "reports/attendances/monthly/pdf.js",
  "reports/payables/index/pdf.js",
  "reports/payables/receipts/pdf.js",
  "reports/profits/horizontal/loss/receipts/pdf.js",
  "reports/profits/index/loss/receipts/pdf.js",
  "reports/profits/index/loss/summaries/pdf.js",
  "reports/receivables/pdf.js",
  "reports/sales/index/pdf.js",
  "reports/sales/receipts/pdf.js",
  "reports/statements/pdf.js",
  "tasks/calendar.js",
  "tasks/drag.js",
  "timeTrackers/images.js",
  "zoomMeetings/actions.js",
  "zoomMeetings/calendar.js",
];

// ─── Files with medium redundancy (local getMsg with full localization, no toast) ───
const MEDIUM_FILES = [
  "settings/pos/purchase.js",
  "timeTrackers/time.js",
  "installer/env.js",
  "vendors/copy.js",
  "transactions/pdf.js",
  "payslips/storeEnv.js",
];

// ─── Regex patterns for boilerplate function blocks ─────────────────────
// Each pattern removes a full const X = ... ; declaration (including multi-line arrow fns)

/**
 * Remove a const declaration that spans from assignment to its closing ;
 * accounting for nested braces and balanced parens.
 * We use a custom scanner rather than a single regex for reliability.
 */
function removeConstBlock(src, name) {
  // Find "const <name> = " or "const <name> =" accounting for whitespace
  const startRe = new RegExp(`(^[ \\t]*)const\\s+${escRe(name)}\\s*=\\s*`, "m");
  const match = startRe.exec(src);
  if (!match) return src;

  const indent = match[1];
  const startIdx = match.index;
  let i = startIdx + match[0].length;

  // Scan forward, counting braces/parens/brackets
  let braces = 0,
    parens = 0,
    brackets = 0;
  let inString = null; // null | '"' | "'" | '`'
  let escaped = false;
  let foundBody = false;

  while (i < src.length) {
    const ch = src[i];

    if (escaped) {
      escaped = false;
      i++;
      continue;
    }
    if (ch === "\\") {
      escaped = true;
      i++;
      continue;
    }

    // String tracking
    if (inString) {
      if (ch === inString) inString = null;
      i++;
      continue;
    }
    if (ch === '"' || ch === "'" || ch === "`") {
      inString = ch;
      i++;
      continue;
    }

    // Line comment
    if (ch === "/" && src[i + 1] === "/") {
      const nl = src.indexOf("\n", i);
      i = nl === -1 ? src.length : nl + 1;
      continue;
    }
    // Block comment
    if (ch === "/" && src[i + 1] === "*") {
      const end = src.indexOf("*/", i + 2);
      i = end === -1 ? src.length : end + 2;
      continue;
    }

    if (ch === "{") {
      braces++;
      foundBody = true;
    } else if (ch === "}") {
      braces--;
    } else if (ch === "(") {
      parens++;
      foundBody = true;
    } else if (ch === ")") {
      parens--;
    } else if (ch === "[") {
      brackets++;
    } else if (ch === "]") {
      brackets--;
    }

    // End of declaration: balanced and we hit ";"
    if (
      braces <= 0 &&
      parens <= 0 &&
      brackets <= 0 &&
      foundBody &&
      ch === ";"
    ) {
      // Remove from startIdx to i+1, including trailing newline
      let endIdx = i + 1;
      if (src[endIdx] === "\n") endIdx++;
      src = src.slice(0, startIdx) + src.slice(endIdx);
      return src;
    }

    // Simple value (no braces/parens) ending with ;
    if (
      braces === 0 &&
      parens === 0 &&
      brackets === 0 &&
      !foundBody &&
      ch === ";"
    ) {
      let endIdx = i + 1;
      if (src[endIdx] === "\n") endIdx++;
      src = src.slice(0, startIdx) + src.slice(endIdx);
      return src;
    }

    i++;
  }

  // Fallback: if we couldn't find the end, remove just the line
  const lineEnd = src.indexOf("\n", startIdx);
  if (lineEnd !== -1) {
    src = src.slice(0, startIdx) + src.slice(lineEnd + 1);
  }
  return src;
}

function escRe(s) {
  return s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

/**
 * Remove all occurrences of a const block with the given name.
 */
function removeAllConst(src, name) {
  let prev;
  let iterations = 0;
  do {
    prev = src;
    src = removeConstBlock(src, name);
    iterations++;
  } while (src !== prev && iterations < 5);
  return src;
}

/**
 * Insert ERPBootstrap.require line after the IIFE opening.
 */
function insertBootstrapRequire(src, needsUtils = false) {
  // Already has it?
  if (src.includes("ERPBootstrap.require")) return src;

  const args = needsUtils ? '"ERPGuard", "ERPUtils"' : '"ERPGuard"';
  const requireLine = `  const { guard${needsUtils ? ", utils" : ""} } = window.ERPBootstrap.require(${args});\n  if (!guard) return;\n`;

  // Find the IIFE opening: (() => { or (function () { or (function() {
  const iifePatterns = [
    /\(\s*\(\s*\)\s*=>\s*\{[ \t]*\n/,
    /\(\s*function\s*\(\s*\)\s*\{[ \t]*\n/,
    /\(\s*function\s*\(\s*w\s*\)\s*\{[ \t]*\n/, // (function(w){
  ];

  for (const pat of iifePatterns) {
    const m = pat.exec(src);
    if (m) {
      const insertPos = m.index + m[0].length;
      // Skip any existing "use strict" line
      let skipPos = insertPos;
      const strictMatch = src
        .slice(skipPos)
        .match(/^[ \t]*["']use strict["'];?\s*\n/);
      if (strictMatch) {
        skipPos += strictMatch[0].length;
      }
      src = src.slice(0, skipPos) + requireLine + src.slice(skipPos);
      return src;
    }
  }

  // Fallback: insert after first line
  const firstNl = src.indexOf("\n");
  if (firstNl !== -1) {
    src = src.slice(0, firstNl + 1) + requireLine + src.slice(firstNl + 1);
  }
  return src;
}

/**
 * Remove old-style singleton destructuring that's been replaced by bootstrap require.
 */
function removeOldDestructuring(src) {
  // Remove patterns like:
  //   const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  //   const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  //   const { scheduleError, utils } = window.ERPGuard || {};
  //   const { getMsg } = window.ERPUtils ?? {};
  //   if (!guard || !utils) return;
  //   if (typeof scheduleError !== "function" ...) return;

  const patterns = [
    /^[ \t]*const guard = typeof window.*?;\s*\n/m,
    /^[ \t]*const utils = typeof window.*?;\s*\n/m,
    /^[ \t]*const \{.*?\} = window\.ERPGuard.*?;\s*\n/m,
    /^[ \t]*const \{.*?\} = window\.ERPUtils.*?;\s*\n/m,
    /^[ \t]*if \(!guard \|\| !utils\) return;\s*\n/m,
    /^[ \t]*if \(typeof scheduleError !== "function".*?\) \{?\s*\n(?:[ \t]*\n)*(?:[ \t]*return;\s*\n)?(?:[ \t]*\}\s*\n)?/m,
    /^[ \t]*if \(!scheduleError \|\| !getMsg\) return;\s*\n/m,
  ];

  for (const pat of patterns) {
    src = src.replace(pat, "");
  }

  return src;
}

/**
 * Replace calls to local boilerplate functions with guard singleton methods.
 */
function replaceCalls(src) {
  // scheduleInteractiveError(getMsg(el, "key")) → guard.scheduleInteractiveError(guard.getMsg("key"))
  // scheduleInteractiveError(getMsg(document.body, "key")) → guard.scheduleInteractiveError(guard.getMsg("key"))
  // scheduleInteractiveError(localize(el, "key")) → guard.scheduleInteractiveError(guard.getMsg("key"))
  // showErrorNow(msg) → guard.error(msg)
  // schedulePointerupError(msg) → guard.scheduleInteractiveError(msg)
  // getMsg(el, key) → guard.getMsg(key)  [where el is first arg]
  // localize(el, key) → guard.getMsg(key)
  // ensureJq() → !!(window.jQuery && window.jQuery.fn)

  // Replace getMsg(el, "key") or getMsg(el, key) — strip the first argument
  // Pattern: getMsg(someExpr, "keyString") or getMsg(someExpr, keyVar)
  src = src.replace(/\bgetMsg\s*\(\s*[^,)]+,\s*/g, "guard.getMsg(");

  // Replace localize(el, key) — same pattern
  src = src.replace(/\blocalize\s*\(\s*[^,)]+,\s*/g, "guard.getMsg(");

  // Replace bare getMsg("key") that's already just a key lookup (from ERPUtils pattern)
  src = src.replace(/\bgetMsg\s*\(/g, "guard.getMsg(");

  // Replace showErrorNow(msg) → guard.error(msg)
  src = src.replace(/\bshowErrorNow\s*\(/g, "guard.error(");

  // Replace scheduleInteractiveError(...) → guard.scheduleInteractiveError(...)
  src = src.replace(
    /\bscheduleInteractiveError\s*\(/g,
    "guard.scheduleInteractiveError(",
  );

  // Replace schedulePointerupError(...) → guard.scheduleInteractiveError(...)
  src = src.replace(
    /\bschedulePointerupError\s*\(/g,
    "guard.scheduleInteractiveError(",
  );

  // Replace scheduleError(msg) → guard.scheduleError(msg)  (but only if not already guard.)
  src = src.replace(
    /(?<!guard\.)(?<!window\.ERPGuard\.)\bscheduleError\s*\(/g,
    "guard.scheduleError(",
  );

  // Replace ensureJq() → !!(window.jQuery && window.jQuery.fn)
  src = src.replace(
    /\bensureJq\s*\(\s*\)/g,
    "!!(window.jQuery && window.jQuery.fn)",
  );

  // Remove duplicate guard.guard. if any
  src = src.replace(/\bguard\.guard\./g, "guard.");

  return src;
}

/**
 * Remove now-unused data attribute constants that were only referenced by removed boilerplate.
 */
function removeUnusedDataConsts(src) {
  const candidates = [
    "dataClientLocalized",
    "dataSvLocalized",
    "dataGuardMsg",
    "dataErrGuard",
  ];

  for (const name of candidates) {
    // Check if still referenced (other than its own definition)
    const defPat = new RegExp(
      `^[ \\t]*const\\s+${name}\\s*=\\s*["'][^"']+["'];?\\s*$`,
      "m",
    );
    const defMatch = defPat.exec(src);
    if (!defMatch) continue;

    // Count references outside the definition
    const withoutDef =
      src.slice(0, defMatch.index) +
      src.slice(defMatch.index + defMatch[0].length);
    const refCount = (withoutDef.match(new RegExp(`\\b${name}\\b`, "g")) || [])
      .length;

    if (refCount === 0) {
      // Safe to remove
      src = src.replace(defPat, "");
    }
  }

  return src;
}

/**
 * Clean up extra blank lines (3+ consecutive → 2).
 */
function cleanBlankLines(src) {
  return src.replace(/\n{3,}/g, "\n\n");
}

// ─── BOILERPLATE FUNCTION NAMES TO STRIP ────────────────────────────────
const HEAVY_BOILERPLATE = [
  "ensureToastContainer",
  "showErrorNow",
  "scheduleInteractiveError",
  "schedulePointerupError",
  "hasBS",
  "ensureJq",
  "ensureDragula",
];

const MEDIUM_BOILERPLATE = [
  // getMsg with full localization body — detected separately
];

/**
 * Process a single file.
 */
function processFile(relPath, category) {
  const absPath = resolve(BASE, relPath);
  if (!existsSync(absPath)) {
    console.warn(`  SKIP (not found): ${relPath}`);
    return false;
  }

  let src = readFileSync(absPath, "utf8");
  const origLen = src.length;

  // 1. Remove heavy boilerplate function definitions
  if (category === "heavy") {
    for (const name of HEAVY_BOILERPLATE) {
      src = removeAllConst(src, name);
    }
  }

  // 2. Remove getMsg/localize function definitions if they contain the
  //    full localization body (sessionStorage, translations, etc.)
  if (src.includes("sessionStorage.getItem") && src.includes("translations")) {
    // Full localization getMsg — remove it
    src = removeAllConst(src, "getMsg");
    src = removeAllConst(src, "localize");
  }

  // 3. Remove verifyRoute if present (guard.isInvalidUrl replaces it)
  if (/\bconst verifyRoute\b/.test(src)) {
    src = removeAllConst(src, "verifyRoute");
    // Replace verifyRoute calls:
    // verifyRoute(x) returns true if valid → !guard.isInvalidUrl(x)
    src = src.replace(/\bverifyRoute\s*\(/g, "!guard.isInvalidUrl(");
    // Handle negated: !verifyRoute(x) → guard.isInvalidUrl(x)
    src = src.replace(/!\s*!guard\.isInvalidUrl\(/g, "guard.isInvalidUrl(");
  }

  // 4. Also strip verifyRouteFromElement if present
  if (/\bconst verifyRouteFromElement\b/.test(src)) {
    src = removeAllConst(src, "verifyRouteFromElement");
  }

  // 5. Remove old destructuring patterns
  src = removeOldDestructuring(src);

  // 6. Insert ERPBootstrap.require
  const needsUtils = src.includes("utils.") || src.includes("ERPUtils");
  src = insertBootstrapRequire(src, needsUtils);

  // 7. Replace function calls
  src = replaceCalls(src);

  // 8. Clean up unused data consts
  src = removeUnusedDataConsts(src);

  // 9. Clean blank lines
  src = cleanBlankLines(src);

  // 10. Remove errFb const if no longer used
  {
    const errFbDef = /^[ \t]*const errFb = ["']# ERROR["'];?\s*$/m;
    const defMatch = errFbDef.exec(src);
    if (defMatch) {
      const without =
        src.slice(0, defMatch.index) +
        src.slice(defMatch.index + defMatch[0].length);
      if (!/\berrFb\b/.test(without)) {
        src = src.replace(errFbDef, "");
      }
    }
  }

  // Final clean
  src = cleanBlankLines(src);

  const saved = origLen - src.length;
  const dir = relative(BASE, dirname(absPath));
  const name = basename(absPath);

  if (src !== readFileSync(absPath, "utf8")) {
    if (DRY_RUN) {
      console.log(
        `  [DRY] ${dir}/${name}: ${origLen} → ${src.length} (−${saved} bytes)`,
      );
    } else {
      writeFileSync(absPath, src, "utf8");
      console.log(
        `  ✓ ${dir}/${name}: ${origLen} → ${src.length} (−${saved} bytes)`,
      );
    }
    return true;
  } else {
    console.log(`  – ${dir}/${name}: no changes needed`);
    return false;
  }
}

// ─── Main ───────────────────────────────────────────────────────────────
console.log(`\n${DRY_RUN ? "[DRY RUN] " : ""}Refactoring route files...\n`);

let changed = 0;
let total = 0;

console.log("── Heavy boilerplate files ──");
for (const f of HEAVY_FILES) {
  total++;
  if (processFile(f, "heavy")) changed++;
}

console.log("\n── Medium boilerplate files ──");
for (const f of MEDIUM_FILES) {
  total++;
  if (processFile(f, "medium")) changed++;
}

console.log(
  `\nDone. ${changed}/${total} files ${DRY_RUN ? "would be " : ""}modified.\n`,
);
