#!/usr/bin/env node
// fix-last2.mjs — Fix remaining 21 warnings
import { readFileSync, writeFileSync } from "node:fs";

const REPORT = "/tmp/eslint-result.json";
const report = JSON.parse(readFileSync(REPORT, "utf8"));
let fixed = 0;

for (const entry of report) {
  const warnings = entry.messages.filter(m => m.severity === 1);
  if (warnings.length === 0) continue;

  let lines = readFileSync(entry.filePath, "utf8").split("\n");
  const orig = lines.join("\n");

  // Sort by line desc
  warnings.sort((a, b) => b.line - a.line);

  for (const w of warnings) {
    const idx = w.line - 1;
    if (idx < 0 || idx >= lines.length) continue;
    let line = lines[idx];

    if (w.ruleId === "prefer-const") {
      const col = w.column - 1;
      const before = line.slice(0, col);
      const after = line.slice(col);
      if (after.startsWith("let ")) {
        lines[idx] = before + "const " + after.slice(4);
        fixed++;
      }
    } else if (w.ruleId === "@typescript-eslint/no-floating-promises") {
      // Add "void" before the function call at the column
      const col = w.column - 1;
      const before = line.slice(0, col);
      const after = line.slice(col);
      // Check if it's inside an if: "if (cond) call()" → "if (cond) void call()"
      // Or standalone: "call()" → "void call()"
      if (!after.startsWith("void ")) {
        lines[idx] = before + "void " + after;
        fixed++;
      }
    }
  }

  const newSrc = lines.join("\n");
  if (newSrc !== orig) {
    writeFileSync(entry.filePath, newSrc);
  }
}

console.log(`Fixed ${fixed} remaining warnings.`);
