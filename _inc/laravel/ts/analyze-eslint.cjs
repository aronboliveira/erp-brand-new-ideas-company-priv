const report = require("./eslint-report.json");
let totalErrors = 0,
  totalWarnings = 0;
const ruleStats = {};

for (const file of report) {
  totalErrors += file.errorCount;
  totalWarnings += file.warningCount;
  for (const msg of file.messages) {
    const key = msg.ruleId || "unknown";
    if (!ruleStats[key]) {
      ruleStats[key] = { errors: 0, warnings: 0 };
    }
    if (msg.severity === 2) {
      ruleStats[key].errors++;
    } else {
      ruleStats[key].warnings++;
    }
  }
}

console.log("Total Errors:", totalErrors);
console.log("Total Warnings:", totalWarnings);
console.log("Top rules:");
Object.entries(ruleStats)
  .sort((a, b) => b[1].errors + b[1].warnings - (a[1].errors + a[1].warnings))
  .slice(0, 25)
  .forEach(([rule, counts]) =>
    console.log(
      "  ",
      rule,
      ":",
      counts.errors,
      "errors,",
      counts.warnings,
      "warnings",
    ),
  );
