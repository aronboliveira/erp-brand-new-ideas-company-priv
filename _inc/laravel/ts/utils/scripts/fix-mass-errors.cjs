/**
 * Mass TypeScript Error Fixer
 * Handles the most common patterns causing TS errors with noImplicitAny
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

function fixPatterns(content, filePath) {
  let modified = content;
  let fixes = 0;

  // === TS7006: Parameter implicitly has 'any' type ===
  // Common event handlers: function(e) { → function(e: Event) {
  // Keep original parameter patterns but add types

  // Fix addEventListener callbacks - (e) => or function(e)
  // Pattern: .addEventListener("xxx", (e) =>
  modified = modified.replace(
    /\.addEventListener\s*\(\s*["']([^"']+)["']\s*,\s*(?:function\s*)?\((\w+)\)\s*(?:=>)?\s*\{/g,
    (match, event, param) => {
      // Map event types
      let eventType = "Event";
      if (
        [
          "click",
          "dblclick",
          "mousedown",
          "mouseup",
          "mouseover",
          "mouseout",
          "mousemove",
          "mouseenter",
          "mouseleave",
          "contextmenu",
        ].includes(event)
      ) {
        eventType = "MouseEvent";
      } else if (["keydown", "keyup", "keypress"].includes(event)) {
        eventType = "KeyboardEvent";
      } else if (
        ["input", "change", "blur", "focus", "focusin", "focusout"].includes(
          event,
        )
      ) {
        eventType = "Event";
      } else if (["submit", "reset"].includes(event)) {
        eventType = "Event";
      } else if (
        ["touchstart", "touchend", "touchmove", "touchcancel"].includes(event)
      ) {
        eventType = "TouchEvent";
      } else if (
        [
          "drag",
          "dragstart",
          "dragend",
          "dragover",
          "dragenter",
          "dragleave",
          "drop",
        ].includes(event)
      ) {
        eventType = "DragEvent";
      } else if (["wheel", "scroll"].includes(event)) {
        eventType = "Event";
      } else if (["resize"].includes(event)) {
        eventType = "UIEvent";
      } else if (["load", "error"].includes(event)) {
        eventType = "Event";
      }

      fixes++;
      // Check if it was arrow or regular function
      if (match.includes("=>")) {
        return `.addEventListener("${event}", (${param}: ${eventType}) => {`;
      } else {
        return `.addEventListener("${event}", function(${param}: ${eventType}) {`;
      }
    },
  );

  // Fix .forEach callbacks with single parameter
  // .forEach((item) => { → .forEach((item: unknown) => {
  // But only if no type annotation exists
  modified = modified.replace(
    /\.forEach\s*\(\s*\((\w+)\)\s*=>\s*\{/g,
    (match, param) => {
      // Skip if already has type
      if (match.includes(":")) return match;
      fixes++;
      return `.forEach((${param}: unknown) => {`;
    },
  );

  // Fix .map callbacks
  modified = modified.replace(
    /\.map\s*\(\s*\((\w+)\)\s*=>\s*\{/g,
    (match, param) => {
      if (match.includes(":")) return match;
      fixes++;
      return `.map((${param}: unknown) => {`;
    },
  );

  // Fix .filter callbacks
  modified = modified.replace(
    /\.filter\s*\(\s*\((\w+)\)\s*=>\s*\{/g,
    (match, param) => {
      if (match.includes(":")) return match;
      fixes++;
      return `.filter((${param}: unknown) => {`;
    },
  );

  // Fix .then callbacks with simple parameter: .then(result => or .then((result) =>
  modified = modified.replace(
    /\.then\s*\(\s*\(?\s*(\w+)\s*\)?\s*=>\s*\{/g,
    (match, param) => {
      if (match.includes(":")) return match;
      fixes++;
      return `.then((${param}: unknown) => {`;
    },
  );

  // Fix .catch callbacks
  modified = modified.replace(
    /\.catch\s*\(\s*\(?\s*(\w+)\s*\)?\s*=>\s*\{/g,
    (match, param) => {
      if (match.includes(":")) return match;
      fixes++;
      return `.catch((${param}: unknown) => {`;
    },
  );

  // === TS1064: async function return type must be Promise<T> ===
  // async function name() { → async function name(): Promise<void> {
  // But skip if already has return type
  modified = modified.replace(
    /async\s+function\s+(\w+)\s*\([^)]*\)\s*\{/g,
    (match, name) => {
      // Skip if already has return type (look for : before {)
      if (/\)\s*:\s*/.test(match)) return match;
      fixes++;
      return match.replace(/\)\s*\{/, "): Promise<void> {");
    },
  );

  // async () => { without return type - harder to fix safely, skip for now

  // === TS7034/TS7005: Variable implicitly has 'any' type ===
  // Common patterns like: let timerInterval;
  // Add : number | undefined for timer-like names
  modified = modified.replace(
    /let\s+(timer\w*|interval\w*)\s*;/gi,
    (match, varName) => {
      fixes++;
      return `let ${varName}: ReturnType<typeof setTimeout> | undefined;`;
    },
  );

  // Variables ending in Id often are numbers or strings
  modified = modified.replace(/let\s+(\w+Id)\s*;/g, (match, varName) => {
    fixes++;
    return `let ${varName}: string | number | undefined;`;
  });

  // === Fix .parentNode to HTMLElement cast for common operations ===
  // .parentNode.classList → (.parentNode as HTMLElement | null)?.classList
  // But don't double-fix
  modified = modified.replace(
    /(\w+)\.parentNode\.(classList|style|remove|querySelector|insertAdjacentHTML)/g,
    (match, varName, property) => {
      if (match.includes("as HTMLElement")) return match;
      fixes++;
      return `(${varName}.parentNode as HTMLElement | null)?.${property}`;
    },
  );

  // === Fix event.target casts for DOM properties ===
  // event.target.classList → (event.target as HTMLElement).classList
  modified = modified.replace(
    /(event|e|ev|evt)\.target\.(classList|style|value|checked|getAttribute|querySelector)/g,
    (match, eventVar, property) => {
      if (match.includes("as")) return match;
      fixes++;
      return `(${eventVar}.target as HTMLElement | null)?.${property}`;
    },
  );

  // === Fix querySelector null coalescing ===
  // document.querySelector(".x").classList → document.querySelector(".x")?.classList
  // Only where . follows directly without null check
  modified = modified.replace(
    /(document\.querySelector(?:<[^>]+>)?\s*\([^)]+\))\.(classList|style|textContent|innerHTML|value)/g,
    (match, selector, property) => {
      if (selector.includes("?.")) return match;
      fixes++;
      return `${selector}?.${property}`;
    },
  );

  // === Fix .checked on Element (needs HTMLInputElement) ===
  modified = modified.replace(/(\w+)\.checked\b/g, (match, varName) => {
    // Skip if already cast
    if (
      match.includes("as") ||
      ["input", "checkbox", "radio"].some(n =>
        varName.toLowerCase().includes(n),
      )
    ) {
      return match;
    }
    // Don't auto-fix this one - too risky without context
    return match;
  });

  // === Fix missing optional chaining on querySelector chains ===
  // .querySelector(".x").querySelector(".y") → .querySelector(".x")?.querySelector(".y")
  modified = modified.replace(
    /\.querySelector\s*\([^)]+\)\.querySelector/g,
    match => {
      if (match.includes("?.")) return match;
      fixes++;
      return match.replace(").querySelector", ")?.querySelector");
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

console.log("Mass TypeScript Error Fixer");
console.log("==========================\n");
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
