const fs = require("fs");
const path = require("path");
const report = require("./eslint-report.json");

// Collect all strict-boolean-expressions issues
const issues = [];
for (const file of report) {
  const msgs = file.messages.filter(
    m => m.ruleId === "@typescript-eslint/strict-boolean-expressions",
  );
  if (msgs.length > 0) {
    issues.push({
      path: file.filePath,
      locations: msgs.map(m => ({
        line: m.line,
        column: m.column,
        message: m.message,
      })),
    });
  }
}

console.log(
  `Found ${issues.reduce((a, b) => a + b.locations.length, 0)} strict-boolean-expressions issues in ${issues.length} files`,
);

let totalFixed = 0;

// These patterns need Boolean() wrapper or explicit checks
// 1. Window/document optional chain checks: if (window.x?.y) → if (window.x?.y != null)
// 2. String checks: if (str) → if (str !== "")
// 3. Number checks: if (num) → if (num !== 0)
// 4. Nullable object checks after optional chain

for (const issue of issues) {
  let content = fs.readFileSync(issue.path, "utf8");
  let changed = false;

  // Pattern 1: if (window.something?.something) - wrap with != null
  // This handles "always true" object checks from optional chaining
  const windowOptionalPattern = /\bif\s*\(\s*(window\.[\w.?]+)\s*\)/g;
  const m1 = content.match(windowOptionalPattern);
  if (m1) {
    content = content.replace(windowOptionalPattern, "if ($1 != null)");
    changed = true;
    totalFixed += m1.length;
    console.log(
      `  Fixed ${m1.length} window optional checks in ${path.basename(issue.path)}`,
    );
  }

  // Pattern 2: if (bsLink && window... - already fixed above causes issues
  // Let's be more conservative and only fix specific known patterns

  if (changed) {
    fs.writeFileSync(issue.path, content);
  }
}

console.log(`\nTotal fixed: ${totalFixed} patterns`);
console.log(
  "\nNote: Many remaining issues require manual inspection or are type-system conflicts.",
);
