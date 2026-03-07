/**
 * Arrow Function Parameter Type Fixer
 * Handles the pattern: const funcName = param => { → const funcName = (param: unknown) => {
 */
const fs = require("fs");
const path = require("path");

const srcDir = path.join(__dirname, "src");
let totalFixes = 0;
let filesFixed = 0;

function getAllTsFiles(dir, fileList = []) {
  const files = fs.readdirSync(dir);
  for (const file of files) {
    const filePath = path.join(dir, file);
    const stat = fs.statSync(filePath);
    if (stat.isDirectory()) {
      getAllTsFiles(filePath, fileList);
    } else if (file.endsWith(".ts") && !file.endsWith(".d.ts")) {
      fileList.push(filePath);
    }
  }
  return fileList;
}

// Parameter type inference based on name
function inferTypeFromName(paramName) {
  const lower = paramName.toLowerCase();

  // Event-related
  if (["e", "ev", "evt", "event"].includes(lower)) return "Event";

  // DOM elements
  if (
    ["el", "elem", "element", "node", "target", "afterel", "beforeel"].includes(
      lower,
    )
  )
    return "HTMLElement | null";

  // Strings
  if (
    lower.includes("message") ||
    lower.includes("msg") ||
    lower.includes("text") ||
    lower.includes("name") ||
    lower.includes("key") ||
    lower.includes("id") ||
    lower.includes("str") ||
    lower.includes("label") ||
    lower.includes("title") ||
    lower.includes("content") ||
    lower.includes("html") ||
    lower.includes("url") ||
    lower.includes("path") ||
    lower.includes("class") ||
    lower.includes("selector")
  ) {
    return "string";
  }

  // Numbers
  if (
    lower.includes("index") ||
    lower.includes("idx") ||
    lower === "i" ||
    lower === "j" ||
    lower === "n" ||
    lower.includes("count") ||
    lower.includes("num") ||
    lower.includes("amount") ||
    lower.includes("price") ||
    lower.includes("total") ||
    lower.includes("width") ||
    lower.includes("height") ||
    lower.includes("size") ||
    lower.includes("delay") ||
    lower.includes("duration")
  ) {
    return "number";
  }

  // Booleans
  if (
    lower.includes("is") ||
    lower.includes("has") ||
    lower.includes("can") ||
    lower.includes("should") ||
    lower.includes("flag") ||
    lower.includes("enabled") ||
    lower.includes("disabled") ||
    lower.includes("visible") ||
    lower.includes("active") ||
    lower.includes("throttle")
  ) {
    return "boolean";
  }

  // Functions/callbacks
  if (
    lower.includes("callback") ||
    lower.includes("handler") ||
    lower.includes("fn") ||
    lower.includes("func")
  ) {
    return "(...args: unknown[]) => void";
  }

  // Response/data objects
  if (
    lower.includes("response") ||
    lower.includes("res") ||
    lower.includes("data") ||
    lower.includes("result") ||
    lower.includes("item") ||
    lower.includes("obj") ||
    lower.includes("record") ||
    lower.includes("row")
  ) {
    return "Record<string, unknown>";
  }

  // Arrays
  if (
    lower.includes("items") ||
    lower.includes("list") ||
    lower.includes("array") ||
    lower.includes("elements") ||
    lower.includes("nodes")
  ) {
    return "unknown[]";
  }

  // Form-related
  if (lower.includes("form")) return "HTMLFormElement | null";
  if (lower.includes("input")) return "HTMLInputElement | null";
  if (lower.includes("button")) return "HTMLButtonElement | null";

  // Default to unknown
  return "unknown";
}

function fixPatterns(content, filePath) {
  let modified = content;
  let fixes = 0;

  // Pattern 1: const/let funcName = param => { (single param, no parens, no type)
  modified = modified.replace(
    /^(\s*(?:const|let|var)\s+\w+\s*=\s*)(\w+)\s*=>\s*\{/gm,
    (match, prefix, param) => {
      const type = inferTypeFromName(param);
      fixes++;
      return `${prefix}(${param}: ${type}) => {`;
    },
  );

  // Pattern 2: (param) => { where param has no type
  // But be careful not to match (param: type) =>
  modified = modified.replace(/=\s*\((\w+)\)\s*=>\s*\{/g, (match, param) => {
    // Skip if looks like it has a type (: follows param)
    const type = inferTypeFromName(param);
    fixes++;
    return `= (${param}: ${type}) => {`;
  });

  // Pattern 3: function parameters in regular functions
  // function name(param) { where param has no type
  // This is tricky because we need to handle multiple params
  // Let's do a simple single-param version first
  modified = modified.replace(
    /function\s+(\w+)\s*\((\w+)\)\s*(?::\s*\w+\s*)?\{/g,
    (match, funcName, param) => {
      // Skip if already has a type after param
      if (match.includes(param + ":") || match.includes(param + " :"))
        return match;
      const type = inferTypeFromName(param);
      // Check for return type
      const returnTypeMatch = match.match(/\)\s*:\s*(\w+)\s*\{/);
      if (returnTypeMatch) {
        fixes++;
        return `function ${funcName}(${param}: ${type}): ${returnTypeMatch[1]} {`;
      } else {
        fixes++;
        return `function ${funcName}(${param}: ${type}) {`;
      }
    },
  );

  // Pattern 4: Fix two-param arrow functions
  // (a, b) => { → (a: T, b: T) => {
  modified = modified.replace(
    /=\s*\((\w+)\s*,\s*(\w+)\)\s*=>\s*\{/g,
    (match, param1, param2) => {
      const type1 = inferTypeFromName(param1);
      const type2 = inferTypeFromName(param2);
      fixes++;
      return `= (${param1}: ${type1}, ${param2}: ${type2}) => {`;
    },
  );

  // Pattern 5: Fix three-param arrow functions
  modified = modified.replace(
    /=\s*\((\w+)\s*,\s*(\w+)\s*,\s*(\w+)\)\s*=>\s*\{/g,
    (match, param1, param2, param3) => {
      const type1 = inferTypeFromName(param1);
      const type2 = inferTypeFromName(param2);
      const type3 = inferTypeFromName(param3);
      fixes++;
      return `= (${param1}: ${type1}, ${param2}: ${type2}, ${param3}: ${type3}) => {`;
    },
  );

  return { content: modified, fixes };
}

function processFile(filePath) {
  try {
    const content = fs.readFileSync(filePath, "utf-8");
    const result = fixPatterns(content, filePath);

    if (result.fixes > 0) {
      fs.writeFileSync(filePath, result.content, "utf-8");
      console.log(
        `Fixed ${result.fixes} patterns in: ${path.relative(srcDir, filePath)}`,
      );
      totalFixes += result.fixes;
      filesFixed++;
    }
  } catch (err) {
    console.error(`Error processing ${filePath}:`, err.message);
  }
}

console.log("Arrow Function Parameter Type Fixer");
console.log("===================================\n");
console.log("Finding TypeScript files...");
const files = getAllTsFiles(srcDir);
console.log(`Found ${files.length} TypeScript files\n`);

console.log("Fixing patterns...\n");
for (const file of files) {
  processFile(file);
}

console.log(`\n=== Summary ===`);
console.log(`Files modified: ${filesFixed}`);
console.log(`Total fixes: ${totalFixes}`);
