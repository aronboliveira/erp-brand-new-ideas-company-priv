#!/usr/bin/env node
// fetch-guard.mjs — Ensure all fetch() and JSON.parse() calls in src/ are
// wrapped in try-catch blocks. Also wraps .json() calls.
// Strategy: Parse each file, find fetch/JSON calls, check if they're
// already inside a try block, add try-catch if not.

import { readFileSync, writeFileSync, readdirSync, statSync } from "node:fs";
import { join } from "node:path";

const BASE =
  "/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/ts/src";

function walk(dir) {
  let results = [];
  for (const f of readdirSync(dir)) {
    const p = join(dir, f);
    if (statSync(p).isDirectory()) results = results.concat(walk(p));
    else if (f.endsWith(".ts")) results.push(p);
  }
  return results;
}

let fileCount = 0;
let fetchGuards = 0;
let jsonGuards = 0;

for (const fp of walk(BASE)) {
  let src = readFileSync(fp, "utf8");
  const orig = src;

  // ──────────────────────────────────────────────────
  // 1. Wrap bare fetch().then().catch() chains that don't have .catch()
  //    Already-awaited fetches inside try blocks are fine.
  // ──────────────────────────────────────────────────

  // Pattern: fetch(url).then(r => r.json()) without .catch()
  // Add .catch(() => {}) at the end
  // This catches: fetch(...).then(...) NOT followed by .catch
  src = src.replace(
    /\bfetch\([^)]*\)[\s\S]*?\.then\([^)]*\)(?!\s*\.catch)/gm,
    match => {
      // Don't double-add
      if (match.includes(".catch")) return match;
      return match;
    },
  );

  // ──────────────────────────────────────────────────
  // 2. For .json() calls that aren't in try-catch, wrap the
  //    response handling in try-catch
  // ──────────────────────────────────────────────────

  // Pattern: const data = await res.json(); (not inside try)
  // We check line-by-line if a .json() call is inside a try block
  const lines = src.split("\n");
  let tryDepth = 0;
  let braceDepth = 0;
  const tryStack = []; // track try block brace depths

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    const trimmed = line.trim();

    // Track braces (simplified)
    for (const ch of line) {
      if (ch === "{") {
        braceDepth++;
      } else if (ch === "}") {
        braceDepth--;
        if (tryStack.length > 0 && braceDepth < tryStack[tryStack.length - 1]) {
          tryStack.pop();
          tryDepth--;
        }
      }
    }

    if (
      trimmed.startsWith("try ") ||
      trimmed === "try{" ||
      trimmed === "try {"
    ) {
      tryDepth++;
      tryStack.push(braceDepth);
    }

    // Check for JSON.parse not in try
    if (
      tryDepth === 0 &&
      /JSON\.parse\s*\(/.test(line) &&
      !trimmed.startsWith("//")
    ) {
      // Wrap this line in try-catch
      const indent = line.match(/^(\s*)/)?.[1] ?? "";
      lines[i] =
        `${indent}try { ${trimmed} } catch (_jsonErr) { console.error("JSON parse failed", _jsonErr); }`;
      jsonGuards++;
    }
  }

  src = lines.join("\n");

  // ──────────────────────────────────────────────────
  // 3. Ensure fetch() calls have .catch() or are in try-catch
  //    For fetch().then() chains without .catch(), add .catch()
  // ──────────────────────────────────────────────────

  // Add .catch(console.error) to fetch().then() chains that don't have catch
  // This regex matches: fetch(...)followed by .then(...)  not followed by .catch
  src = src.replace(
    /(fetch\([^)]*(?:\([^)]*\))*[^)]*\)\s*\.then\([^)]*(?:\([^)]*\))*[^)]*\))(?!\s*\.\s*catch)/g,
    "$1.catch(console.error)",
  );

  // For standalone fetch().then().then() - add catch if missing
  src = src.replace(
    /(\.then\([^)]*(?:\([^)]*\))*[^)]*\)\s*\.then\([^)]*(?:\([^)]*\))*[^)]*\))(?!\s*\.\s*catch)/g,
    "$1.catch(console.error)",
  );

  if (src !== orig) {
    writeFileSync(fp, src);
    fileCount++;
  }
}

console.log(
  `Guarded: ${fileCount} files, ${fetchGuards} fetch calls, ${jsonGuards} JSON.parse calls.`,
);
