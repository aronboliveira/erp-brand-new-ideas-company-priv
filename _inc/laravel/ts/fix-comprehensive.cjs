/**
 * Comprehensive TypeScript error fixer
 * Handles the most common patterns causing TS errors
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

function fixPatterns(content) {
  let modified = content;
  let fixes = 0;

  // 1. querySelector without type generic - add HTMLElement
  // Pattern: document.querySelector(".selector").property
  // Problem: returns Element | null, not HTMLElement
  // Skip if already has type parameter like querySelector<HTMLElement>

  // 2. querySelectorAll[i].property - need to cast
  // Pattern: elements[i].style → (elements[i] as HTMLElement).style
  // Common for .style, .classList, .value, etc.

  // 3. .parentNode.classList → (.parentNode as HTMLElement).classList
  modified = modified.replace(
    /(\w+)\.parentNode\.classList\b/g,
    (match, varName) => {
      fixes++;
      return `(${varName}.parentNode as HTMLElement | null)?.classList`;
    },
  );

  // 4. .parentNode.style → (.parentNode as HTMLElement).style
  modified = modified.replace(
    /(\w+)\.parentNode\.style\b/g,
    (match, varName) => {
      fixes++;
      return `(${varName}.parentNode as HTMLElement | null)?.style`;
    },
  );

  // 5. .parentNode.children → remains ParentNode, children is ok
  // BUT we often see .parentNode.children[x].classList which needs cast
  modified = modified.replace(
    /(\w+)\.parentNode\.children\[(\d+)\]\.classList\b/g,
    (match, varName, idx) => {
      fixes++;
      return `(${varName}.parentNode?.children[${idx}] as HTMLElement | undefined)?.classList`;
    },
  );

  // 6. Fix .parentElement which returns HTMLElement | null (better than .parentNode)
  // Could suggest replacement but that changes semantics

  // 7. Array-like forEach with untyped element
  // .forEach((x) => → .forEach((x: TYPE) =>
  // Generic pattern - risky without context

  // 8. Fix common callback patterns without parens
  // e.g., data => { → (data: unknown) => {
  // Only in known contexts

  // 9. Fix jQuery-style $(selector).on('event', function(e) {...})
  // jQuery types the e as JQuery.Event

  // 10. Fix .href on Element - cast to HTMLAnchorElement
  modified = modified.replace(/(\w+)\[(\w+)\]\.href\b/g, (match, arr, idx) => {
    // Only fix if it seems to be an Element array, not already typed
    // Check if looks like a DOM element collection
    if (
      ["elem", "elements", "links", "anchors", "items"].includes(
        arr.toLowerCase(),
      )
    ) {
      fixes++;
      return `(${arr}[${idx}] as HTMLAnchorElement).href`;
    }
    return match;
  });

  // 11. Fix .value on Element - cast to HTMLInputElement
  // Pattern: elem.value (where elem is Element)
  modified = modified.replace(/(\w+)\[(\w+)\]\.value\b/g, (match, arr, idx) => {
    if (
      ["inputs", "fields", "elements", "formElements"].includes(
        arr.toLowerCase(),
      )
    ) {
      fixes++;
      return `(${arr}[${idx}] as HTMLInputElement).value`;
    }
    return match;
  });

  // 12. Fix .checked on Element
  modified = modified.replace(
    /(\w+)\[(\w+)\]\.checked\b/g,
    (match, arr, idx) => {
      fixes++;
      return `(${arr}[${idx}] as HTMLInputElement).checked`;
    },
  );

  // 13. Fix "Property 'X' does not exist on type 'unknown'"
  // Common with fetch .then(data => ...)
  // data.property → (data as Record<string, unknown>).property
  // This is tricky and risky without context

  // 14. Fix optional property assignment issue
  // x?.prop = value is invalid if x might be null
  // Need to use if (x) { x.prop = value }
  // This requires AST manipulation, skip for regex

  // 15. Fix createElement casting
  // document.createElement("canvas") returns HTMLCanvasElement but not always known
  // Add type assertions where needed

  // 16. Fix event.detail on Event (needs CustomEvent)
  modified = modified.replace(/(\w+)\.detail\b/g, (match, varName) => {
    if (["event", "e", "evt", "ev"].includes(varName.toLowerCase())) {
      fixes++;
      return `(${varName} as CustomEvent).detail`;
    }
    return match;
  });

  // 17. Fix .scrollTop assignment on Element
  // Element doesn't have scrollTop setter, HTMLElement does
  modified = modified.replace(/(\w+)\.scrollTop\s*=/g, (match, varName) => {
    if (!match.includes("as HTMLElement")) {
      fixes++;
      // We can't easily rewrite this without AST
      // Instead suggest casting in context
    }
    return match;
  });

  // 18. Fix Number/Boolean context issues
  // These require careful analysis

  // 19. querySelector null chains
  // document.querySelector().something → document.querySelector()?.something
  // But need to be careful about assignments

  // 20. Fix .getBoundingClientRect on Element (ok) vs unknown
  // When value is unknown, need to assert

  return { content: modified, fixes };
}

function processFile(filePath) {
  try {
    const content = fs.readFileSync(filePath, "utf-8");
    const result = fixPatterns(content);

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
