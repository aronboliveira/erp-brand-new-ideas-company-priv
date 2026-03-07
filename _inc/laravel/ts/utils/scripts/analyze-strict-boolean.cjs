const report = require("./eslint-report.json");

// Get sample files with strict-boolean-expressions errors
const errFiles = [];
for (const file of report) {
  const msgs = file.messages.filter(
    m => m.ruleId === "@typescript-eslint/strict-boolean-expressions",
  );
  if (msgs.length > 0) {
    errFiles.push({
      path: file.filePath.replace(/.*\/ts\//, ""),
      count: msgs.length,
      samples: msgs
        .slice(0, 3)
        .map(m => ({ line: m.line, message: m.message })),
    });
  }
}

console.log(
  `Files with strict-boolean-expressions (${errFiles.reduce((a, b) => a + b.count, 0)} total in ${errFiles.length} files):\n`,
);

// Group by message type
const msgTypes = {};
for (const file of errFiles) {
  for (const s of file.samples) {
    const key = s.message.replace(/["'][\w\s]+["']/g, "").slice(0, 60);
    msgTypes[key] = (msgTypes[key] || 0) + 1;
  }
}

console.log("Message types:");
Object.entries(msgTypes)
  .sort((a, b) => b[1] - a[1])
  .forEach(([msg, count]) => console.log(`  ${count}x: ${msg}`));

console.log("\nSample locations:");
errFiles.slice(0, 10).forEach(f => {
  console.log(`  ${f.path}: ${f.count} errors`);
  f.samples.forEach(s => console.log(`    Line ${s.line}: ${s.message}`));
});
