const report = require("./eslint-report.json");

console.log("=== Remaining ERRORS ===\n");

for (const file of report) {
  const errors = file.messages.filter(m => m.severity === 2);
  if (errors.length > 0) {
    console.log(`\n${file.filePath.replace(/.*\/ts\//, "")}:`);
    errors.forEach(e => {
      console.log(`  Line ${e.line}:${e.column} [${e.ruleId}]`);
      console.log(`    ${e.message}`);
    });
  }
}
