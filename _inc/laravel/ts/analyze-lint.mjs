#!/usr/bin/env node
// analyze-lint.mjs — Parse ESLint JSON output and print breakdown
import { readFileSync, existsSync } from "node:fs";
const REPORT = "/tmp/eslint-result.json";
if (!existsSync(REPORT)) { console.error("Missing " + REPORT); process.exit(1); }
const report = JSON.parse(readFileSync(REPORT, "utf8"));
const total = report.length;
let errCnt = 0, warnCnt = 0;
const byRule = {};
let withIssues = 0;
for (const f of report) {
  if (f.messages.length > 0) withIssues++;
  for (const m of f.messages) {
    if (m.severity === 2) errCnt++; else warnCnt++;
    const r = m.ruleId || "(parse-error)";
    if (!byRule[r]) byRule[r] = { total: 0, fixable: 0, errors: 0, warnings: 0 };
    byRule[r].total++;
    if (m.severity === 2) byRule[r].errors++; else byRule[r].warnings++;
    if (m.fix) byRule[r].fixable++;
  }
}
console.log(`Files with issues: ${withIssues} / ${total}`);
console.log(`Errors: ${errCnt}   Warnings: ${warnCnt}`);
console.log("");
console.log("RULE".padEnd(55) + "ERR".padStart(6) + "WARN".padStart(7) + "FIX".padStart(6));
const sorted = Object.entries(byRule).sort((a, b) => b[1].total - a[1].total);
for (const [rule, s] of sorted) {
  console.log(rule.padEnd(55) + String(s.errors).padStart(6) + String(s.warnings).padStart(7) + String(s.fixable).padStart(6));
}
