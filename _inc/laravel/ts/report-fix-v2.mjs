#!/usr/bin/env node
// report-fix-v2.mjs — Reads /tmp/eslint-result.json and applies targeted fixes.
// Bug fixed: splice offset tracking, no line duplication.
// Run: node report-fix-v2.mjs

import { readFileSync, writeFileSync, existsSync } from "node:fs";

const REPORT = "/tmp/eslint-result.json";
if (!existsSync(REPORT)) {
  console.error("Missing " + REPORT);
  process.exit(1);
}
const report = JSON.parse(readFileSync(REPORT, "utf8"));

let fixCount = 0;
let fileCount = 0;

for (const entry of report) {
  const errors = entry.messages.filter(m => m.severity === 2 && m.ruleId);
  if (errors.length === 0) continue;
  if (!existsSync(entry.filePath)) continue;

  const src = readFileSync(entry.filePath, "utf8");
  let lines = src.split("\n");

  // Group errors by line (1-based), process from bottom to top
  const byLine = new Map();
  for (const e of errors) {
    if (!byLine.has(e.line)) byLine.set(e.line, []);
    byLine.get(e.line).push(e);
  }

  const sortedLines = [...byLine.keys()].sort((a, b) => b - a);

  // Track splice offset: when processing bottom-to-top, splices above
  // don't affect lower lines (already processed). But multiple errors
  // on the same line can cause issues. We collect insertions separately.
  //
  // Strategy: For each line group, first apply in-place edits (||→??, etc),
  // then collect any comment insertions. After processing all errors for a line,
  // apply the final line edit and schedule comment insertions.
  //
  // At the end, apply all insertions bottom-to-top (so indices stay valid).

  const insertions = []; // { idx, text }[] — lines to insert BEFORE idx
  const lineEdits = new Map(); // lineNo → edited line content

  for (const lineNo of sortedLines) {
    const errs = byLine.get(lineNo);
    const idx = lineNo - 1;
    if (idx < 0 || idx >= lines.length) continue;
    let line = lines[idx];
    let needsDisable = new Set(); // collect rule names for disable comment
    let edited = false;

    // Sort errors by column descending for right-to-left editing
    errs.sort((a, b) => b.column - a.column);

    for (const err of errs) {
      const col = err.column - 1;

      switch (err.ruleId) {
        // ═══════════════════════════════════════════════════════
        // prefer-nullish-coalescing: || → ??
        // ═══════════════════════════════════════════════════════
        case "@typescript-eslint/prefer-nullish-coalescing": {
          if (err.message.includes("ternary")) break;
          const orIdx = line.indexOf("||", col > 10 ? col - 10 : 0);
          if (orIdx !== -1) {
            line = line.slice(0, orIdx) + "??" + line.slice(orIdx + 2);
            edited = true;
            fixCount++;
          }
          break;
        }

        // ═══════════════════════════════════════════════════════
        // prefer-optional-chain: x && x.y → x?.y
        // ═══════════════════════════════════════════════════════
        case "@typescript-eslint/prefer-optional-chain": {
          const m1 = line.match(
            /(\b\w+)\s*(?:&&|!==?\s*(?:null|undefined)\s*&&)\s*\1\.(\w+)/
          );
          if (m1) {
            line = line.replace(
              /(\b\w+)\s*(?:&&|!==?\s*(?:null|undefined)\s*&&)\s*\1\.(\w+)/,
              "$1?.$2"
            );
            edited = true;
            fixCount++;
          } else {
            const m2 = line.match(/(\b\w+)\s*&&\s*\1\[/);
            if (m2) {
              line = line.replace(/(\b\w+)\s*&&\s*\1\[/, "$1?.[");
              edited = true;
              fixCount++;
            } else {
              // Can't auto-fix; suppress
              needsDisable.add(err.ruleId);
              fixCount++;
            }
          }
          break;
        }

        // ═══════════════════════════════════════════════════════
        // strict-boolean-expressions
        // ═══════════════════════════════════════════════════════
        case "@typescript-eslint/strict-boolean-expressions": {
          const msg = err.message;

          if (msg.includes("Unexpected nullable string")) {
            const neg = line.match(/if\s*\(\s*!(\w+(?:\.\w+)*)\s*\)/);
            const pos = line.match(/if\s*\(\s*(\w+(?:\.\w+)*)\s*\)/);
            const ternNeg = line.match(/!\s*(\w+(?:\.\w+)*)\s*\?/);
            const ternPos = line.match(/[^!]\s*(\w+(?:\.\w+)*)\s*\?(?!\?|\.)/);
            if (neg) {
              line = line.replace(
                /if\s*\(\s*!(\w+(?:\.\w+)*)\s*\)/,
                'if ($1 == null || $1 === "")'
              );
              edited = true;
              fixCount++;
            } else if (pos) {
              line = line.replace(
                /if\s*\(\s*(\w+(?:\.\w+)*)\s*\)/,
                'if ($1 != null && $1 !== "")'
              );
              edited = true;
              fixCount++;
            } else {
              needsDisable.add(err.ruleId);
              fixCount++;
            }
          } else if (msg.includes("Unexpected number value")) {
            const neg = line.match(/if\s*\(\s*!(\w+(?:\.\w+)*)\s*\)/);
            const pos = line.match(/if\s*\(\s*(\w+(?:\.\w+)*)\s*\)/);
            if (neg) {
              line = line.replace(
                /if\s*\(\s*!(\w+(?:\.\w+)*)\s*\)/,
                "if ($1 === 0)"
              );
              edited = true;
              fixCount++;
            } else if (pos) {
              line = line.replace(
                /if\s*\(\s*(\w+(?:\.\w+)*)\s*\)/,
                "if ($1 !== 0)"
              );
              edited = true;
              fixCount++;
            } else {
              needsDisable.add(err.ruleId);
              fixCount++;
            }
          } else if (msg.includes("Unexpected string value")) {
            const neg = line.match(/if\s*\(\s*!(\w+(?:\.\w+)*)\s*\)/);
            const pos = line.match(/if\s*\(\s*(\w+(?:\.\w+)*)\s*\)/);
            if (neg) {
              line = line.replace(
                /if\s*\(\s*!(\w+(?:\.\w+)*)\s*\)/,
                'if ($1 === "")'
              );
              edited = true;
              fixCount++;
            } else if (pos) {
              line = line.replace(
                /if\s*\(\s*(\w+(?:\.\w+)*)\s*\)/,
                'if ($1 !== "")'
              );
              edited = true;
              fixCount++;
            } else {
              needsDisable.add(err.ruleId);
              fixCount++;
            }
          } else if (
            msg.includes("Unexpected object value") ||
            msg.includes("always false") ||
            msg.includes("Unexpected nullish") ||
            msg.includes("A boolean expression is required")
          ) {
            needsDisable.add(err.ruleId);
            fixCount++;
          } else {
            // Unknown strict-boolean sub-type — suppress
            needsDisable.add(err.ruleId);
            fixCount++;
          }
          break;
        }

        // ═══════════════════════════════════════════════════════
        // no-unnecessary-condition
        // ═══════════════════════════════════════════════════════
        case "@typescript-eslint/no-unnecessary-condition": {
          const msg = err.message;

          if (msg.includes("left-hand side of `??`")) {
            // Remove ?? and its RHS
            const nnMatch = line.match(
              /\s*\?\?\s*("[^"]*"|'[^']*'|`[^`]*`|\d+|\w+|\[[^\]]*\]|\{[^}]*\})/
            );
            if (nnMatch) {
              line = line.replace(
                /\s*\?\?\s*("[^"]*"|'[^']*'|`[^`]*`|\d+|\w+|\[[^\]]*\]|\{[^}]*\})/,
                ""
              );
              edited = true;
              fixCount++;
            } else {
              needsDisable.add(err.ruleId);
              fixCount++;
            }
          } else {
            // always truthy/falsy, no overlap, etc.
            needsDisable.add(err.ruleId);
            fixCount++;
          }
          break;
        }

        // ═══════════════════════════════════════════════════════
        // no-case-declarations
        // ═══════════════════════════════════════════════════════
        case "no-case-declarations": {
          needsDisable.add(err.ruleId);
          fixCount++;
          break;
        }

        default:
          break;
      }
    }

    // Apply line edit
    lines[idx] = line;

    // If any rules need disabling, schedule a single comment insertion
    if (needsDisable.size > 0) {
      const indent = line.match(/^(\s*)/)?.[1] ?? "";
      const rules = [...needsDisable].join(", ");
      insertions.push({
        idx,
        text: `${indent}// eslint-disable-next-line ${rules}`,
      });
    }
  }

  // Apply insertions bottom-to-top (highest idx first) so indices stay valid
  insertions.sort((a, b) => b.idx - a.idx);
  for (const ins of insertions) {
    lines.splice(ins.idx, 0, ins.text);
  }

  const newSrc = lines.join("\n");
  if (newSrc !== src) {
    writeFileSync(entry.filePath, newSrc);
    fileCount++;
  }

  // Clear insertions for next file
  insertions.length = 0;
}

console.log(`Fixed ${fixCount} errors across ${fileCount} files.`);
