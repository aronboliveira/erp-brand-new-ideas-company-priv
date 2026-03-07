/**
 * Script to add Event type to 'e' parameters in event handler contexts
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

function fixEventParameters(content) {
  let modified = content;
  let fixes = 0;

  // Pattern 1: const/let/var onX = e => { (event handler arrow function without parens)
  modified = modified.replace(
    /\b(const|let|var)\s+(on\w+|handle\w+)\s*=\s*e\s*=>\s*{/gi,
    (match, decl, name) => {
      fixes++;
      return `${decl} ${name} = (e: Event) => {`;
    },
  );

  // Pattern 2: const/let/var onX = event => {
  modified = modified.replace(
    /\b(const|let|var)\s+(on\w+|handle\w+)\s*=\s*event\s*=>\s*{/gi,
    (match, decl, name) => {
      fixes++;
      return `${decl} ${name} = (event: Event) => {`;
    },
  );

  // Pattern 3: addEventListener callbacks with (e) => (no type)
  modified = modified.replace(
    /(addEventListener\(\s*["'][^"']+["']\s*,\s*)\(\s*e\s*\)\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(e: Event) =>`;
    },
  );

  // Pattern 4: addEventListener callbacks with e => (no parens)
  modified = modified.replace(
    /(addEventListener\(\s*["'][^"']+["']\s*,\s*)e\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(e: Event) =>`;
    },
  );

  // Pattern 5: addEventListener with (event) =>
  modified = modified.replace(
    /(addEventListener\(\s*["'][^"']+["']\s*,\s*)\(\s*event\s*\)\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(event: Event) =>`;
    },
  );

  // Pattern 6: .on("event", (e) => or .on("event", e =>
  modified = modified.replace(
    /(\.on\(\s*["'][^"']+["']\s*,\s*)\(\s*e\s*\)\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(e: JQuery.Event) =>`;
    },
  );
  modified = modified.replace(
    /(\.on\(\s*["'][^"']+["']\s*,\s*)e\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(e: JQuery.Event) =>`;
    },
  );

  // Pattern 7: .click((e) => ...) jQuery shorthand events
  const jqueryEvents =
    "click|change|submit|focus|blur|keydown|keyup|keypress|mousedown|mouseup|mouseover|mouseout|mousemove|input|scroll|resize";
  const jqRegex1 = new RegExp(
    `(\\.(${jqueryEvents})\\(\\s*)\\(\\s*e\\s*\\)\\s*=>`,
    "g",
  );
  modified = modified.replace(jqRegex1, (match, prefix) => {
    fixes++;
    return `${prefix}(e: JQuery.Event) =>`;
  });
  const jqRegex2 = new RegExp(`(\\.(${jqueryEvents})\\(\\s*)e\\s*=>`, "g");
  modified = modified.replace(jqRegex2, (match, prefix) => {
    fixes++;
    return `${prefix}(e: JQuery.Event) =>`;
  });

  // Pattern 8: querySelectorAll.forEach((el) => ...)
  modified = modified.replace(
    /(querySelectorAll(?:<[^>]+>)?\([^)]+\)\.forEach\(\s*)\(\s*el\s*\)\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(el: Element) =>`;
    },
  );
  // Without parens
  modified = modified.replace(
    /(querySelectorAll(?:<[^>]+>)?\([^)]+\)\.forEach\(\s*)el\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(el: Element) =>`;
    },
  );

  // Pattern 9: .forEach((element) =>
  modified = modified.replace(
    /(querySelectorAll(?:<[^>]+>)?\([^)]+\)\.forEach\(\s*)\(\s*element\s*\)\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(element: Element) =>`;
    },
  );

  // Pattern 10: Simple function(e) in event context
  modified = modified.replace(
    /(addEventListener\(\s*["'][^"']+["']\s*,\s*)function\s*\(\s*e\s*\)\s*{/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}function(e: Event) {`;
    },
  );

  // Pattern 11: function(event)
  modified = modified.replace(
    /(addEventListener\(\s*["'][^"']+["']\s*,\s*)function\s*\(\s*event\s*\)\s*{/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}function(event: Event) {`;
    },
  );

  // Pattern 12: Generic function with (e) in arrow - very common
  // const cb = (e) => e.preventDefault()
  // Too risky to change generically without context

  // Pattern 13: jQuery ajax .done((data) => ...) or .done(data => ...)
  modified = modified.replace(
    /(\.done\(\s*)\(\s*data\s*\)\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(data: unknown) =>`;
    },
  );
  modified = modified.replace(/(\.done\(\s*)data\s*=>/g, (match, prefix) => {
    fixes++;
    return `${prefix}(data: unknown) =>`;
  });

  // Pattern 14: .fail((jqXHR, textStatus, errorThrown) => ...)
  modified = modified.replace(
    /(\.fail\(\s*)\(\s*jqXHR\s*,\s*textStatus\s*,\s*errorThrown\s*\)\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(jqXHR: JQueryXHR, textStatus: string, errorThrown: string) =>`;
    },
  );

  // Pattern 15: .then((response) => response.json())
  modified = modified.replace(
    /(\.then\(\s*)\(\s*response\s*\)\s*=>\s*response\.json\(\)/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(response: Response) => response.json()`;
    },
  );

  // Pattern 16: .then(response => response.json())
  modified = modified.replace(
    /(\.then\(\s*)response\s*=>\s*response\.json\(\)/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(response: Response) => response.json()`;
    },
  );

  // Pattern 17: .catch((error) => ...) or .catch(error => ...)
  modified = modified.replace(
    /(\.catch\(\s*)\(\s*error\s*\)\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(error: unknown) =>`;
    },
  );
  modified = modified.replace(/(\.catch\(\s*)error\s*=>/g, (match, prefix) => {
    fixes++;
    return `${prefix}(error: unknown) =>`;
  });

  // Pattern 18: .catch((err) => ...) or .catch(err => ...)
  modified = modified.replace(
    /(\.catch\(\s*)\(\s*err\s*\)\s*=>/g,
    (match, prefix) => {
      fixes++;
      return `${prefix}(err: unknown) =>`;
    },
  );
  modified = modified.replace(/(\.catch\(\s*)err\s*=>/g, (match, prefix) => {
    fixes++;
    return `${prefix}(err: unknown) =>`;
  });

  return { content: modified, fixes };
}

function processFile(filePath) {
  try {
    const content = fs.readFileSync(filePath, "utf-8");
    const result = fixEventParameters(content);

    if (result.fixes > 0) {
      fs.writeFileSync(filePath, result.content, "utf-8");
      console.log(
        `Fixed ${result.fixes} event params in: ${path.relative(srcDir, filePath)}`,
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

console.log("Fixing event parameters...\n");
for (const file of files) {
  processFile(file);
}

console.log(`\n=== Summary ===`);
console.log(`Files modified: ${filesFixed}`);
console.log(`Total fixes: ${totalFixes}`);
