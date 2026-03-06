#!/usr/bin/env node
// fix-last.mjs — Fix the final edge cases
import { readFileSync, writeFileSync } from "node:fs";

const BASE = "/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/ts/";

// 1. Fix "void if" patterns (invalid syntax) — remove "void " before if/for/while
for (const rel of [
  "src/public/assets/js/routes/jobs/boards/convert.ts",
  "src/public/assets/js/routes/pos/index.ts",
]) {
  const fp = BASE + rel;
  let src = readFileSync(fp, "utf8");
  // "void if" → just "if"
  src = src.replace(/\bvoid\s+if\s*\(/g, "if (");
  src = src.replace(/\bvoid\s+for\s*\(/g, "for (");
  src = src.replace(/\bvoid\s+while\s*\(/g, "while (");
  writeFileSync(fp, src);
  console.log("Fixed void-if in " + rel);
}

// Also scan all files for "void if" patterns just in case
import { globSync } from "node:fs";
import { readdirSync, statSync } from "node:fs";

function walk(dir) {
  let results = [];
  for (const f of readdirSync(dir)) {
    const p = dir + "/" + f;
    if (statSync(p).isDirectory()) results = results.concat(walk(p));
    else if (f.endsWith(".ts")) results.push(p);
  }
  return results;
}

for (const fp of walk(BASE + "src")) {
  let src = readFileSync(fp, "utf8");
  if (/\bvoid\s+if\s*\(/.test(src)) {
    src = src.replace(/\bvoid\s+if\s*\(/g, "if (");
    writeFileSync(fp, src);
    console.log("Fixed void-if in " + fp.replace(BASE, ""));
  }
}

// 2. Fix shebang in run-newman.ts — move shebang to line 1
{
  const fp = BASE + "src/tests/postman/scripts/run-newman.ts";
  let src = readFileSync(fp, "utf8");
  if (src.includes("#!/usr/bin/env") && !src.startsWith("#!")) {
    // Extract and remove shebang
    const shebangMatch = src.match(/^.*?(#!\/usr\/bin\/env[^\n]*)\n/m);
    if (shebangMatch) {
      src = src.replace(shebangMatch[1] + "\n", "");
      src = shebangMatch[1] + "\n" + src;
      writeFileSync(fp, src);
      console.log("Fixed shebang in run-newman.ts");
    }
  }
}

// 3. Add no-unnecessary-condition to cookieconsent.ts disable comment
{
  const fp = BASE + "src/public/js/cookieconsent.ts";
  let src = readFileSync(fp, "utf8");
  // This is a vendor file — add file-level disable for all strict rules
  if (!src.includes("eslint-disable")) {
    const jsdocEnd = src.indexOf("*/");
    if (jsdocEnd !== -1) {
      const insertPos = src.indexOf("\n", jsdocEnd) + 1;
      src = src.slice(0, insertPos) +
        "/* eslint-disable @typescript-eslint/no-unnecessary-condition */\n" +
        src.slice(insertPos);
    } else {
      src = "/* eslint-disable @typescript-eslint/no-unnecessary-condition */\n" + src;
    }
    writeFileSync(fp, src);
    console.log("Added eslint-disable for cookieconsent.ts");
  } else {
    // Already has an eslint-disable — append the rule
    src = src.replace(
      /\/\* eslint-disable ([^*]*)\*\//,
      (m, rules) => {
        if (rules.includes("no-unnecessary-condition")) return m;
        return `/* eslint-disable ${rules.trim()}, @typescript-eslint/no-unnecessary-condition */`;
      }
    );
    writeFileSync(fp, src);
    console.log("Updated eslint-disable for cookieconsent.ts");
  }
}

console.log("Done.");
