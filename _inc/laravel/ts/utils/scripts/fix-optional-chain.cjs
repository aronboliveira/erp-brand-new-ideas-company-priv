const fs = require("fs");
const path = require("path");
const report = require("./eslint-report.json");

// Collect all files with prefer-optional-chain issues
const issues = [];
for (const file of report) {
  const msgs = file.messages.filter(
    m => m.ruleId === "@typescript-eslint/prefer-optional-chain",
  );
  if (msgs.length > 0) {
    issues.push({
      path: file.filePath,
      locations: msgs.map(m => ({ line: m.line, column: m.column })),
    });
  }
}

console.log(
  `Found ${issues.reduce((a, b) => a + b.locations.length, 0)} prefer-optional-chain issues in ${issues.length} files`,
);

let totalFixed = 0;
for (const issue of issues) {
  let content = fs.readFileSync(issue.path, "utf8");
  const lines = content.split("\n");
  let changed = false;

  // Pattern: expr && expr.prop → expr?.prop
  // Pattern: expr && expr[key] → expr?.[key]
  // Pattern: a && a.b && a.b.c → a?.b?.c

  const patterns = [
    // Handle: typeof window.X !== "undefined" && window.X.Y → window.X?.Y
    {
      regex:
        /typeof\s+(window\.(\w+))\s*!==?\s*["']undefined["']\s*&&\s*\1\.(\w+)/g,
      replace: "window.$2?.$3",
    },
    // Handle: typeof X !== "undefined" && X.Y → X?.Y (for any object)
    {
      regex: /typeof\s+([\w.]+)\s*!==?\s*["']undefined["']\s*&&\s*\1\.(\w+)/g,
      replace: "$1?.$2",
    },
    // Handle: obj && obj.prop && obj.prop.sub → obj?.prop?.sub
    {
      regex: /(\b(\w+(?:\.\w+)*)\s*&&\s*)\2\.(\w+)\s*&&\s*\2\.\3\.(\w+)/g,
      replace: "$2?.$3?.$4",
    },
    // Handle: obj && obj.prop → obj?.prop
    {
      regex: /(\b(\w+(?:\.\w+)*)\s*&&\s*)\2\.(\w+)/g,
      replace: "$2?.$3",
    },
    // Handle: window.obj && window.obj.prop → window.obj?.prop
    {
      regex: /\b(window\.(\w+))\s*&&\s*\1\.(\w+)/g,
      replace: "window.$2?.$3",
    },
    // Handle: this.obj && this.obj.prop → this.obj?.prop
    {
      regex: /\b(this\.(\w+))\s*&&\s*\1\.(\w+)/g,
      replace: "this.$2?.$3",
    },
  ];

  for (const { regex, replace } of patterns) {
    const matches = content.match(regex);
    if (matches) {
      const prev = content;
      content = content.replace(regex, replace);
      if (content !== prev) {
        changed = true;
        totalFixed += matches.length;
        console.log(
          `  Fixed ${matches.length} in ${path.basename(issue.path)}`,
        );
      }
    }
  }

  if (changed) {
    fs.writeFileSync(issue.path, content);
  }
}

console.log(`\nTotal fixed: ${totalFixed} patterns`);
