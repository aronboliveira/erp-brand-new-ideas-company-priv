const report = require("./eslint-report.json");

// Get files with prefer-optional-chain errors
const optChainFiles = [];
for (const file of report) {
  const msgs = file.messages.filter(
    m => m.ruleId === "@typescript-eslint/prefer-optional-chain",
  );
  if (msgs.length > 0) {
    optChainFiles.push({
      path: file.filePath.replace(/.*\/ts\//, ""),
      count: msgs.length,
      lines: msgs.map(m => m.line),
    });
  }
}

console.log(
  `Files with prefer-optional-chain (${optChainFiles.reduce((a, b) => a + b.count, 0)} total):`,
);
optChainFiles.forEach(f =>
  console.log(
    `  ${f.path}: lines ${f.lines.slice(0, 5).join(", ")}${f.lines.length > 5 ? "..." : ""}`,
  ),
);
