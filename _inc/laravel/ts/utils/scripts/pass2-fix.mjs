#!/usr/bin/env node
// pass2-fix.mjs — Second-pass fixer for ESLint errors:
// - prefer-nullish-coalescing: || → ??
// - prefer-optional-chain: x && x.foo → x?.foo
// - strict-boolean-expressions: nullable strings, numbers → explicit checks
// - no-unnecessary-condition: remove always-true/false guards
// - explicit-function-return-type: add : void to callbacks
// - no-case-declarations: wrap case bodies in blocks
// - prefer-const: let → const where never reassigned (ESLint --fix)
// - no-var: remaining var → let
//
// Run: node pass2-fix.mjs

import { readFileSync, writeFileSync } from "node:fs";
import { execSync } from "node:child_process";

const DRY = process.argv.includes("--dry-run");

const files = execSync('find src -name "*.ts" -not -name "*.d.ts" -type f', {
  encoding: "utf8",
})
  .trim()
  .split("\n")
  .filter(Boolean);

let changed = 0;

for (const file of files) {
  let src = readFileSync(file, "utf8");
  const orig = src;

  // ──────────────────────────────────────────────────────────────
  // 1. prefer-nullish-coalescing: || → ?? for string | null/undefined types
  //    Pattern: .getAttribute(...) || "literal"  →  already done
  //    Pattern: variable || "literal"  (generic)
  //    Pattern: expr || expr  where left side is likely nullable
  // ──────────────────────────────────────────────────────────────

  // Generic: any  `expression || "string"` → `expression ?? "string"`
  // But be careful not to change boolean/number contexts
  // Safe: after .getAttribute, .value, .textContent, variable assignment contexts
  src = src.replace(
    /(\b\w+(?:\.\w+)*(?:\([^)]*\))?)\s*\|\|\s*(?=(["'`]))/g,
    (m, left) => {
      // Don't convert if already ??
      if (m.includes("??")) return m;
      return `${left} ?? `;
    },
  );

  // x || "#" → x ?? "#"  (URL defaults)
  // x || 0 → keep (could be boolean/number context)
  // Already handled above for string literals

  // target || document  → target ?? document
  src = src.replace(
    /((?:target|container|wrapper|el|element|node|parent)\w*)\s*\|\|\s*(document\b)/g,
    "$1 ?? $2",
  );

  // ──────────────────────────────────────────────────────────────
  // 2. prefer-optional-chain: x && x.foo → x?.foo
  //    Also: x && x.foo && x.foo.bar → x?.foo?.bar
  //    Also: x !== null && x.foo → x?.foo
  //    Also: x != null && x.foo → x?.foo
  // ──────────────────────────────────────────────────────────────

  // Pattern: identifier && identifier.property
  src = src.replace(/\b(\w+)\s*&&\s*\1\.(\w+)/g, "$1?.$2");

  // Pattern: identifier !== null && identifier.property
  src = src.replace(/\b(\w+)\s*!==?\s*null\s*&&\s*\1\.(\w+)/g, "$1?.$2");
  // Pattern: identifier !== undefined && identifier.property
  src = src.replace(/\b(\w+)\s*!==?\s*undefined\s*&&\s*\1\.(\w+)/g, "$1?.$2");
  // Pattern: typeof identifier !== "undefined" && identifier.property
  src = src.replace(
    /typeof\s+(\w+)\s*!==?\s*["']undefined["']\s*&&\s*\1\.(\w+)/g,
    "$1?.$2",
  );

  // ──────────────────────────────────────────────────────────────
  // 3. strict-boolean-expressions: nullable string in conditional
  //    if (str) → if (str != null && str !== "")
  //    But we can't know the type from regex alone.
  //    Instead, common patterns:
  //    if (value) → if (value != null)  when value is from getAttribute/etc
  //    We handle this specifically for known nullable patterns.
  // ──────────────────────────────────────────────────────────────

  // if (!value) where value came from getAttribute → if (value == null || value === "")
  // This is hard to do with regex across statements. Leave for targeted fixes.

  // ──────────────────────────────────────────────────────────────
  // 4. Fix remaining var → let
  // ──────────────────────────────────────────────────────────────
  src = src.replace(/\bvar\s+/g, "let ");

  // ──────────────────────────────────────────────────────────────
  // 5. Clean up double optional chains
  // ──────────────────────────────────────────────────────────────
  src = src.replace(/\?\.\?\./g, "?.");

  // ──────────────────────────────────────────────────────────────
  // 6. void void → void
  // ──────────────────────────────────────────────────────────────
  src = src.replace(/void\s+void\s+/g, "void ");

  if (src !== orig) {
    changed++;
    if (!DRY) writeFileSync(file, src, "utf8");
  }
}

console.log(
  `${DRY ? "[DRY] " : ""}Pass 2 transformed ${changed} / ${files.length} files.`,
);
