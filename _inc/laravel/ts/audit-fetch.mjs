#!/usr/bin/env node
// audit-fetch.mjs — Check which fetch/JSON calls are NOT inside try-catch
import { readFileSync, readdirSync, statSync } from "node:fs";
import { join } from "node:path";

const BASE = "/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/ts/src";

function walk(dir) {
  let results = [];
  for (const f of readdirSync(dir)) {
    const p = join(dir, f);
    if (statSync(p).isDirectory()) results = results.concat(walk(p));
    else if (f.endsWith(".ts")) results.push(p);
  }
  return results;
}

for (const fp of walk(BASE)) {
  const src = readFileSync(fp, "utf8");
  const lines = src.split("\n");
  
  let tryDepth = 0;
  const tryBraceStarts = [];
  let braceDepth = 0;
  
  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    const trimmed = line.trim();
    
    // Check for try blocks
    if (/\btry\s*\{/.test(trimmed) || trimmed === "try {" || trimmed === "try{") {
      tryDepth++;
      // find the { on this line and record brace depth
    }
    
    for (const ch of line) {
      if (ch === "{") {
        braceDepth++;
        if (/\btry\s*\{/.test(trimmed) || trimmed === "try {") {
          tryBraceStarts.push(braceDepth);
        }
      } else if (ch === "}") {
        if (tryBraceStarts.length > 0 && braceDepth === tryBraceStarts[tryBraceStarts.length - 1]) {
          tryBraceStarts.pop();
          tryDepth = tryBraceStarts.length;
        }
        braceDepth--;
      }
    }
    
    tryDepth = tryBraceStarts.length;
    
    // Check for unprotected fetch
    if (/\bfetch\s*\(/.test(line) && !trimmed.startsWith("//") && tryDepth === 0) {
      // Also check if it has .catch on the same line or a few lines ahead
      const nextLines = lines.slice(i, i + 10).join(" ");
      if (!nextLines.includes(".catch")) {
        const rel = fp.replace(BASE + "/", "");
        console.log(`UNGUARDED fetch: ${rel}:${i + 1}  tryDepth=${tryDepth}`);
        console.log(`  ${trimmed.slice(0, 100)}`);
      }
    }
    
    // Check for unprotected JSON.parse
    if (/JSON\.parse\s*\(/.test(line) && !trimmed.startsWith("//") && tryDepth === 0) {
      const rel = fp.replace(BASE + "/", "");
      // Check if it's wrapped in our inline try
      if (!trimmed.includes("try {")) {
        console.log(`UNGUARDED JSON.parse: ${rel}:${i + 1}  tryDepth=${tryDepth}`);
        console.log(`  ${trimmed.slice(0, 100)}`);
      }
    }
  }
}
