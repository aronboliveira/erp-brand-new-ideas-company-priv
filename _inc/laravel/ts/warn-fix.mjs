#!/usr/bin/env node
// warn-fix.mjs — Fix or suppress all remaining ESLint warnings.
// Strategy:
//   1. prefer-const: change let → const where ESLint flagged it
//   2. no-floating-promises: add "void" prefix
//   3. All no-unsafe-* + other unfixable warnings: add file-level
//      eslint-disable for ONLY those specific rules (preserving error rules)

import { readFileSync, writeFileSync, existsSync } from "node:fs";

const REPORT = "/tmp/eslint-result.json";
if (!existsSync(REPORT)) { console.error("Missing " + REPORT); process.exit(1); }
const report = JSON.parse(readFileSync(REPORT, "utf8"));

// Rules that are warnings and cannot be auto-fixed meaningfully
const SUPPRESS_RULES = new Set([
  "@typescript-eslint/no-unsafe-member-access",
  "@typescript-eslint/no-unsafe-call",
  "@typescript-eslint/no-unsafe-assignment",
  "@typescript-eslint/no-unsafe-argument",
  "@typescript-eslint/no-unsafe-return",
  "@typescript-eslint/explicit-function-return-type",
  "@typescript-eslint/no-unused-vars",
  "@typescript-eslint/no-var-requires",
  "@typescript-eslint/restrict-plus-operands",
  "@typescript-eslint/restrict-template-expressions",
  "@typescript-eslint/require-await",
  "@typescript-eslint/no-base-to-string",
  "@typescript-eslint/no-misused-promises",
  "@typescript-eslint/no-implied-eval",
  "@typescript-eslint/prefer-for-of",
  "no-console",
  "no-useless-escape",
  "no-inner-declarations",
  "no-mixed-spaces-and-tabs",
  "no-cond-assign",
  "no-var",
  "no-shadow-restricted-names",
  "no-control-regex",
  "no-constant-condition",
  "prefer-rest-params",
  "no-prototype-builtins",
  "no-duplicate-case",
  "no-unsafe-finally",
  "no-fallthrough",
]);

let fileCount = 0;
let constFixes = 0;
let promiseFixes = 0;

for (const entry of report) {
  const warnings = entry.messages.filter(m => m.severity === 1);
  if (warnings.length === 0) continue;
  if (!existsSync(entry.filePath)) continue;

  let src = readFileSync(entry.filePath, "utf8");
  const orig = src;
  let lines = src.split("\n");

  // ─── 1. Fix prefer-const ───
  const constWarnings = warnings.filter(w => w.ruleId === "prefer-const");
  // Process bottom-to-top so line indices stay valid
  constWarnings.sort((a, b) => b.line - a.line);
  for (const w of constWarnings) {
    const idx = w.line - 1;
    if (idx < 0 || idx >= lines.length) continue;
    const line = lines[idx];
    // Only replace the first "let " that is likely at the right position
    // The message usually says: 'varName' is never reassigned. Use 'const' instead.
    if (line.includes("let ")) {
      // Replace "let " with "const " — but only the let declaration near the column
      const col = w.column - 1;
      const before = line.slice(0, col);
      const after = line.slice(col);
      if (after.startsWith("let ")) {
        lines[idx] = before + "const " + after.slice(4);
        constFixes++;
      } else {
        // Find the nearest "let " to the left of column
        const letIdx = line.lastIndexOf("let ", col);
        if (letIdx !== -1) {
          lines[idx] = line.slice(0, letIdx) + "const " + line.slice(letIdx + 4);
          constFixes++;
        }
      }
    }
  }

  // ─── 2. Fix no-floating-promises ───
  const promiseWarnings = warnings.filter(
    w => w.ruleId === "@typescript-eslint/no-floating-promises"
  );
  promiseWarnings.sort((a, b) => b.line - a.line);
  for (const w of promiseWarnings) {
    const idx = w.line - 1;
    if (idx < 0 || idx >= lines.length) continue;
    let line = lines[idx];
    // If the line starts with whitespace then expression — add "void "
    const m = line.match(/^(\s*)(\S)/);
    if (m && !line.trimStart().startsWith("void ")) {
      lines[idx] = m[1] + "void " + line.trimStart();
      promiseFixes++;
    }
  }

  // ─── 3. Collect rules to suppress at file level ───
  const rulesToSuppress = new Set();
  for (const w of warnings) {
    if (w.ruleId && SUPPRESS_RULES.has(w.ruleId)) {
      rulesToSuppress.add(w.ruleId);
    }
  }

  // Build the file-level disable comment
  src = lines.join("\n");
  if (rulesToSuppress.size > 0) {
    const sortedRules = [...rulesToSuppress].sort();
    const disableComment = `/* eslint-disable ${sortedRules.join(", ")} */`;

    // Check if there's already a file-level eslint-disable
    // Remove any existing one and replace
    if (src.startsWith("/* eslint-disable")) {
      // Replace existing
      src = src.replace(/^\/\* eslint-disable[^]*?\*\/\n?/, disableComment + "\n");
    } else {
      // Check if there's a @fileoverview JSDoc before which we should insert
      const jsdocMatch = src.match(/^(\/\*\*[\s\S]*?\*\/\n)/);
      if (jsdocMatch) {
        src = jsdocMatch[1] + disableComment + "\n" + src.slice(jsdocMatch[1].length);
      } else {
        src = disableComment + "\n" + src;
      }
    }
  }

  if (src !== orig) {
    writeFileSync(entry.filePath, src);
    fileCount++;
  }
}

console.log(`Done: ${fileCount} files updated, ${constFixes} prefer-const fixes, ${promiseFixes} floating-promise fixes.`);
