/**
 * Script to fix common TypeScript errors automatically:
 * - TS2339: Property doesn't exist on EventTarget (cast to HTMLElement)
 * - TS18047: Possibly null (add null checks)
 * - TS2322: Type mismatch in PerfectScrollbar options
 */
const fs = require("fs");
const path = require("path");

const srcDir = path.join(__dirname, "src");

let totalFixes = 0;
let filesFixed = 0;
const fixedFilePaths = [];

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

function fixCommonPatterns(content) {
  let modified = content;
  let fixes = 0;

  // Fix 1: PerfectScrollbar swipeEasing: 0 → swipeEasing: false
  modified = modified.replace(/swipeEasing:\s*0\b/g, () => {
    fixes++;
    return "swipeEasing: false";
  });

  // Fix 2: PerfectScrollbar wheelPropagation: 1 → wheelPropagation: true
  modified = modified.replace(/wheelPropagation:\s*1\b/g, () => {
    fixes++;
    return "wheelPropagation: true";
  });

  // Fix 3: suppressScrollX: !0 → suppressScrollX: true
  modified = modified.replace(/suppressScrollX:\s*!0\b/g, () => {
    fixes++;
    return "suppressScrollX: true";
  });

  // Fix 4: event.target without cast → cast to HTMLElement
  modified = modified.replace(
    /(\blet\s+targetElement\s*=\s*event\.target)\s*;/g,
    match => {
      fixes++;
      return match.replace(
        "event.target;",
        "event.target as HTMLElement | null;",
      );
    },
  );

  modified = modified.replace(
    /(\bconst\s+targetElement\s*=\s*event\.target)\s*;/g,
    match => {
      fixes++;
      return match.replace(
        "event.target;",
        "event.target as HTMLElement | null;",
      );
    },
  );

  // Fix 5: elem[j].style → (elem[j] as HTMLElement).style (when not already cast)
  modified = modified.replace(
    /(?<!\()\belem\[(\w+)\]\.style\b/g,
    (match, idx) => {
      fixes++;
      return `(elem[${idx}] as HTMLElement).style`;
    },
  );

  // Fix 6: e.target.parentNode → (e.target as HTMLElement | null)?.parentNode
  modified = modified.replace(/\be\.target\.parentNode\b/g, () => {
    fixes++;
    return "(e.target as HTMLElement | null)?.parentNode";
  });

  // Fix 7: e.target.classList → (e.target as HTMLElement | null)?.classList
  modified = modified.replace(/\be\.target\.classList\b/g, () => {
    fixes++;
    return "(e.target as HTMLElement | null)?.classList";
  });

  // Fix 8: e.target.style → (e.target as HTMLElement | null)?.style
  modified = modified.replace(/\be\.target\.style\b/g, () => {
    fixes++;
    return "(e.target as HTMLElement | null)?.style";
  });

  // Fix 9: .then(response => response.json()) → typed
  modified = modified.replace(
    /\.then\(\s*response\s*=>\s*response\.json\(\)\s*\)/g,
    () => {
      fixes++;
      return ".then((response: Response) => response.json())";
    },
  );

  // Fix 10: .then((data) => → .then((data: unknown) =>
  modified = modified.replace(/\.then\(\s*\(\s*data\s*\)\s*=>/g, () => {
    fixes++;
    return ".then((data: unknown) =>";
  });

  // Fix 11: .catch((error) => → .catch((error: unknown) =>
  modified = modified.replace(/\.catch\(\s*\(\s*error\s*\)\s*=>/g, () => {
    fixes++;
    return ".catch((error: unknown) =>";
  });

  // Fix 12: .parentNode.classList → .parentNode?.classList as HTMLElement cast
  // This is tricky - parentNode returns ParentNode | null, and ParentNode doesn't have classList
  modified = modified.replace(/\.parentNode\.classList\b/g, () => {
    fixes++;
    return "?.parentNode && (targetElement.parentNode as HTMLElement).classList";
  });

  // Fix 13: .parentElement.classList → ?.parentElement?.classList (safer)
  modified = modified.replace(/\.parentElement\.classList\b/g, () => {
    fixes++;
    return "?.parentElement?.classList";
  });

  // Fix 14: (el).value where el is HTMLElement → (el as HTMLInputElement).value
  // Don't apply if already typed

  // Fix 15: document.querySelector(...).value → cast to HTMLInputElement
  modified = modified.replace(
    /(document\.querySelector(?:<[^>]+>)?\([^)]+\))\.value\b(?!\s*[=!])/g,
    (match, selector) => {
      // Only fix if not already HTMLInputElement type param
      if (
        selector.includes("HTMLInputElement") ||
        selector.includes("HTMLSelectElement") ||
        selector.includes("HTMLTextAreaElement")
      ) {
        return match;
      }
      fixes++;
      return `(${selector} as HTMLInputElement | null)?.value`;
    },
  );

  // Fix 16: .querySelector(...).checked → cast
  modified = modified.replace(
    /(document\.querySelector(?:<[^>]+>)?\([^)]+\))\.checked\b/g,
    (match, selector) => {
      if (selector.includes("HTMLInputElement")) return match;
      fixes++;
      return `(${selector} as HTMLInputElement | null)?.checked`;
    },
  );

  // Fix 17: .detail on Event → CustomEvent
  // This needs context, skip for now

  return { content: modified, fixes };
}

function processFile(filePath) {
  try {
    const content = fs.readFileSync(filePath, "utf-8");
    const result = fixCommonPatterns(content);

    if (result.fixes > 0) {
      fs.writeFileSync(filePath, result.content, "utf-8");
      console.log(
        `Fixed ${result.fixes} patterns in: ${path.relative(srcDir, filePath)}`,
      );
      totalFixes += result.fixes;
      filesFixed++;
      fixedFilePaths.push(path.relative(srcDir, filePath));
    }
  } catch (err) {
    console.error(`Error processing ${filePath}:`, err.message);
  }
}

console.log("Finding TypeScript files...");
const files = getAllTsFiles(srcDir);
console.log(`Found ${files.length} TypeScript files\n`);

console.log("Fixing common patterns...\n");
for (const file of files) {
  processFile(file);
}

console.log(`\n=== Summary ===`);
console.log(`Files modified: ${filesFixed}`);
console.log(`Total pattern fixes: ${totalFixes}`);
if (fixedFilePaths.length > 0 && fixedFilePaths.length <= 20) {
  console.log("\nFiles changed:");
  fixedFilePaths.forEach(f => console.log(`  - ${f}`));
}
