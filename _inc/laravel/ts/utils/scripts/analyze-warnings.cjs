#!/usr/bin/env node
/**
 * Analyze ESLint warnings by rule and message pattern
 */
const fs = require("fs");
const data = JSON.parse(fs.readFileSync("/tmp/eslint-out.json", "utf8"));

const ruleMessages = {};
for (const f of data) {
  for (const m of f.messages) {
    if (m.severity !== 1) continue;
    const rule = m.ruleId || "unknown";
    if (!ruleMessages[rule]) ruleMessages[rule] = {};
    const msg = m.message.substring(0, 100);
    if (!ruleMessages[rule][msg]) ruleMessages[rule][msg] = 0;
    ruleMessages[rule][msg]++;
  }
}

for (const [rule, msgs] of Object.entries(ruleMessages).sort(
  (a, b) =>
    Object.values(b[1]).reduce((s, v) => s + v, 0) -
    Object.values(a[1]).reduce((s, v) => s + v, 0)
)) {
  const total = Object.values(msgs).reduce((s, v) => s + v, 0);
  console.log(`\n=== ${rule} (${total}) ===`);
  for (const [msg, count] of Object.entries(msgs).sort(
    (a, b) => b[1] - a[1]
  )) {
    console.log(`  ${count}  ${msg}`);
  }
}
