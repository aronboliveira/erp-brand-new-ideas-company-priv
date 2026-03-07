/**
 * Script to fix TS7006 (implicit any) errors by adding explicit types
 * to commonly-named parameters.
 */
const fs = require("fs");
const path = require("path");

const srcDir = path.join(__dirname, "src");

// Map parameter names to their types based on common patterns
const paramTypeMap = {
  // Event parameters
  e: "Event",
  ev: "Event",
  evt: "Event",
  event: "Event",
  mouseEvent: "MouseEvent",
  keyEvent: "KeyboardEvent",

  // DOM elements
  el: "HTMLElement",
  elem: "HTMLElement",
  element: "HTMLElement",
  target: "HTMLElement",
  container: "HTMLElement",
  parent: "HTMLElement",
  child: "HTMLElement",
  node: "Node",

  // Bootstrap-related
  tooltipTriggerEl: "Element",
  popoverTriggerEl: "Element",
  toastEl: "Element",
  modalEl: "Element",
  dropdownEl: "Element",
  collapseEl: "Element",

  // Form elements
  input: "HTMLInputElement",
  textarea: "HTMLTextAreaElement",
  select: "HTMLSelectElement",
  form: "HTMLFormElement",
  btn: "HTMLButtonElement",
  button: "HTMLButtonElement",
  field: "HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement",

  // Common types
  data: "unknown",
  response: "Response",
  result: "unknown",
  error: "Error",
  err: "Error",
  msg: "string",
  message: "string",
  text: "string",
  str: "string",
  s: "string",
  value: "unknown",
  val: "unknown",
  name: "string",
  key: "string",
  k: "string",
  id: "string | number",
  index: "number",
  idx: "number",
  i: "number",
  j: "number",
  n: "number",
  count: "number",
  num: "number",
  len: "number",
  length: "number",

  // URL/path related
  url: "string",
  href: "string",
  src: "string",
  path: "string",
  selector: "string",

  // Function-related
  callback: "() => void",
  cb: "() => void",
  fn: "(...args: unknown[]) => unknown",
  func: "(...args: unknown[]) => unknown",
  handler: "(e: Event) => void",
  listener: "(e: Event) => void",

  // Promise-related
  resolve: "(value: unknown) => void",
  reject: "(reason?: unknown) => void",

  // Array-related
  item: "unknown",
  items: "unknown[]",
  arr: "unknown[]",
  array: "unknown[]",
  list: "unknown[]",

  // Object-related
  obj: "Record<string, unknown>",
  options: "Record<string, unknown>",
  config: "Record<string, unknown>",
  settings: "Record<string, unknown>",
  params: "Record<string, unknown>",
  args: "unknown[]",

  // Image-related
  imgId: "string",
  inputId: "string",
  afterEl: "HTMLElement | null",
  explicit: "boolean",
};

// Patterns that should be fixed in specific contexts
const contextPatterns = [
  // .forEach((item) => ...) → .forEach((item: T) => ...)
  // Already we're matching by param name, but we can add more specific ones
];

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

function fixImplicitAny(content) {
  let modified = content;
  let fixes = 0;

  for (const [param, type] of Object.entries(paramTypeMap)) {
    // Match arrow function parameters: (param) => or (param, other) =>
    // Be careful to not match already-typed params like (param: Type)
    // Also match function parameters in callbacks

    // Pattern 1: Single param arrow function without parens: param =>
    // This is rare and usually needs parens for typing, so skip for now

    // Pattern 2: (param) or (param, in arrow/function params
    // Match: (param) but not (param: or (param? or param already has type
    const singleParamRegex = new RegExp(
      `\\(\\s*\\b(${param})\\b\\s*\\)\\s*=>`,
      "g",
    );
    const replacement1 = `(${param}: ${type}) =>`;
    const newContent1 = modified.replace(singleParamRegex, (match, p1) => {
      fixes++;
      return replacement1;
    });
    if (newContent1 !== modified) modified = newContent1;

    // Pattern 3: param in multiple params: (param, other) or (..., param, ...)
    // Match param NOT followed by : or ? which indicates typing
    const multiParamRegex = new RegExp(
      `\\(([^)]*?)\\b(${param})\\b(?!\\s*[:\\?])([^)]*?)\\)\\s*=>`,
      "g",
    );
    const newContent2 = modified.replace(
      multiParamRegex,
      (match, before, p, after) => {
        // Check if already has type
        if (before.includes(`${param}:`) || after.includes(`${param}:`)) {
          return match;
        }
        fixes++;
        return `(${before}${param}: ${type}${after}) =>`;
      },
    );
    if (newContent2 !== modified) modified = newContent2;

    // Pattern 4: function(param) or function name(param)
    const funcParamRegex = new RegExp(
      `(function\\s*\\w*\\s*\\([^)]*?)\\b(${param})\\b(?!\\s*[:\\?])([^)]*?\\))`,
      "g",
    );
    const newContent3 = modified.replace(
      funcParamRegex,
      (match, before, p, after) => {
        fixes++;
        return `${before}${param}: ${type}${after}`;
      },
    );
    if (newContent3 !== modified) modified = newContent3;
  }

  return { content: modified, fixes };
}

function processFile(filePath) {
  try {
    const content = fs.readFileSync(filePath, "utf-8");
    const result = fixImplicitAny(content);

    if (result.fixes > 0) {
      fs.writeFileSync(filePath, result.content, "utf-8");
      console.log(
        `Fixed ${result.fixes} implicit any in: ${path.relative(srcDir, filePath)}`,
      );
      totalFixes += result.fixes;
      filesFixed++;
    }
  } catch (err) {
    console.error(`Error processing ${filePath}:`, err.message);
  }
}

console.log("Finding TypeScript files...");
const files = getAllTsFiles(srcDir);
console.log(`Found ${files.length} TypeScript files`);

console.log("\nFixing implicit any types...\n");
for (const file of files) {
  processFile(file);
}

console.log(`\n=== Summary ===`);
console.log(`Files modified: ${filesFixed}`);
console.log(`Total fixes: ${totalFixes}`);
